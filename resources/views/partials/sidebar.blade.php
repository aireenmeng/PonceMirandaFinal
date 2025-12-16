<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    {{-- BRAND LOGO --}}
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('poce.jpg') }}" alt="Logo" height="40" class="rounded-circle">
        </div>
        <div class="sidebar-brand-text mx-3">Ponce Miranda</div>
    </a>

    <hr class="sidebar-divider my-0">

    {{-- ========================================================= --}}
    {{-- 1. ADMIN MENU (Role: 'admin') --}}
    {{-- ========================================================= --}}
    @if(auth()->user()->role === 'admin')

        <div class="sidebar-heading">Overview</div>

        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i><span>Admin Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Clinic Operations</div>

        <li class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.appointments.index', ['date' => now()->format('Y-m-d')]) }}">
                <i class="fas fa-calendar-check"></i><span>Appointment</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="fas fa-clock"></i><span>Schedule</span>
            </a>
        </li>
        
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Management</div>
        
        <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.services.index') }}">
                <i class="fas fa-clipboard-list"></i><span>Services</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.patients.index') }}">
                <i class="fas fa-users"></i><span>Patients</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.staff.index') }}">
                <i class="fas fa-user-md"></i><span>Staff</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.reports.index') }}">
                <i class="fas fa-chart-area"></i><span>Reports</span>
            </a>
        </li>

    {{-- ========================================================= --}}
    {{-- 2. DOCTOR MENU (Role: 'doctor') --}}
    {{-- ========================================================= --}}
    @elseif(auth()->user()->role === 'doctor')

        <li class="nav-item {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('doctor.dashboard') }}">
                <i class="fas fa-fw fa-stethoscope"></i>
                <span>Doctor Workspace</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item {{ request()->routeIs('doctor.schedule.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('doctor.schedule.index') }}">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>My Schedule</span>
            </a>
        </li>

    {{-- UPDATED LINK --}}
    <li class="nav-item {{ request()->routeIs('doctor.consultations') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('doctor.consultations') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Patients</span>
        </a>
    </li>

    {{-- NEW LINK FOR TODAY'S CONSULTATIONS --}}
    <li class="nav-item {{ request()->routeIs('doctor.todaysConsultations') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('doctor.todaysConsultations') }}">
            <i class="fas fa-fw fa-notes-medical"></i>
            <span>Today's Consultations</span>
        </a>
    </li>

    {{-- ========================================================= --}}
    {{-- 3. PATIENT MENU (Role: 'patient') --}}
    {{-- ========================================================= --}}
    @else

        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-home"></i>
                <span>My Home</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item {{ request()->routeIs('patient.booking.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('patient.booking.step1') }}">
                <i class="fas fa-fw fa-calendar-plus"></i>
                <span>Book Appointment</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('patient.history') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('patient.history') }}">
                <i class="fas fa-fw fa-list-alt"></i>
                <span>My Appointments</span>
            </a>
        </li>

    @endif

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>