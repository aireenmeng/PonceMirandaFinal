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
use App\Http\Controllers\Doctor\DoctorDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Guest / Public Routes
|--------------------------------------------------------------------------
|
| Routes accessible to all users without authentication.
| Includes the landing page, service listings, and doctor profiles.
*/

// Landing page for the clinic
Route::get('/', [PublicController::class, 'index'])->name('public.home');

// Publicly available information pages
Route::get('/services', [PublicController::class, 'services'])->name('services.public.index');
Route::get('/doctors', [PublicController::class, 'doctors'])->name('doctors.public.index');
Route::get('/doctors/{id}', [PublicController::class, 'doctorProfile'])->name('doctors.public.show');

// Post-Login Redirect: Directs users to their respective dashboards based on role
Route::get('/home', LoginRedirectController::class)->middleware(['auth'])->name('home');

/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes
|--------------------------------------------------------------------------
|
| Routes available to all authenticated users (Admins, Doctors, Patients).
| Includes profile management and shared API endpoints.
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // API Endpoints for fetching calendar events and day details
    Route::get('/api/calendar/events', [CalendarController::class, 'getEvents'])->name('api.calendar');
    Route::get('/api/calendar/details', [CalendarController::class, 'getDayDetails'])->name('api.day_details');
    
    // User Profile Management (Edit, Update, Delete)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Patient Routes
|--------------------------------------------------------------------------
|
| Routes specifically for users with the patient role.
| Covers dashboard access, appointment booking, and history.
*/
Route::middleware(['auth', 'role:patient', 'verified'])->group(function () {
    // Patient Dashboard
    Route::get('/dashboard', [PatientHomeController::class, 'index'])->name('dashboard');

    // Appointment Booking Wizard (Step-by-step process)
    Route::get('/book-appointment', [PatientBookingController::class, 'create'])->name('patient.booking.step1');
    Route::post('/book-appointment', [PatientBookingController::class, 'store'])->name('patient.booking.store');

    // Medical History and Appointment Records
    Route::get('/my-history', [PatientHistoryController::class, 'index'])->name('patient.history');
    Route::get('/my-appointments', [PatientHistoryController::class, 'index'])->name('patient.appointments');
    
    // Cancel an existing appointment
    Route::post('/my-appointments/{id}/cancel', [PatientHistoryController::class, 'cancel'])->name('patient.cancel');
});

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
|
| Routes specifically for users with the 'doctor' role.
| Covers dashboard, schedule management, and patient consultations.
*/
Route::middleware(['auth', 'role:doctor', 'verified'])->prefix('doctor')->name('doctor.')->group(function () {
    
    // Doctor Dashboard: Overview of appointments and stats
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    
    // Update diagnosis and prescription for a specific appointment
    Route::put('appointments/{appointment}/diagnosis', [DoctorDashboardController::class, 'updateDiagnosis'])->name('appointments.updateDiagnosis');

    // Schedule Management: View and modify availability
    Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/update-date', [DoctorScheduleController::class, 'updateDateSchedule'])->name('schedule.updateDate');
    
    // Toggle specific time slots availability
    Route::post('/schedule/toggle', [DoctorScheduleController::class, 'toggleSlot'])->name('schedule.toggle');
    
    // Patient Consultations and Lists
    Route::get('consultations', [DoctorDashboardController::class, 'patientList'])->name('consultations');
    Route::get('consultations/{patient}', [DoctorDashboardController::class, 'showPatientConsultations'])->name('patient.consultations');
    Route::get('todays-consultations', [DoctorDashboardController::class, 'todaysConsultations'])->name('todaysConsultations');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes specifically for users with the 'admin' role.
| Covers comprehensive management of the clinic's data.
*/
Route::middleware(['auth', 'role:admin', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard: Clinic-wide statistics and quick actions
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Service Management: CRUD operations for clinic services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index'); 
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy'); 
    Route::post('/services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore'); 
    Route::delete('/services/{id}/force', [ServiceController::class, 'forceDelete'])->name('services.forceDelete');

    // Schedule Management: Manage doctor schedules and availability
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    
    // Appointment Management: Full control over appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
    
    // Appointment Actions: State transitions and utility endpoints
    Route::post('/appointments/{id}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{id}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{id}/restore', [AppointmentController::class, 'restore'])->name('appointments.restore');
    Route::post('/appointments/block-slot', [AppointmentController::class, 'blockSlot'])->name('appointments.block');
    Route::get('/appointments/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.availableSlots');

    // Patient Management: CRUD operations for patient records
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{id}/restore', [PatientController::class, 'restore'])->name('patients.restore');
    
    // Staff Management: Manage clinic staff (Doctors, Admins)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])->name('staff.restore');

    // Reports: View clinic reports and analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';
