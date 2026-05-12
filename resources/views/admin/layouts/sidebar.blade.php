<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    @php
      $currentAdmin = auth('admin')->user();
      $adminPermissions = $currentAdmin ? \App\Support\AdminPermissions::forAdmin($currentAdmin) : [];
      $can = static fn (string $permission): bool => in_array($permission, $adminPermissions, true);
      $siteLogo = \App\Models\AppSetting::getValue('site_logo');
      $defaultLogoUrl = asset('admin/assets/static/logo.svg');
      $siteLogoUrl = $defaultLogoUrl;
      $isCustomLogo = false;

      if ($siteLogo) {
        if (\Illuminate\Support\Str::startsWith($siteLogo, ['http://', 'https://'])) {
          $siteLogoUrl = $siteLogo;
          $isCustomLogo = true;
        } else {
          $normalizedLogoPath = ltrim($siteLogo, '/');
          $fullLogoPath = public_path($normalizedLogoPath);
          if (is_file($fullLogoPath)) {
            $siteLogoUrl = asset($normalizedLogoPath);
            $isCustomLogo = true;
          }
        }
      }
    @endphp
    <h1 class="navbar-brand navbar-brand-autodark">
      <a href="{{ route('admin.dashboard') }}">
        <img
          src="{{ $siteLogoUrl }}"
          alt="Admin"
          class="navbar-brand-image"
          style="{{ $isCustomLogo ? 'width:auto;height:32px;max-width:160px;object-fit:contain;filter:none;' : 'width:110px;height:32px;' }}"
          onerror="this.onerror=null;this.src='{{ $defaultLogoUrl }}';this.style.width='110px';this.style.height='32px';this.style.maxWidth='';this.style.objectFit='';this.style.filter='';">
      </a>
    </h1>
    <div class="navbar-nav flex-row d-lg-none">
      <div class="nav-item d-none d-lg-flex me-3">
        <div class="btn-list">
          <a href="https://github.com/tabler/tabler" class="btn" target="_blank" rel="noreferrer">
            <!-- Download SVG icon from http://tabler-icons.io/i/brand-github -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" />
            </svg>
            Source code
          </a>
          <a href="https://github.com/sponsors/codecalm" class="btn" target="_blank" rel="noreferrer">
            <!-- Download SVG icon from http://tabler-icons.io/i/heart -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-pink" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
            </svg>
            Sponsor
          </a>
        </div>
      </div>
      <div class="d-none d-lg-flex">
        <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"
          data-bs-placement="bottom">
          <!-- Download SVG icon from http://tabler-icons.io/i/moon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
          </svg>
        </a>
        <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
          data-bs-placement="bottom">
          <!-- Download SVG icon from http://tabler-icons.io/i/sun -->
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
            <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
          </svg>
        </a>
        <div class="nav-item dropdown d-none d-md-flex me-3">
          <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
            <!-- Download SVG icon from http://tabler-icons.io/i/bell -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
              <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
            </svg>
            <span class="badge bg-red"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Last updates</h3>
              </div>
              <div class="list-group list-group-flush list-group-hoverable">
                <div class="list-group-item">
                  <div class="row align-items-center">
                    <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
                    <div class="col text-truncate">
                      <a href="#" class="text-body d-block">Example 1</a>
                      <div class="d-block text-secondary text-truncate mt-n1">
                        Change deprecated html tags to text decoration classes (#29604)
                      </div>
                    </div>
                    <div class="col-auto">
                      <a href="#" class="list-group-item-actions">
                        <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="row align-items-center">
                    <div class="col-auto"><span class="status-dot d-block"></span></div>
                    <div class="col text-truncate">
                      <a href="#" class="text-body d-block">Example 2</a>
                      <div class="d-block text-secondary text-truncate mt-n1">
                        justify-content:between ⇒ justify-content:space-between (#29734)
                      </div>
                    </div>
                    <div class="col-auto">
                      <a href="#" class="list-group-item-actions show">
                        <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="row align-items-center">
                    <div class="col-auto"><span class="status-dot d-block"></span></div>
                    <div class="col text-truncate">
                      <a href="#" class="text-body d-block">Example 3</a>
                      <div class="d-block text-secondary text-truncate mt-n1">
                        Update change-version.js (#29736)
                      </div>
                    </div>
                    <div class="col-auto">
                      <a href="#" class="list-group-item-actions">
                        <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="row align-items-center">
                    <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
                    <div class="col text-truncate">
                      <a href="#" class="text-body d-block">Example 4</a>
                      <div class="d-block text-secondary text-truncate mt-n1">
                        Regenerate package-lock.json (#29730)
                      </div>
                    </div>
                    <div class="col-auto">
                      <a href="#" class="list-group-item-actions">
                        <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="nav-item dropdown">
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
          <span class="avatar avatar-sm" style="background-image: url(./static/avatars/000m.jpg)"></span>
          <div class="d-none d-xl-block ps-2">
            <div>Paweł Kuna</div>
            <div class="mt-1 small text-secondary">UI Designer</div>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <a href="#" class="dropdown-item">Status</a>
          <a href="{{ route('admin.profile') }}" class="dropdown-item">Profile</a>
          <a href="#" class="dropdown-item">Feedback</a>
          <div class="dropdown-divider"></div>
          <a href="{{ route('admin.settings') }}" class="dropdown-item">Settings</a>
          <a href="./sign-in.html" class="dropdown-item">Logout</a>
        </div>
      </div>
    </div>
    <div class="collapse navbar-collapse" id="sidebar-menu">
      <ul class="navbar-nav pt-lg-3">
        @if ($can('dashboard.view'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
            </span>
            <span class="nav-link-title">Dashboard</span>
          </a>
        </li>
        @endif
        @if ($can('pages.manage'))
        <li class="nav-item dropdown {{ request()->routeIs('admin.slides*') || request()->routeIs('admin.blogs*') || request()->routeIs('admin.faqs*') || request()->routeIs('admin.testimonials*') || request()->routeIs('admin.abouts*') || request()->routeIs('admin.page-headers*') ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle" href="#navbar-pages" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="false">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 9l6 0" /><path d="M9 13l6 0" /><path d="M9 17l6 0" /></svg>
            </span>
            <span class="nav-link-title">Pages</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item {{ request()->routeIs('admin.slides*') ? 'active' : '' }}" href="{{ route('admin.slides') }}">Homepage slides</a>
            <a class="dropdown-item {{ request()->routeIs('admin.blogs*') ? 'active' : '' }}" href="{{ route('admin.blogs') }}">Blog</a>
            <a class="dropdown-item {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}" href="{{ route('admin.faqs') }}">FAQs</a>
            <a class="dropdown-item {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}" href="{{ route('admin.testimonials') }}">Testimonials</a>
            <a class="dropdown-item {{ request()->routeIs('admin.abouts*') ? 'active' : '' }}" href="{{ route('admin.abouts') }}">About</a>
            <div class="dropdown-divider"></div>
            <h6 class="dropdown-header">Page headers</h6>
            <a class="dropdown-item {{ request()->routeIs('admin.page-headers.about*') ? 'active' : '' }}" href="{{ route('admin.page-headers.about') }}">About</a>
            <a class="dropdown-item {{ request()->routeIs('admin.page-headers.appointment*') ? 'active' : '' }}" href="{{ route('admin.page-headers.appointment') }}">Appointment</a>
            <a class="dropdown-item {{ request()->routeIs('admin.page-headers.contact*') ? 'active' : '' }}" href="{{ route('admin.page-headers.contact') }}">Contact</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.doctor', 'admin.page-headers.doctor.update', 'admin.page-headers.doctor.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.doctor') }}">Doctors</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.doctor-details', 'admin.page-headers.doctor-details.update', 'admin.page-headers.doctor-details.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.doctor-details') }}">Doctor details</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.faq', 'admin.page-headers.faq.update', 'admin.page-headers.faq.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.faq') }}">FAQ</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.pricing', 'admin.page-headers.pricing.update', 'admin.page-headers.pricing.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.pricing') }}">Pricing</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.products', 'admin.page-headers.products.update', 'admin.page-headers.products.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.products') }}">Products</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.product-show', 'admin.page-headers.product-show.update', 'admin.page-headers.product-show.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.product-show') }}">Product detail</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.services', 'admin.page-headers.services.update', 'admin.page-headers.services.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.services') }}">Our Services</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.service-show', 'admin.page-headers.service-show.update', 'admin.page-headers.service-show.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.service-show') }}">Service detail</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.testimonials', 'admin.page-headers.testimonials.update', 'admin.page-headers.testimonials.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.testimonials') }}">Testimonials</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.testimonial-show', 'admin.page-headers.testimonial-show.update', 'admin.page-headers.testimonial-show.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.testimonial-show') }}">Testimonial detail</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.login-page', 'admin.page-headers.login-page.update', 'admin.page-headers.login-page.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.login-page') }}">Login page</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.sign-up-page', 'admin.page-headers.sign-up-page.update', 'admin.page-headers.sign-up-page.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.sign-up-page') }}">Sign-up page</a>
            <a class="dropdown-item {{ request()->routeIs(['admin.page-headers.not-found', 'admin.page-headers.not-found.update', 'admin.page-headers.not-found.reset']) ? 'active' : '' }}" href="{{ route('admin.page-headers.not-found') }}">404 page</a>
          </div>
        </li>
        @endif
        @if ($can('appointments.manage'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.appointments') ? 'active' : '' }}" href="{{ route('admin.appointments') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
            </span>
            <span class="nav-link-title">Appointments</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.appointments.calendar') ? 'active' : '' }}" href="{{ route('admin.appointments.calendar') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4m0 1a1 1 0 0 1 1 -1h8a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-8a1 1 0 0 1 -1 -1z" /><path d="M11 8h2" /><path d="M10 12h4" /><path d="M10 16h4" /></svg>
            </span>
            <span class="nav-link-title">Calendar</span>
          </a>
        </li>
        @endif
        @if ($can('inquiries.manage'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.inquiries*') ? 'active' : '' }}" href="{{ route('admin.inquiries') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-6l-4 4v-4h-4a2 2 0 0 1 -2 -2v-9z" /><path d="M8 9h8" /><path d="M8 13h5" /></svg>
            </span>
            <span class="nav-link-title">Inquiries</span>
          </a>
        </li>
        @endif
        @if ($can('registrations.manage'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.registrations*') ? 'active' : '' }}" href="{{ route('admin.registrations') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a6 6 0 0 1 12 0v2" /><path d="M19 9l2 2l-2 2" /></svg>
            </span>
            <span class="nav-link-title">Registrations</span>
          </a>
        </li>
        @endif
        @if ($can('staff.manage'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.staffs*') ? 'active' : '' }}" href="{{ route('admin.staffs') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7a4 4 0 1 0 0 -8a4 4 0 0 0 0 8z" /><path d="M17 11a4 4 0 1 0 0 -8a4 4 0 0 0 0 8z" /><path d="M9 21v-2a4 4 0 0 1 4 -4h2" /><path d="M17 21v-2a4 4 0 0 0 -4 -4h-2" /></svg>
            </span>
            <span class="nav-link-title">Staff</span>
          </a>
        </li>
        @endif
        @if ($can('doctors.manage'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.doctors') ? 'active' : '' }}" href="{{ route('admin.doctors') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 0 -8a4 4 0 0 0 0 8z" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
            </span>
            <span class="nav-link-title">Doctors</span>
          </a>
        </li>
        @endif
        @if ($can('patients.view'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.patients') ? 'active' : '' }}" href="{{ route('admin.patients') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
            </span>
            <span class="nav-link-title">Patient</span>
          </a>
        </li>
        @endif
        @if ($can('roles.manage'))
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 9m0 2a2 2 0 1 0 0 -4a2 2 0 0 0 0 4" /><path d="M17 9m0 2a2 2 0 1 0 0 -4a2 2 0 0 0 0 4" /><path d="M7 13l0 5" /><path d="M17 13l0 5" /><path d="M7 16l10 0" /></svg>
            </span>
            <span class="nav-link-title">Role Management</span>
          </a>
        </li>
        @endif
        @if ($can('services.manage'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.services') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
            </span>
            <span class="nav-link-title">Services</span>
          </a>
        </li>
        @endif
        @if ($can('packages.manage'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.packages') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
            </span>
            <span class="nav-link-title">Treatment Packages</span>
          </a>
        </li>
        @endif
        @if ($can('subscriptions.manage'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.subscriptions') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M13 11l2 2l4 -4" /></svg>
            </span>
            <span class="nav-link-title">Subscriptions / Membership Plans</span>
          </a>
        </li>
        @endif
        @if ($can('products.manage'))
        <li class="nav-item dropdown {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle" href="#navbar-products" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="false">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M13 11l2 2l4 -4" /></svg>
            </span>
            <span class="nav-link-title">Products</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('admin.products') }}">All Products</a>
            <a class="dropdown-item {{ request()->routeIs('admin.products.pages') ? 'active' : '' }}" href="{{ route('admin.products.pages') }}">Catalog page</a>
            <a class="dropdown-item" href="{{ route('admin.products.categories') }}">Categories</a>
            <a class="dropdown-item" href="{{ route('admin.products.inventory') }}">Inventory</a>
            <a class="dropdown-item" href="{{ route('admin.products.stock-movements') }}">Stock Movements</a>
          </div>
        </li>
        @endif
        @if ($can('payments.manage'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.payments') }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 5v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a1 1 0 0 1 1 1v3" /></svg>
            </span>
            <span class="nav-link-title">Payments</span>
          </a>
        </li>
        @endif
        @if ($can('promotions.manage'))
        <li class="nav-item dropdown {{ request()->routeIs('admin.promotions*') ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle" href="#navbar-promotions" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="false"> {{-- promotions --}}
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 8h-2a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2v2l-4 2v-8l4 2v-2h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2z" /><path d="M17 8h2a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-2v2l4 2v-8l-4 2v-2z" /></svg>
            </span>
            <span class="nav-link-title">Promotions / Offers</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item {{ request()->routeIs('admin.promotions') ? 'active' : '' }}" href="{{ route('admin.promotions') }}">All Promotions</a>
            <a class="dropdown-item {{ request()->routeIs('admin.promotions.email*') ? 'active' : '' }}" href="{{ route('admin.promotions.email') }}">Email Blast</a>
          </div>
        </li>
        @endif
        @if ($can('reports.view'))
        <li class="nav-item dropdown {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="false"> {{-- reports --}}
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3a2 2 0 0 0 -4 -5h-4a2 2 0 0 0 -4 5v3a2 2 0 0 0 4 5h4a2 2 0 0 0 4 -5" /><path d="M12 7v-4" /></svg>
            </span>
            <span class="nav-link-title">Reports</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('admin.reports.revenue') }}">Revenue</a>
            <a class="dropdown-item" href="{{ route('admin.reports.appointments') }}">Appointments</a>
            <a class="dropdown-item" href="{{ route('admin.reports.services') }}">Services</a>
            <a class="dropdown-item" href="{{ route('admin.reports.patients') }}">Patients</a>
            <a class="dropdown-item" href="{{ route('admin.reports.subscriptions') }}">Subscriptions</a>
          </div>
        </li>
        @endif
        @if ($can('settings.manage'))
        <li class="nav-item dropdown {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle" href="#navbar-settings" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="{{ request()->routeIs('admin.settings*') ? 'true' : 'false' }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c.996 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
            </span>
            <span class="nav-link-title">Settings</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item {{ request()->routeIs('admin.settings') && ! request()->routeIs('admin.settings.footer*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">General</a>
            <a class="dropdown-item {{ request()->routeIs('admin.settings.footer*') ? 'active' : '' }}" href="{{ route('admin.settings.footer') }}">Site footer</a>
          </div>
        </li>
        @endif
      </ul>
    </div>
  </div>
</aside>