<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ServiceController; 
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Auth\LoginRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// --- Smart Redirect ---
Route::get('/home', LoginRedirectController::class)->middleware(['auth'])->name('home');

// --- PATIENT ROUTES (Role: patient) ---
// Using 'role:patient' middleware we created earlier
Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');
});

// --- DOCTOR ROUTES (Role: doctor) ---
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', function () {
        return view('doctor.dashboard'); 
    })->name('dashboard');
});

// --- ADMIN ROUTES (Role: admin) ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Services Management (Inventory)
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    
    // Edit & Update
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    
    // Archives & Soft Delete
    Route::get('/services/archives', [ServiceController::class, 'archives'])->name('services.archives');
    Route::post('/services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');
    Route::delete('/services/{id}/force', [ServiceController::class, 'forceDelete'])->name('services.forceDelete');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // --- Schedule Management ---
    Route::get('/schedules', [App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [App\Http\Controllers\ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [App\Http\Controllers\ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [App\Http\Controllers\ScheduleController::class, 'destroy'])->name('schedules.destroy');

    // --- Internal API for Calendar ---
    Route::get('/api/calendar/events', [App\Http\Controllers\Api\CalendarController::class, 'getEvents'])->name('api.calendar');
    Route::get('/api/calendar/details', [App\Http\Controllers\Api\CalendarController::class, 'getDayDetails'])->name('api.day_details');

    // --- Appointment Management ---
    Route::get('/appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/walkin', [App\Http\Controllers\AppointmentController::class, 'storeWalkIn'])->name('appointments.store_walkin');
    
    // Actions
    Route::post('/appointments/{id}/confirm', [App\Http\Controllers\AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{id}/cancel', [App\Http\Controllers\AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{id}/complete', [App\Http\Controllers\AppointmentController::class, 'complete'])->name('appointments.complete');

    // --- Appointment Details & Blocking ---
Route::get('/appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
Route::post('/appointments/block-slot', [App\Http\Controllers\AppointmentController::class, 'blockSlot'])->name('appointments.block');

});

// --- SHARED PROFILE ROUTES ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';