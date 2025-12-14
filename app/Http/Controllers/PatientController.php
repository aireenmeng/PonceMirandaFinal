<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    // In app/Http/Controllers/PatientController.php
    public function index(Request $request)
    {
        $view = $request->get('view', 'active');
        $search = $request->get('search');

        // Base Query: Start with patients only
        $query = User::where('role', 'patient');

        // Apply Search (works across all tabs)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // --- LOGIC FIXES ---
        if ($view === 'pending') {
            // PENDING: Has an email, but hasn't verified it yet.
            // Exclude Walk-ins (who have null email)
            $patients = $query->whereNull('email_verified_at')
                            ->whereNotNull('email') 
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'walkin') {
            // WALK-IN: Patients with NO email address (manually created)
            $patients = $query->whereNull('email')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'archived') {
            // ARCHIVED: Soft deleted users
            $patients = User::onlyTrashed()
                            ->where('role', 'patient')
                            ->paginate(10);

        } else {
            // ACTIVE: Verified Email OR Walk-ins (since they don't need verification)
            // This is your main list.
            $patients = $query->where(function($q) {
                    $q->whereNotNull('email_verified_at') // Verified Online Users
                    ->orWhereNull('email');             // Walk-in Patients
                })
                ->withCount('appointments') // Eager load count
                ->orderBy('name')
                ->paginate(10);
        }

        return view('admin.patients.index', compact('patients', 'search', 'view'));
    }

    // 2. SHOW & EDIT (These make your buttons work)
    public function show($id)
{
    // 1. Find the patient
    $patient = User::with(['appointments', 'appointments.doctor', 'appointments.service'])->findOrFail($id);
    
    // 2. FIX: Calculate the current status based on their latest appointment
    $lastAppt = $patient->appointments()->latest()->first();
    $currentStatus = $lastAppt ? $lastAppt->status : 'New';

    // 3. Pass it to the view
    return view('admin.patients.show', compact('patient', 'currentStatus'));
}
    public function edit($id)
    {
        $patient = User::where('role', 'patient')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $patient = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($patient->id)],
            'phone' => 'nullable|numeric|digits:11', // Added phone validation
        ]);
        $patient->update($request->all());
        return redirect()->route('admin.patients.show', $id)->with('success', 'Updated.');
    }

    // 3. ARCHIVE ACTIONS
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Patient archived.');
    }

    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Patient restored.');
    }

    public function create()
    {
        return view('admin.patients.create');
    }

    // Find the store() method and replace it
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|numeric|digits:11', // Updated phone validation
            'password' => 'required|string|min:8',
        ]);

        // 1. Create User with the provided password
        // Do NOT auto-verify. Let them verify via email.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password), 
            'role' => 'patient',
        ]);

        // 2. Trigger Email Verification
        event(new \Illuminate\Auth\Events\Registered($user));

        return redirect()->route('admin.patients.index')
            ->with('success', 'Patient registered. A verification email has been sent to them.');
    }
}