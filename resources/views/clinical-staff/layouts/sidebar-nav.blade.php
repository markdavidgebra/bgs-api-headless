@php
  $portalDoctor = auth('clinical_staff')->user();
  $docCan = static fn (string $key): bool => $portalDoctor ? \App\Support\ClinicalStaffPermissions::can($portalDoctor, $key) : false;
  $horizontal = $horizontal ?? false;
@endphp
<ul @class([
  'nav',
  'mb-0',
  'flex-column' => ! $horizontal,
  'doctor-portal-nav-h d-flex flex-row flex-nowrap align-items-center' => $horizontal,
]) role="navigation" aria-label="{{ __('Clinical staff portal') }}">
  @if ($docCan('clinical_staff.dashboard'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.dashboard'),
      ]) href="{{ route('clinical_staff.dashboard') }}"><i class="fi-rs-home mr-10"></i>{{ __('Dashboard') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.appointments'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.appointments*'),
      ]) href="{{ route('clinical_staff.appointments') }}"><i class="fi-rs-calendar mr-10"></i>{{ __('Appointments / Schedule') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.patient_records'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.patient-records*'),
      ]) href="{{ route('clinical_staff.patient-records') }}"><i class="fi-rs-folder mr-10"></i>{{ __('Patient Records') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.treatment_notes'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.treatment-notes*'),
      ]) href="{{ route('clinical_staff.treatment-notes') }}"><i class="fi-rs-note mr-10"></i>{{ __('Treatment Notes') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.products'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.products*'),
      ]) href="{{ route('clinical_staff.products') }}"><i class="fi-rs-shopping-cart mr-10"></i>{{ __('Clinic products & stock') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.services'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.services*'),
      ]) href="{{ route('clinical_staff.services') }}"><i class="fi-rs-apps mr-10"></i>{{ __('My Services') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.availability'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.availability*'),
      ]) href="{{ route('clinical_staff.availability') }}"><i class="fi-rs-clock mr-10"></i>{{ __('Availability') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.notifications'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.notifications*'),
      ]) href="{{ route('clinical_staff.notifications') }}"><i class="fi-rs-bell mr-10"></i>{{ __('Notifications') }}</a>
    </li>
  @endif
  @if ($docCan('clinical_staff.profile'))
    <li @class(['nav-item', 'flex-shrink-0' => $horizontal])>
      <a @class([
        'nav-link',
        'doctor-portal-nav-h-link' => $horizontal,
        'active' => request()->routeIs('clinical_staff.profile*'),
      ]) href="{{ route('clinical_staff.profile') }}"><i class="fi-rs-user mr-10"></i>{{ __('Profile') }}</a>
    </li>
  @endif
  <li @class(['nav-item', 'flex-shrink-0' => $horizontal, 'doctor-portal-nav-h-logout' => $horizontal])>
    <form method="POST" action="{{ route('logout') }}" class="m-0 h-100">
      @csrf
      <button type="submit" @class([
        'nav-link text-start border-0 bg-transparent w-100 py-2',
        'doctor-portal-nav-h-link doctor-portal-nav-h-link--logout' => $horizontal,
      ])>
        <i class="fi-rs-sign-out mr-10"></i>{{ __('Log out') }}
      </button>
    </form>
  </li>
</ul>
