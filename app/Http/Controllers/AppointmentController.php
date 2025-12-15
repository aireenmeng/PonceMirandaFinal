<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB; // Import DB
use Carbon\Carbon;
use App\Services\AppointmentService; 

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        // 1. Filter Parameters
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $date = $request->get('date'); 
        
        $status = $request->get('status'); // Nullable initially

        // 2. Sorting Parameters (New Logic)
        $sort = $request->get('sort', 'appointment_date'); 
        $direction = $request->get('direction', 'asc');

        $allowedSorts = ['appointment_date', 'appointment_time', 'created_at', 'id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'appointment_date';
        }

        // 3. Build Query
        $query = Appointment::with(['patient', 'doctor', 'service']);

        if ($date) {
            $query->whereDate('appointment_date', $date)
                  ->where('status', '!=', 'blocked'); // Exclude blocked slots
            
            // Allow sub-filtering by status within Today view
            if ($status) {
                $query->where('status', $status);
            }
        } else {
            // Default View
            $status = $status ?: 'pending';
            $query->where('status', $status);
            
            if ($startDate && $endDate) {
                $query->whereBetween('appointment_date', [$startDate, $endDate]);
            }
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 4. Apply Sorting
        $query->orderBy($sort, $direction);
        
        if ($sort == 'appointment_date') {
            $query->orderBy('appointment_time', $direction);
        }

        $appointments = $query->paginate(10)->withQueryString();

        $currentTab = $date ? 'today' : $status;

        return view('admin.appointments.index', compact('appointments', 'status', 'search', 'startDate', 'endDate', 'sort', 'direction', 'date', 'currentTab'));
    }

    // --- 2. SHOW WALK-IN FORM ---
    public function create(Request $request) 
    {
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();
        $date = $request->input('date', date('Y-m-d')); 

        return view('admin.appointments.create', compact('patients', 'doctors', 'services', 'date'));
    }

    // In app/Http/Controllers/AppointmentController.php


