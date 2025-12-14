<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Auth\LoginRedirectController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\PatientHomeController;
use App\Http\Controllers\PatientBookingController;
use App\Http\Controllers\PatientHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\CalendarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Doctor\DoctorScheduleController;

/*
|--------------------------------------------------------------------------
| GUEST / PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// 1. THE HOME PAGE
Route::get('/login', [PublicController::class, 'index'])->name('home');

// 2. OTHER PUBLIC PAGES
Route::get('/services', [PublicController::class, 'services'])->name('services.public.index');
Route::get('/doctors', [PublicController::class, 'doctors'])->name('doctors.public.index');
Route::get('/doctors/{id}', [PublicController::class, 'doctorProfile'])->name('doctors.public.show');

// --- SMART REDIRECT (Post-Login) ---
Route::get('/home', LoginRedirectController::class)->middleware(['auth'])->name('home');

// --- SHARED ROUTES (Admins, Doctors, Patients) ---
Route::middleware(['auth', 'verified'])->group(function () {
    // API Routes for Calendar
    Route::get('/api/calendar/events', [CalendarController::class, 'getEvents'])->name('api.calendar');
    Route::get('/api/calendar/details', [CalendarController::class, 'getDayDetails'])->name('api.day_details');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- PATIENT ROUTES (Role: patient) ---
Route::middleware(['auth', 'role:patient', 'verified'])->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [PatientHomeController::class, 'index'])->name('dashboard');

    // 2. Single Page Booking Wizard
    Route::get('/book-appointment', [PatientBookingController::class, 'create'])->name('patient.booking.step1');
    Route::post('/book-appointment', [PatientBookingController::class, 'store'])->name('patient.booking.store');

    // 3. Full History
    Route::get('/my-history', [PatientHistoryController::class, 'index'])->name('patient.history');
    Route::get('/my-appointments', [PatientHistoryController::class, 'index'])->name('patient.appointments');
    Route::post('/my-appointments/{id}/cancel', [PatientHistoryController::class, 'cancel'])->name('patient.cancel');
});

// --- DOCTOR ROUTES ---
Route::middleware(['auth', 'role:doctor', 'verified'])->prefix('doctor')->name('doctor.')->group(function () {
    
    // 1. Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'index'])->name('dashboard');
    
    // Diagnosis Update
    Route::put('appointments/{appointment}/diagnosis', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'updateDiagnosis'])->name('appointments.updateDiagnosis');

    // 3. SCHEDULE MANAGEMENT
    Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/update-date', [DoctorScheduleController::class, 'updateDateSchedule'])->name('schedule.updateDate');
    
    // *** NEW ROUTE ADDED HERE ***
    Route::post('/schedule/toggle', [DoctorScheduleController::class, 'toggleSlot'])->name('schedule.toggle');
    // ****************************
    
    // Consultations
    Route::get('consultations', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'patientList'])->name('consultations');
    Route::get('consultations/{patient}', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'showPatientConsultations'])->name('patient.consultations');
    Route::get('todays-consultations', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'todaysConsultations'])->name('todaysConsultations');
});

// --- ADMIN ROUTES (Role: admin) ---
Route::middleware(['auth', 'role:admin', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // --- 1. SERVICES ---
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index'); 
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy'); 
    Route::post('/services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore'); 
    Route::delete('/services/{id}/force', [ServiceController::class, 'forceDelete'])->name('services.forceDelete');

    // --- 2. SCHEDULES ---
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    
    // --- 3. APPOINTMENTS ---
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
    
    // Actions
    Route::post('/appointments/{id}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{id}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{id}/restore', [AppointmentController::class, 'restore'])->name('appointments.restore');
    Route::post('/appointments/block-slot', [AppointmentController::class, 'blockSlot'])->name('appointments.block');
    Route::get('/appointments/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.availableSlots');

    // --- 4. PATIENTS ---
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{id}/restore', [PatientController::class, 'restore'])->name('patients.restore');
    
    // --- 5. STAFF ---
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])->name('staff.restore');

    // --- REPORTS ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';