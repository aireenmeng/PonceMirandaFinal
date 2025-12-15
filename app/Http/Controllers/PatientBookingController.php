<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the multi-step appointment booking wizard for patients.
 */
class PatientBookingController extends Controller
{
    /**
     * Show the booking wizard step 1.
     * 
     * Loads services and doctors. Prioritizes popular services like "Teeth Cleaning"
     * and "Teeth Extraction" to appear at the top of the dropdown for better UX.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $allServices = Service::orderBy('name')->get();
        
        // Filter and sort to prioritize specific popular services
        $popular = $allServices->filter(function($s) {
            return in_array($s->name, ['Teeth Cleaning', 'Teeth Extraction']);
        })->sortBy(function($s) {
            // "Teeth Cleaning" comes first, followed by others
            return $s->name === 'Teeth Cleaning' ? 0 : 1;
        });

        $others = $allServices->reject(function($s) {
            return in_array($s->name, ['Teeth Cleaning', 'Teeth Extraction']);
        });
        
        // Merge the prioritized list with the rest of the services
        $services = $popular->merge($others);

        // Load doctors for the selection step
        $doctors = User::where('role', 'doctor')->get();

        return view('patient.booking.index', compact('services', 'doctors'));
    }

    /**
     * Store a new appointment request.
     * 
     * Validates that the appointment is not in the past. If the date is today,
     * ensures the time is in the future. Creates the appointment with 'pending' status.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . \Carbon\Carbon::now()->addMonths(2)->endOfMonth()->toDateString(),
            ],
            'appointment_time' => [
                'required',
                // Custom validation: If booking for today, time must be in the future
                function ($attribute, $value, $fail) use ($request) {
                    $appointmentDateTime = \Carbon\Carbon::parse($request->appointment_date . ' ' . $value);
                    if ($appointmentDateTime->isToday() && $appointmentDateTime->lt(\Carbon\Carbon::now())) {
                        $fail('The ' . $attribute . ' must be a future time for today\'s appointments.');
                    }
                },
            ],
        ]); 

        $service = Service::findOrFail($request->service_id);

        Appointment::create([
            'user_id' => Auth::id(),
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price, // Added price from service
            'status' => 'pending' // Online bookings require staff confirmation
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Request submitted! We will confirm your appointment shortly.');
    }
}