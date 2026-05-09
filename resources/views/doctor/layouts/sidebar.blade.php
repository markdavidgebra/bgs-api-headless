<div class="col-md-3">
  <div class="dashboard-menu">
    <ul class="nav flex-column" role="tablist">
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}"
          href="{{ route('doctor.dashboard') }}">Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}"
          href="{{ route('doctor.appointments') }}">My Appointments / Schedule</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.patient-records') ? 'active' : '' }}"
          href="{{ route('doctor.patient-records') }}">Patient Records</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.treatment-notes') ? 'active' : '' }}"
          href="{{ route('doctor.treatment-notes') }}">Treatment Notes</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.products') ? 'active' : '' }}"
          href="{{ route('doctor.products') }}">Clinic products &amp; stock</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.services') ? 'active' : '' }}"
          href="{{ route('doctor.services') }}">My Services</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.availability*') ? 'active' : '' }}"
          href="{{ route('doctor.availability') }}">Availability</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.notifications*') ? 'active' : '' }}"
          href="{{ route('doctor.notifications') }}">Notifications</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('doctor.profile*') ? 'active' : '' }}"
          href="{{ route('doctor.profile') }}">Profile</a>
      </li>
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
