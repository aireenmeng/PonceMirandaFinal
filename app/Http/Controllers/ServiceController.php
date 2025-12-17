<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

/**
 * Manages the clinic's service offerings.
 * 
 * Handles listing, creating, updating, and archiving services (treatments).
 * Includes enforcement of scheduling rules (e.g., duration must be in 30-minute blocks).
 */
class ServiceController extends Controller
{
    /**
     * Display a paginated list of services.
     * 
     * Supports toggling between active services and archived (soft-deleted) ones.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Determine which view mode to use: 'active' or 'archived'
        $view = $request->get('view', 'active'); 

        if ($view === 'archived') {
            $services = Service::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
        } else {
            $services = Service::orderBy('created_at', 'desc')->paginate(10);
        }

        return view('admin.services.index', compact('services', 'view'));
    }

    /**
     * Show the form for creating a new service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Store a new service in storage.
     * 
     * Enforces strict validation on duration to align with the clinic's 30-minute slot system.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // Enforce that duration is at least 30 minutes and a multiple of 30
            'duration_minutes' => 'required|integer|min:30|multiple_of:30', 
            'description' => 'nullable|string',
        ]);
        Service::create($data);
        return redirect()->route('admin.services.index')->with('success', 'Service added.');
    }

    /**
     * Show the form for editing the specified service.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Update the specified service in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:30|multiple_of:30',
            'description' => 'nullable|string',
        ]);
        Service::findOrFail($id)->update($data);
        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    /**
     * Archive (soft delete) the specified service.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return back()->with('success', 'Service archived.');
    }

    /**
     * Restore a previously archived service.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        Service::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Service restored.');
    }

    /**
     * Permanently remove the specified service from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        Service::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Service permanently deleted.');
    }
}