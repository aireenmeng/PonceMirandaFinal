<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteStaff;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'active'); // Default tab

        // Common query part
        $query = User::whereIn('role', ['admin', 'doctor']);

        if ($view === 'pending') {
            // Pending = Users who haven't verified email yet
            $staff = $query->whereNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($view === 'archived') {
            $staff = User::onlyTrashed()->whereIn('role', ['admin', 'doctor'])->paginate(10);
        } else {
            // Active = Verified users
            $staff = $query->whereNotNull('email_verified_at')->orderBy('created_at', 'desc')->paginate(10);
        }
                     
        return view('admin.staff.index', compact('staff', 'view'));
    }

    // 2. SHOW INVITE FORM
    public function create()
    {
        return view('admin.staff.create');
    }

    // Find the store() method and replace it
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,doctor',
            'phone' => 'nullable|numeric|digits:11'
        ]);

        // 1. Create Staff with RANDOM password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
        ]);

        // 2. Send Invitation / Set Password Link
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        return redirect()->route('admin.staff.index')
            ->with('success', "Invitation sent to new {$request->role}.");
    }

    // 3. SHOW EDIT FORM
    public function edit($id)
    {
        $staff = User::whereIn('role', ['admin', 'doctor'])->findOrFail($id);
        return view('admin.staff.edit', compact('staff'));
    }

    // 4. UPDATE STAFF
    public function update(Request $request, $id)
    {
        $staff = User::whereIn('role', ['admin', 'doctor'])->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($staff->id)],
            'role' => 'required|in:admin,doctor',
            'phone' => 'nullable|numeric|digits:11', // Enforce 11-digit phone number
        ]);

        $staff->update($request->all());

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$staff->name}' updated successfully.");
    }

    // ARCHIVE (Soft Delete)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete self.');
        $user->delete();
        return back()->with('success', 'Staff member archived.');
    }

    // RESTORE
    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Staff member restored.');
    }
}