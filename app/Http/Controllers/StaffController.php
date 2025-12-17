<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteStaff;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

/**
 * Manages doctor accounts within the clinic system.
 * 
 * This controller handles the lifecycle of doctor accounts, including invitation,
 * creation, updating details, and soft/hard deletion.
 */
class StaffController extends Controller
{
    /**
     * Display a list of doctor accounts with filtering options.
     *
     * - active: Doctors with verified email addresses.
     * - pending: Doctors who have been invited but not yet set their password/verified email.
     * - archived: Soft-deleted doctor accounts.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'active'); 

        // Base query for doctor roles only
        $query = User::where('role', 'doctor');

        if ($view === 'pending') {
            $staff = $query->whereNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($view === 'archived') {
            $staff = User::onlyTrashed()->where('role', 'doctor')->paginate(10);
        } else {
            $staff = $query->whereNotNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        }
                     
        return view('admin.staff.index', compact('staff', 'view'));
    }

    /**
     * Show the invitation form for a new doctor.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Invite a new doctor.
     * 
     * Creates a user account with a random, temporary password. A password reset
     * token is generated and sent via email, allowing the invited doctor to set
     * their own secure credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:doctor', // Restrict to 'doctor' role
            'phone' => 'nullable|numeric|digits:11'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => Hash::make(Str::random(16)),
        ]);

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        return redirect()->route('admin.staff.index')
            ->with('success', "Invitation sent to new doctor.");
    }

    /**
     * Show the form for editing a doctor's details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $staff = User::where('role', 'doctor')->findOrFail($id);
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update a doctor's profile information.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $staff = User::where('role', 'doctor')->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'role' => 'required|in:doctor', // Restrict to 'doctor' role
            'phone' => 'nullable|numeric|digits:11', 
        ]);

        $staff->update($request->all());

        return redirect()->route('admin.staff.index')
            ->with('success', "Doctor '{$staff->name}' updated successfully.");
    }

    /**
     * Archive (soft delete) a doctor's account.
     * 
     * Prevents self-deletion to avoid admin lockout.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot deactivate your own account.');
        $user->delete();
        return back()->with('success', 'Doctor account archived.');
    }

    /**
     * Restore an archived doctor's account.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        User::onlyTrashed()->where('role', 'doctor')->findOrFail($id)->restore();
        return back()->with('success', 'Doctor account restored.');
    }

    /**
     * Permanently remove the specified doctor's account from storage.
     * 
     * This action is irreversible.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->where('role', 'doctor')->findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete your own account permanently.');
        
        $user->forceDelete();
        return back()->with('success', 'Doctor account permanently deleted.');
    }
}