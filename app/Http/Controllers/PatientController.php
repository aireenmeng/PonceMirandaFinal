<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

/**
 * Manages patient records within the admin panel.
 * 
 * Handles listing, viewing details, updating profile information,
 * and archiving/restoring patient accounts.
 */
class PatientController extends Controller
{
    /**
     * Display a paginated list of patients with tab-based filtering.
     * 
     * The view parameter controls the subset of patients shown:
     * - active: Users with verified email addresses (Default).
     * - pending: Users who registered but haven't verified their email.
     * - walkin: Users created by admins/staff (no email on file).
     * - archived: Soft-deleted user accounts.
     * - all: Comprehensive list of all non-archived patients.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'active');
        $search = $request->get('search');

        // Start building the query restricted to the 'patient' role
        $query = User::where('role', 'patient');

        // Apply search filter across name and email fields
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply tab-specific filters
        if ($view === 'all') {
            $patients = $query->withCount('appointments')
                              ->orderBy('name')
                              ->paginate(10);

        } elseif ($view === 'pending') {
            $patients = $query->whereNull('email_verified_at')
                            ->whereNotNull('email') 
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'walkin') {
            $patients = $query->whereNull('email')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'archived') {
            $patients = User::onlyTrashed()
                            ->where('role', 'patient')
                            ->paginate(10);

        } else { // 'active'
            $patients = $query->whereNotNull('email_verified_at')
                            ->withCount('appointments')
                            ->orderBy('name')
                            ->paginate(10);
        }

        return view('admin.patients.index', compact('patients', 'search', 'view'));
    }

    /**
     * Display the specified patient's profile and appointment history.
     * 
     * Includes a nested search/filter for the patient's appointments.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $patient = User::findOrFail($id);
        
        $status = $request->get('status', 'all'); 
        $search = $request->get('search');

        // Build the query for the patient's specific appointments
        $query = $patient->appointments()->with(['doctor', 'service'])->orderBy('appointment_date', 'desc');

        // Filter appointments by status
        if ($status !== 'all') {
            if ($status === 'incoming') {
                $query->whereIn('status', ['pending', 'confirmed']);
            } else {
                $query->where('status', $status);
            }
        }

        // Filter appointments by doctor name or service name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(5);
        
        $currentStatus = $status;

        return view('admin.patients.show', compact('patient', 'appointments', 'search', 'currentStatus'));
    }

    /**
     * Show the form for editing the patient's basic details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $patient = User::where('role', 'patient')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update the patient's profile in storage.
     * 
     * If the email address is changed, the email verification timestamp is reset,
     * triggering a new verification requirement.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $patient = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($patient->id)],
            'phone' => ['nullable', 'regex:/^09\d{9}$/'], 
        ]);

        $patient->fill($request->all());

        // Reset verification if email changes
        if ($patient->isDirty('email')) {
            $patient->email_verified_at = null;
            
            if ($patient->email) {
                $patient->sendEmailVerificationNotification();
            }
        }

        $patient->save();

        return redirect()->route('admin.patients.show', $id)->with('success', 'Patient details updated successfully.');
    }

    /**
     * Archive (soft delete) the specified patient.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Patient archived.');
    }

    /**
     * Restore a previously archived patient.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Patient restored.');
    }

    /**
     * Permanently remove the specified patient from storage.
     * 
     * This action is irreversible and removes the user record completely.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Patient permanently deleted.');
    }

    /**
     * Show the form for creating a new patient manually.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.patients.create');
    }

    /**
     * Store a new patient in storage.
     * 
     * Creates the user account and triggers the standard email verification event.
     * The user is not auto-logged in, ensuring they verify their email first.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable', 'regex:/^09\d{9}$/'], 
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password), 
            'role' => 'patient',
        ]);

        event(new Registered($user));

        return redirect()->route('admin.patients.index')
            ->with('success', 'Patient registered. A verification email has been sent to them.');
    }
}
