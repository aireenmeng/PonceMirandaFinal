<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

/**
 * Manages the full history of appointments for a logged-in patient.
 */
class PatientHistoryController extends Controller
{
    /**
     * Display a paginated, searchable list of the patient's appointment history.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Appointment::where('user_id', Auth::id())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('service', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(10)->withQueryString();
        
        return view('patient.history.index', compact('appointments', 'search'));
    }

    /**
     * Cancel a pending appointment.
     * 
     * Policy enforcement: Patients are only allowed to self-cancel appointments
     * that are still in 'pending' status. Confirmed appointments must be cancelled
     * by contacting the clinic directly to minimize schedule disruption.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        $appt = Appointment::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        
        if (!in_array($appt->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Only pending or confirmed appointments can be cancelled by the patient.');
        }
        
        $cancellationReason = 'Patient requested cancellation';
        if ($appt->status === 'confirmed') {
            $cancellationReason = 'Patient cancelled a confirmed appointment';
        }

        $appt->update([
            'status' => 'cancelled', 
            'cancellation_reason' => $cancellationReason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now()
        ]);
        
        return back()->with('success', 'Appointment cancelled.');
    }
}