// --- 3. STORE (Handle Booking Logic) ---
    public function store(Request $request)
    {
        // 1. Define Base Validation Rules (FIX: Defined at the very top)
        $rules = [
            'doctor_id'        => 'required|exists:users,id',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'duration_minutes' => 'required|integer|min:30',
            // Ensure we know if it's a "new" (walk-in) or "existing" patient
            'patient_type'     => 'required|in:existing,new', 
        ];

        // 2. Add Conditional Rules based on Patient Type
        if ($request->patient_type === 'existing') {
            // Existing patient must select a valid user ID
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            // New Walk-in patient must provide name and phone
            $rules['new_patient_name']  = 'required|string|max:255';
            $rules['new_patient_phone'] = ['required', 'regex:/^09\d{9}$/'];
        }

        // 3. Run Validation (Now $rules is guaranteed to exist)
        $validated = $request->validate($rules);

        // --- Database Transaction to Prevent Double Booking ---
        return DB::transaction(function () use ($request) {
            
            // A. Check for Double Bookings (Race Condition Lock)
            $conflicts = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->whereIn('status', ['confirmed', 'pending'])
                ->lockForUpdate()
                ->count();

            if ($conflicts > 0) {
                 return redirect()->back()->withInput()->with('error', 'Slot was just taken! Please choose another.');
            }

            // B. Determine User ID (Find Existing or Create New)
            $userId = $request->user_id;
            
            // If Walk-in, create the account silently
            if ($request->patient_type === 'new') {
                $newPatient = User::create([
                    'name'     => $request->new_patient_name,
                    'phone'    => $request->new_patient_phone,
                    'email'    => null, // Walk-ins might not have email
                    'password' => null, // No password needed yet
                    'role'     => 'patient',
                ]);
                $userId = $newPatient->id;
            }

            // C. Get Price
            $service = Service::findOrFail($request->service_id);

            // D. Create the Appointment
            Appointment::create([
                'user_id'          => $userId,
                'doctor_id'        => $request->doctor_id,
                'service_id'       => $request->service_id,
                'price'            => $service->price,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'duration_minutes' => $request->duration_minutes,
                'status'           => 'confirmed' // Admin bookings are confirmed immediately
            ]);

            return redirect()->route('admin.appointments.index', ['status' => 'confirmed'])
                ->with('success', 'Appointment booked successfully.');
        });
    }
    
    // --- 4. CONFIRM ACTION ---
    public function confirm(Request $request, $id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment confirmed.');
    }

    // --- 5. COMPLETE ACTION (Revenue Recording) ---
    public function complete(Request $request, $id) 
    {
        $appt = Appointment::findOrFail($id);
        
        // Changed Logic: Allow marking complete if it's TODAY or PAST.
        // Only block if the date is strictly in the future (tomorrow onwards).
        if ($appt->appointment_date->gt(Carbon::today())) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot mark future appointments as completed.');
        }

        $appt->update(['status' => 'completed']);
        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment marked as completed.');
    }

    // --- 6. CANCEL ACTION (With Audit Log) ---
    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);

        $appt = Appointment::findOrFail($id);

        if ($appt->status === 'completed') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot cancel a completed appointment.');
        }
        
        $appt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(), 
            'cancelled_at' => now(),      
        ]);

        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment cancelled.');
    }

    // --- 7. SHOW DETAILS ---
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service', 'canceller'])->findOrFail($id);
        
        // Removed the block that redirects 'blocked' appointments back.
        // We now allow viewing them to unblock or see details.

        return view('admin.appointments.show', compact('appointment'));
    }

    // --- 8. EDIT APPOINTMENT ---
    public function edit($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service'])->findOrFail($id);
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors', 'services'));
    }

    // --- 9. UPDATE APPOINTMENT ---
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Base validation rules
        $rules = [
            'user_id' => 'required|exists:users,id', // Assuming user_id is always present for update
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today', 
                'before_or_equal:' . \Carbon\Carbon::now()->addMonths(2)->endOfMonth()->toDateString(), 
            ],
            'appointment_time' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $appointmentDateTime = \Carbon\Carbon::parse($request->appointment_date . ' ' . $value);
                    if ($appointmentDateTime->isToday() && $appointmentDateTime->lt(\Carbon\Carbon::now())) {
                        $fail('The ' . $attribute . ' must be a future time for today\'s appointments.');
                    }
                },
            ],
            'duration_minutes' => 'required|integer|min:30',
        ];
        $request->validate($rules);

        // Check for conflicts using the AppointmentService, excluding the current appointment
        $conflicts = $this->appointmentService->checkConflicts([
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_minutes' => $request->duration_minutes,
        ], $appointment->id); 

        if (!empty($conflicts)) {
            return redirect()->back()->withErrors($conflicts)->withInput();
        }
        
        $appointment->update($request->all());

        return redirect()->route('admin.appointments.index', $request->all())
            ->with('success', 'Appointment updated successfully.');
    }

    // --- 10. RESTORE CANCELLED APPOINTMENT ---
    public function restore(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'cancelled') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Only cancelled appointments can be restored.');
        }

        $appointment->update([
            'status' => 'pending',
            'cancellation_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);

        return redirect()->route('admin.appointments.index', array_merge($request->query(), ['status' => 'pending']))
            ->with('success', 'Appointment restored successfully to pending status.');
    }

    // --- 12. Get Available Slots (AJAX) ---
    public function getAvailableSlots(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $date = Carbon::parse($request->input('date'));
        $selectedDuration = (int) $request->input('duration', 30); 

        if (!$doctorId || !$date) {
            return response()->json(['error' => 'Doctor ID and Date are required.'], 400);
        }

        $slots = [];
        $schedule = \App\Models\Schedule::where('doctor_id', $doctorId)
            ->where('date', $date->toDateString())
            ->first();

        if (!$schedule) {
            if ($date->isSunday()) {
                return response()->json(['message' => 'Doctor is not available on Sundays.'], 200);
            }
            $schedule = new \App\Models\Schedule([
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'max_appointments' => 20
            ]);
        }

        $scheduleStartTime = Carbon::parse($schedule->start_time);
        $scheduleEndTime = Carbon::parse($schedule->end_time);

        if ($scheduleStartTime->format('H:i') === '00:00' && $scheduleEndTime->format('H:i') === '00:00') {
            return response()->json(['message' => 'Doctor is on a day off.'], 200);
        }

        $currentTime = $scheduleStartTime->copy();
        
        while ($currentTime->lt($scheduleEndTime)) {
            $slotEndTime = $currentTime->copy()->addMinutes($selectedDuration);

            if ($slotEndTime->gt($scheduleEndTime)) {
                break; 
            }

            $potentialAppointmentData = [
                'doctor_id' => $doctorId,
                'appointment_date' => $date->toDateString(),
                'appointment_time' => $currentTime->format('H:i:s'),
                'duration_minutes' => $selectedDuration,
            ];

            $conflicts = $this->appointmentService->checkConflicts($potentialAppointmentData);

            if (empty($conflicts)) {
                $slots[] = [
                    'time' => $currentTime->format('H:i:s'),
                    'display' => $currentTime->format('h:i A'),
                ];
            }
            $currentTime->addMinutes($selectedDuration);
        }

        return response()->json(['slots' => $slots]);
    }


    // --- 11. BLOCK SLOT (Ajax for Calendar) ---
    public function blockSlot(Request $request)
    {
        if ($request->status === 'reserved') {
            // Find a dummy service ID (since service_id is not nullable)
            $serviceId = \App\Models\Service::first()->id ?? 1;

            Appointment::create([
                'doctor_id' => $request->doctor_id,
                'user_id'   => $request->doctor_id, // Doctor "owns" the block
                'service_id' => $serviceId,         // Dummy service
                'appointment_date' => $request->date,
                'appointment_time' => $request->time,
                'status' => 'blocked', 
                'duration_minutes' => 30, 
                'price' => 0
            ]);
        } else {
            // Unblock
            Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->where('status', 'blocked')
                ->delete(); 
        }
        return response()->json(['success' => true]);
    }
}
