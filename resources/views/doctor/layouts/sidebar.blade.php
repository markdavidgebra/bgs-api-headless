<div class="col-md-3">
  @php
    $portalDoctor = auth('doctor')->user();
    $docCan = static fn (string $key): bool => $portalDoctor ? \App\Support\DoctorPermissions::can($portalDoctor, $key) : false;
  @endphp
  <div class="dashboard-menu">
    <ul class="nav flex-column" role="tablist">
      @if ($docCan('doctor.dashboard'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}"
            href="{{ route('doctor.dashboard') }}">Dashboard</a>
        </li>
      @endif
      @if ($docCan('doctor.appointments'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}"
            href="{{ route('doctor.appointments') }}">Appointments / Schedule</a>
        </li>
      @endif
      @if ($docCan('doctor.patient_records'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.patient-records*') ? 'active' : '' }}"
            href="{{ route('doctor.patient-records') }}">Patient Records</a>
        </li>
      @endif
      @if ($docCan('doctor.treatment_notes'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.treatment-notes*') ? 'active' : '' }}"
            href="{{ route('doctor.treatment-notes') }}">Treatment Notes</a>
        </li>
      @endif
      @if ($docCan('doctor.products'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.products*') ? 'active' : '' }}"
            href="{{ route('doctor.products') }}">Clinic products &amp; stock</a>
        </li>
      @endif
      @if ($docCan('doctor.services'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.services*') ? 'active' : '' }}"
            href="{{ route('doctor.services') }}">My Services</a>
        </li>
      @endif
      @if ($docCan('doctor.availability'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.availability*') ? 'active' : '' }}"
            href="{{ route('doctor.availability') }}">Availability</a>
        </li>
      @endif
      @if ($docCan('doctor.notifications'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.notifications*') ? 'active' : '' }}"
            href="{{ route('doctor.notifications') }}">Notifications</a>
        </li>
      @endif
      @if ($docCan('doctor.profile'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('doctor.profile*') ? 'active' : '' }}"
            href="{{ route('doctor.profile') }}">Profile</a>
        </li>
      @endif
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="nav-link text-start border-0 bg-transparent w-100 py-2">
            Log out
          </button>
        </form>
      </li>
    </ul>
  </div>
</div>
