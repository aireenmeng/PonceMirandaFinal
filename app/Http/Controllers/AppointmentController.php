<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\AppointmentService; 

/**
 * Manages the full lifecycle of appointments within the admin panel.
 * 
 * This controller handles listing, creating (booking), updating, canceling,
 * and managing the status of appointments. It also includes utility methods
 * for checking availability and blocking specific time slots on the calendar.
 */
class AppointmentController extends Controller
{
    protected $appointmentService;

    /**
     * Initialize the controller with the AppointmentService.
     *
     * @param AppointmentService $appointmentService Service for handling complex appointment logic like conflict checks.
     */
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display a paginated list of appointments with advanced filtering and sorting.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Extract filter parameters from the request
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $date = $request->get('date'); 
        $status = $request->get('status');

        // Determine sorting criteria, defaulting to appointment date
        $sort = $request->get('sort', 'appointment_date'); 
        $direction = $request->get('direction', 'asc');

        // Handle consolidated Sorting Filter (Dropdown)
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'date_desc':
                    $sort = 'appointment_date'; $direction = 'desc'; break;
                case 'date_asc':
                    $sort = 'appointment_date'; $direction = 'asc'; break;
                case 'created_desc':
                    $sort = 'created_at'; $direction = 'desc'; break;
                case 'cancelled_desc':
                    $sort = 'cancelled_at'; $direction = 'desc'; break;
            }
        } elseif ($status === 'cancelled' && !$request->has('sort')) {
            // Default sort for 'cancelled' tab: Most recently cancelled first
            $sort = 'cancelled_at';
            $direction = 'desc';
        }

        $allowedSorts = ['appointment_date', 'appointment_time', 'created_at', 'id', 'cancelled_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'appointment_date';
        }

        // Initialize the query with eager loading for performance
        $query = Appointment::with(['patient', 'doctor', 'service']);

        // Apply filters based on the view mode (Daily vs. Range)
        if ($date) {
            // "Day View": Show appointments for a specific date, excluding internal blocks
            $query->whereDate('appointment_date', $date)
                  ->where('status', '!=', 'blocked'); 
            
            if ($status) {
                $query->where('status', $status);
            }
        } else {
            // "List View": Default to showing pending appointments or a filtered range
            $status = $status ?: 'pending';
            $query->where('status', $status);
            
            if ($startDate && $endDate) {
                $query->whereBetween('appointment_date', [$startDate, $endDate]);
            }
        }
        
        // Apply search functionality across patient and doctor names
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

        // Apply sorting
        $query->orderBy($sort, $direction);
        
        // If sorting by date, secondary sort by time ensures chronological order
        if ($sort == 'appointment_date') {
            $query->orderBy('appointment_time', $direction);
        }

        $appointments = $query->paginate(10)->withQueryString();

        $currentTab = $date ? 'today' : $status;

        return view('admin.appointments.index', compact('appointments', 'status', 'search', 'startDate', 'endDate', 'sort', 'direction', 'date', 'currentTab'));
    }

    /**
     * Show the form for creating a new appointment (Walk-in Booking).
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request) 
    {
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();
        $date = $request->input('date', date('Y-m-d')); 

        return view('admin.appointments.create', compact('patients', 'doctors', 'services', 'date'));
    }

    /**
     * Store a newly created appointment in storage.
     * 
     * Handles both existing patients and new walk-in registrations.
     * Uses a database transaction to ensure data integrity and prevent double bookings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Define core validation rules required for all appointments
        $rules = [
            'doctor_id'        => 'required|exists:users,id',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today|before_or_equal:' . \Carbon\Carbon::now()->addMonths(1)->endOfMonth()->toDateString(),
            'appointment_time' => 'required',
            'duration_minutes' => 'required|integer|min:30',
            'patient_type'     => 'required|in:existing,new', 
        ];

        // Add conditional validation based on whether the patient is new or existing
        if ($request->patient_type === 'existing') {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            // New patients require basic contact info for account creation
            $rules['new_patient_name']  = 'required|string|max:255';
            $rules['new_patient_phone'] = ['required', 'regex:/^09\d{9}$/'];
        }

        $validated = $request->validate($rules);

        // Execute the booking process within a transaction to handle race conditions
        return DB::transaction(function () use ($request) {
            
            // Check for scheduling conflicts using the service
            $checkData = [
                'doctor_id' => $request->doctor_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'duration_minutes' => $request->duration_minutes,
            ];

            if ($request->patient_type === 'existing') {
                $checkData['user_id'] = $request->user_id;
            }

            $conflicts = $this->appointmentService->checkConflicts($checkData);

            if (!empty($conflicts)) {
                 return redirect()->back()->withInput()->withErrors($conflicts);
            }

            $userId = $request->user_id;
            
            // Create a new user account for walk-in patients silently
            if ($request->patient_type === 'new') {
                $newPatient = User::create([
                    'name'     => $request->new_patient_name,
                    'phone'    => $request->new_patient_phone,
                    'email'    => null, // Email is optional for walk-ins
                    'password' => null, // Account is unverified/incomplete initially
                    'role'     => 'patient',
                ]);
                $userId = $newPatient->id;
            }

            $service = Service::findOrFail($request->service_id);

            // Create the confirmed appointment
            Appointment::create([
                'user_id'          => $userId,
                'doctor_id'        => $request->doctor_id,
                'service_id'       => $request->service_id,
                'price'            => $service->price,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'duration_minutes' => $request->duration_minutes,
                'status'           => 'confirmed' // Admin bookings bypass the pending state
            ]);

            return redirect()->route('admin.appointments.index', ['status' => 'confirmed'])
                ->with('success', 'Appointment booked successfully.');
        });
    }
    
    /**
     * Mark an appointment as confirmed.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request, $id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment confirmed.');
    }

    /**
     * Mark an appointment as completed.
     * 
     * Validates that the appointment date is not in the future before completion.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, $id) 
    {
        $appt = Appointment::findOrFail($id);
        
        // Prevent completing appointments scheduled for future dates
        if ($appt->appointment_date->gt(Carbon::today())) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot mark future appointments as completed.');
        }

        $appt->update(['status' => 'completed']);
        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment marked as completed.');
    }

    /**
     * Cancel an appointment and record the reason.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Display the specified appointment.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service', 'canceller'])->findOrFail($id);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service'])->findOrFail($id);
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors', 'services'));
    }

    /**
     * Update the specified appointment in storage.
     * 
     * Performs conflict checks to ensure the new time slot is available.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today', 
                'before_or_equal:' . \Carbon\Carbon::now()->addMonths(1)->endOfMonth()->toDateString(), 
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

        // Check for scheduling conflicts, excluding the current appointment from the check
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

    /**
     * Restore a cancelled appointment to pending status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'cancelled') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Only cancelled appointments can be restored.');
        }

        if ($appointment->appointment_date->lt(Carbon::today())) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot restore a past appointment.');
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

    /**
     * Retrieve available time slots for a specific doctor and date via AJAX.
     * 
     * Iterates through the doctor's schedule to find open blocks of time,
     * checking for conflicts with existing appointments.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $date = Carbon::parse($request->input('date'));
        $selectedDuration = (int) $request->input('duration', 30); 

        if (!$doctorId || !$date) {
            return response()->json(['error' => 'Doctor ID and Date are required.'], 400);
        }

        $slots = [];
        // Retrieve the doctor's specific schedule for the day
        $schedule = \App\Models\Schedule::where('doctor_id', $doctorId)
            ->where('date', $date->toDateString())
            ->first();

        // Fallback: If no specific schedule, assume default hours unless it's Sunday
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

        // Check if the doctor is marked as "Day Off"
        if ($scheduleStartTime->format('H:i') === '00:00' && $scheduleEndTime->format('H:i') === '00:00') {
            return response()->json(['message' => 'Doctor is on a day off.'], 200);
        }

        $currentTime = $scheduleStartTime->copy();
        
        // Generate slots
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

            // Verify if the slot is free from conflicts
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

    /**
     * Block or unblock a specific time slot (AJAX).
     * 
     * Uses a dummy appointment with status 'blocked' to reserve the slot.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockSlot(Request $request)
    {
        if ($request->status === 'reserved') {
            // Create a blocking appointment to reserve the time
            $serviceId = \App\Models\Service::first()->id ?? 1;

            Appointment::create([
                'doctor_id' => $request->doctor_id,
                'user_id'   => $request->doctor_id, // The doctor "owns" the blocked slot
                'service_id' => $serviceId,         // Placeholder service
                'appointment_date' => $request->date,
                'appointment_time' => $request->time,
                'status' => 'blocked', 
                'duration_minutes' => 30, 
                'price' => 0
            ]);
        } else {
            // Remove the block
            Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->where('status', 'blocked')
                ->delete(); 
        }
        return response()->json(['success' => true]);
    }
}
