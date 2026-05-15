<header class="main-header">
    <div class="main-header__wrapper">
        <nav class="main-menu">
            <div class="main-menu__wrapper">
                <div class="container">
                    @php
                        $siteLogo = \App\Models\AppSetting::getValue('site_logo');
                        $defaultLogo = asset('frontend/assets/images/resources/logo-3.png');
                        $logoUrl = $defaultLogo;

                        if ($siteLogo) {
                            if (\Illuminate\Support\Str::startsWith($siteLogo, ['http://', 'https://', '//'])) {
                                $logoUrl = $siteLogo;
                            } else {
                                $normalizedLogoPath = ltrim($siteLogo, '/');
                                if (is_file(public_path($normalizedLogoPath))) {
                                    $logoUrl = asset($normalizedLogoPath);
                                }
                            }
                        }
                    @endphp
                    <div class="main-menu__wrapper-inner">
                        <div class="main-menu__left">
                            <div class="main-menu__logo">
                                <a href="{{ url('/') }}" aria-label="Home">
                                    <img src="{{ $logoUrl }}" alt="Site logo" style="max-height: 64px; width: auto; max-width: 220px; object-fit: contain;" onerror="this.onerror=null;this.src='{{ $defaultLogo }}';">
                                </a>
                            </div>
                        </div>
                        <div class="main-menu__main-menu-box">
                            <a href="{{ url('#') }}" class="mobile-nav__toggler"><i class="fa fa-bars"></i></a>

                            @include('frontend.components.menuList')

                        </div>
                        <div class="main-menu__right">
                            <div class="main-menu__thm-btn">
                                <a href="{{ url('appointment') }}" class="thm-btn">Appointment Now <span class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

