<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;

/**
 * Handles public-facing pages accessible to all visitors.
 * 
 * Includes the homepage, service listings, and doctor directories.
 */
class PublicController extends Controller
{
    /**
     * Show the public homepage.
     * 
     * Loads a preview of services and clinic statistics to display on the landing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load featured services (limited to 3 for the hero section)
        $services = Service::limit(3)->get();
        
        // Calculate total number of doctors for the stats counter
        $doctorCount = User::where('role', 'doctor')->count();

        return view('public.home', compact('services', 'doctorCount'));
    }

    /**
     * Show a public list of all services.
     *
     * @return \Illuminate\View\View
     */
    public function services()
    {
        $services = Service::orderBy('name')->get();
        return view('public.services', compact('services'));
    }

    /**
     * Show a public list of all available doctors.
     * 
     * Supports searching by doctor name.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function doctors(Request $request)
    {
        $search = $request->get('search');

        $query = User::where('role', 'doctor');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $doctors = $query->orderBy('name')->paginate(12);

        return view('public.doctors', compact('doctors', 'search'));
    }

    /**
     * Show a single doctor's detailed profile.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function doctorProfile($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);
        $services = Service::orderBy('name')->get(); 

        return view('public.doctor-profile', compact('doctor', 'services'));
    }
}