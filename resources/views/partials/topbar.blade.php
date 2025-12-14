<nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow-sm border-bottom" style="background-color: #D1396C !important;">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3 text-white">
        <i class="fa fa-bars"></i>
    </button>

    {{-- CLINIC NAME HEADER --}}
    <div class="d-none d-sm-inline-block form-inline mr-auto navbar-brand text-white rounded-pill px-4 py-2 font-weight-bold" 
        style="letter-spacing: 0.5px; font-size: 1.1rem;">
        <i class="fas fa-tooth mr-2"></i> Ponce Miranda Dental
    </div>

    <ul class="navbar-nav ml-auto">

        {{-- USER INFORMATION --}}
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                <div class="d-flex flex-column text-right mr-2 d-none d-lg-flex">
                    <span class="text-white small font-weight-bold">{{ Auth::user()->name ?? 'User' }}</span>
                    <span class="text-white-50 text-xs">{{ ucfirst(Auth::user()->role ?? 'Guest') }}</span>
                </div>
                {{-- Profile picture removed per user request --}}
            </a>
            
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    My Profile
                </a>
                <div class="dropdown-divider"></div>
                
                {{-- FIX: Standard "Link + Hidden Form" method to prevent 419 Errors --}}
                <a class="dropdown-item text-danger" href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>

{{-- HIDDEN LOGOUT FORM: Placed outside the nav to prevent UI conflicts --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>