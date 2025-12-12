<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientHistoryController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('user_id', Auth::id())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);
        return view('patient.history.index', compact('appointments'));
    }

    public function cancel($id)
    {
        $appt = Appointment::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        if ($appt->status != 'pending') {
            return back()->with('error', 'Only pending appointments can be cancelled.');
        }
        $appt->update([
            'status' => 'cancelled', 
            'cancellation_reason' => 'Patient requested cancellation',
            'cancelled_by' => Auth::id()
        ]);
        return back()->with('success', 'Appointment cancelled.');
    }
}