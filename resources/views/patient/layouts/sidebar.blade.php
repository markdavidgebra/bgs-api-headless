<div class="col-md-3">
  <div class="dashboard-menu">
    <ul class="nav flex-column" role="tablist">
      @php
        $isAppointmentsListRoute = request()->routeIs('patient.appointments') || request()->routeIs('patient.appointments.show');
      @endphp
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}"
          href="{{ route('patient.dashboard') }}"><i class="fi-rs-settings-sliders mr-10"></i>Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $isAppointmentsListRoute ? 'active' : '' }}"
          href="{{ route('patient.appointments') }}"><i class="fi-rs-calendar mr-10"></i>My appointments</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.appointments.book') ? 'active' : '' }}"
          href="{{ route('patient.appointments.book') }}"><i class="fi-rs-calendar mr-10"></i>Book appointment</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.treatments*') ? 'active' : '' }}"
          href="{{ route('patient.treatments') }}"><i class="fi-rs-heart mr-10"></i>My treatments</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.memberships*') ? 'active' : '' }}"
          href="{{ route('patient.memberships') }}"><i class="fi-rs-badge mr-10"></i>My memberships</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.payments*') ? 'active' : '' }}"
          href="{{ route('patient.payments') }}"><i class="fi-rs-credit-card mr-10"></i>My payments</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.promotions*') ? 'active' : '' }}"
          href="{{ route('patient.promotions') }}"><i class="fi-rs-gift mr-10"></i>Promotions</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.aftercare-instructions*') ? 'active' : '' }}"
          href="{{ route('patient.aftercare-instructions') }}"><i class="fi-rs-note mr-10"></i>Aftercare</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('patient.profile') ? 'active' : '' }}"
          href="{{ route('patient.profile') }}"><i class="fi-rs-user mr-10"></i>Profile</a>
      </li>
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="nav-link text-start border-0 bg-transparent w-100 py-2">
            <i class="fi-rs-sign-out mr-10"></i>Log out
          </button>
        </form>
      </li>
    </ul>
  </div>
</div>
