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
 * Manages clinic staff accounts (Doctors and Admins).
 * 
 * Handles the invitation flow, where staff are created with a temporary state
 * and invited to set their credentials. Also handles updates and archiving.
 */
class StaffController extends Controller
{
    /**
     * Display a list of staff members with filtering options.
     *
     * - active: Fully registered staff with verified emails.
     * - pending: Invited staff who have not yet set their password/verified email.
     * - archived: Soft-deleted staff accounts.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'active'); 

        // Base query for staff roles only
        $query = User::whereIn('role', ['admin', 'doctor']);

        if ($view === 'pending') {
            $staff = $query->whereNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($view === 'archived') {
            $staff = User::onlyTrashed()->whereIn('role', ['admin', 'doctor'])->paginate(10);
        } else {
            $staff = $query->whereNotNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        }
                     
        return view('admin.staff.index', compact('staff', 'view'));
    }

    /**
     * Show the invitation form for new staff.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Invite a new staff member.
     * 
     * Creates a user account with a random, temporary password. Then generates
     * a secure password reset token and sends it via email (mimicking an "invitation").
     * This ensures the admin never knows the staff member's password.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,doctor',
            'phone' => 'nullable|numeric|digits:11'
        ]);

        // Create the user with a placeholder password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => Hash::make(Str::random(16)),
        ]);

        // Generate a password reset link and notify the user
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        return redirect()->route('admin.staff.index')
            ->with('success', "Invitation sent to new {$request->role}.");
    }

    /**
     * Show the form for editing a staff member's details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $staff = User::whereIn('role', ['admin', 'doctor'])->findOrFail($id);
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update a staff member's profile information.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $staff = User::whereIn('role', ['admin', 'doctor'])->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'role' => 'required|in:admin,doctor',
            'phone' => 'nullable|numeric|digits:11', 
        ]);

        $staff->update($request->all());

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$staff->name}' updated successfully.");
    }

    /**
     * Archive (soft delete) a staff member.
     * 
     * Prevents self-deletion to avoid admin lockout.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete self.');
        $user->delete();
        return back()->with('success', 'Staff member archived.');
    }

    /**
     * Restore an archived staff member.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Staff member restored.');
    }

    /**
     * Permanently remove the specified staff member from storage.
     * 
     * This action is irreversible.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete self.');
        
        $user->forceDelete();
        return back()->with('success', 'Staff member permanently deleted.');
    }
}