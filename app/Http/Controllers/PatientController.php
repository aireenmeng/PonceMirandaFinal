<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    // 1. INDEX (With Tabs)
    public function index(Request $request)
    {
        $view = $request->get('view', 'active');
        $search = $request->get('search');

        // Base Query
        $query = User::where('role', 'patient');

        // Handle Archive Tab
        if ($view === 'archived') {
            $query->onlyTrashed();
        }

        // Handle Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->withCount('appointments')->orderBy('name')->paginate(10);

        return view('admin.patients.index', compact('patients', 'search', 'view'));
    }

    // 2. SHOW & EDIT (These make your buttons work)
    public function show($id)
    {
        $patient = User::where('role', 'patient')->withTrashed()->with(['appointments' => function($q) {
            $q->orderBy('appointment_date', 'desc');
        }, 'appointments.doctor', 'appointments.service'])->findOrFail($id);

        return view('admin.patients.show', compact('patient'));
    }

    public function edit($id)
    {
        $patient = User::where('role', 'patient')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $patient = User::findOrFail($id);
        $request->validate(['name' => 'required', 'email' => 'required|email']);
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|min:8'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'patient',
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        return redirect()->route('admin.patients.index')->with('success', 'Patient registered successfully.');
    }
}