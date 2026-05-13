<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    @php
        $pd = fn (string $path) => asset('patients/' . ltrim($path, '/'));
    @endphp
    <meta charset="utf-8" />
    <title>@yield('title', 'Patient Dashboard')</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    @include('partials.site-favicon', [
        'defaultHref' => $pd('imgs/theme/favicon.svg'),
        'defaultType' => 'image/svg+xml',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Template CSS -->
   
    <link rel="stylesheet" href="{{ $pd('css/main.css') }}?v=6.0" />
    <link rel="stylesheet" href="{{ $pd('css/patient-mobile-drawer.css') }}?v=1.3" />
    <link rel="stylesheet" href="{{ $pd('css/bgs-portal-mobile-content.css') }}?v=1.1" />
</head>

<body>
    @include('patient.layouts.header')
    @include('patient.layouts.mobile-view')
    <!--End header-->

    @yield('content')

    @include('patient.layouts.footer')
    <!-- Preloader Start -->
    <!-- <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="text-center">
                    <img src="{{ $pd('imgs/theme/bgs.png') }}" alt="" />
                </div>
            </div>
        </div>
    </div> -->

    <!-- Vendor JS-->
    <script src="{{ $pd('js/vendor/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ $pd('js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ $pd('js/vendor/jquery-migrate-3.3.0.min.js') }}"></script>
    <script src="{{ $pd('js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $pd('js/plugins/slick.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery.syotimer.min.js') }}"></script>
    <script src="{{ $pd('js/plugins/waypoints.js') }}"></script>
    <script src="{{ $pd('js/plugins/wow.js') }}"></script>
    <script src="{{ $pd('js/plugins/perfect-scrollbar.js') }}"></script>
    <script src="{{ $pd('js/plugins/magnific-popup.js') }}"></script>
    <script src="{{ $pd('js/plugins/select2.min.js') }}"></script>
    <script src="{{ $pd('js/plugins/counterup.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery.countdown.min.js') }}"></script>
    <script src="{{ $pd('js/plugins/images-loaded.js') }}"></script>
    <script src="{{ $pd('js/plugins/isotope.js') }}"></script>
    <script src="{{ $pd('js/plugins/scrollup.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery.vticker-min.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery.theia.sticky.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery.elevatezoom.js') }}"></script>
    <script src="{{ $pd('js/plugins/slider-range.js') }}"></script>
    <script src="{{ $pd('js/plugins/jquery-ui.js') }}"></script>
    <script src="{{ $pd('js/plugins/custom-parallax.js') }}"></script>
    <script src="{{ $pd('js/plugins/TweenMax.min.js') }}"></script>
    <!-- Template  JS -->
    <script src="{{ $pd('js/main.js') }}?v=6.0"></script>
    <script src="{{ $pd('js/shop.js') }}?v=6.0"></script>
</body>

</html>