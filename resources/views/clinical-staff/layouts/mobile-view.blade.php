@php
    $pd ??= fn (string $path) => asset('patients/' . ltrim($path, '/'));
@endphp

<div class="mobile-header-active mobile-header-wrapper-style doctor-mobile-drawer">
  <div class="mobile-header-wrapper-inner">
    <div class="mobile-header-top">
      <div class="mobile-header-logo">
        <a href="{{ route('doctor.dashboard') }}"><img src="{{ $pd('imgs/theme/bgs.png') }}" alt="{{ config('app.name', 'BGS') }}" /></a>
      </div>
      <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
        <button type="button" class="close-style search-close" aria-label="{{ __('Close menu') }}">
          <i class="icon-top"></i>
          <i class="icon-bottom"></i>
        </button>
      </div>
    </div>
    <div class="mobile-header-content-area">
      <div class="mobile-menu-wrap mobile-header-border">
        <div class="dashboard-menu doctor-mobile-dashboard-menu px-3 pt-3 pb-4">
          @include('clinical-staff.layouts.sidebar-nav')
        </div>
      </div>
    </div>
  </div>
</div>
