@php
  $portalDoctor = auth('doctor')->user();
  $docCan = static fn (string $key): bool => $portalDoctor ? \App\Support\DoctorPermissions::can($portalDoctor, $key) : false;
@endphp
<ul class="nav flex-column" role="navigation" aria-label="{{ __('Doctor portal') }}">
  @if ($docCan('doctor.dashboard'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}"
        href="{{ route('doctor.dashboard') }}"><i class="fi-rs-home mr-10"></i>{{ __('Dashboard') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.appointments'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}"
        href="{{ route('doctor.appointments') }}"><i class="fi-rs-calendar mr-10"></i>{{ __('Appointments / Schedule') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.patient_records'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.patient-records*') ? 'active' : '' }}"
        href="{{ route('doctor.patient-records') }}"><i class="fi-rs-folder mr-10"></i>{{ __('Patient Records') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.treatment_notes'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.treatment-notes*') ? 'active' : '' }}"
        href="{{ route('doctor.treatment-notes') }}"><i class="fi-rs-note mr-10"></i>{{ __('Treatment Notes') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.products'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.products*') ? 'active' : '' }}"
        href="{{ route('doctor.products') }}"><i class="fi-rs-shopping-cart mr-10"></i>{{ __('Clinic products & stock') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.services'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.services*') ? 'active' : '' }}"
        href="{{ route('doctor.services') }}"><i class="fi-rs-apps mr-10"></i>{{ __('My Services') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.availability'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.availability*') ? 'active' : '' }}"
        href="{{ route('doctor.availability') }}"><i class="fi-rs-clock mr-10"></i>{{ __('Availability') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.notifications'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.notifications*') ? 'active' : '' }}"
        href="{{ route('doctor.notifications') }}"><i class="fi-rs-bell mr-10"></i>{{ __('Notifications') }}</a>
    </li>
  @endif
  @if ($docCan('doctor.profile'))
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('doctor.profile*') ? 'active' : '' }}"
        href="{{ route('doctor.profile') }}"><i class="fi-rs-user mr-10"></i>{{ __('Profile') }}</a>
    </li>
  @endif
  <li class="nav-item">
    <form method="POST" action="{{ route('logout') }}" class="m-0">
      @csrf
      <button type="submit" class="nav-link text-start border-0 bg-transparent w-100 py-2">
        <i class="fi-rs-sign-out mr-10"></i>{{ __('Log out') }}
      </button>
    </form>
  </li>
</ul>
