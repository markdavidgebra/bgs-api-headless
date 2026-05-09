<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Home | Careon | Laravel Template')</title>
    <meta name="description" content="Careon HTML 5 Template" />

    @include('partials.site-favicon', ['variant' => 'frontend'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/animate.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/custom-animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/swiper.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/font-awesome-all.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/flaticon.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/jarallax.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/jquery.magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/nice-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.carousel.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.theme.default.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/odometer.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/aos.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/jquery-ui.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/timePicker.css') }}" />

    <!-- Module Stylesheets -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/slider.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/banner.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/footer.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/feature.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/about.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/brand.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/service.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/project.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/team.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/faq.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/testimonial.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/blog.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/contact.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/sliding-text.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/newsletter.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/why-choose.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/appiontment.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/counter.css') }}" />

    <!-- Stacks for Custom Styles -->
    @stack('styles')

    <!-- Template Stylesheets -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/responsive.css') }}" />

    @stack('dark-styles')


</head>
<body class="{{ isset($bodyClass) ? $bodyClass . ' custom-cursor' : 'custom-cursor' }}">
    <!-- <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div> -->

    <div class="page-wrapper">
        @include('frontend.layouts.header')

        @yield('content')

        <!-- @include('frontend.components.loader') -->
        @include('frontend.layouts.footer')
        @include('frontend.components.scripts')
    </div>
</body>
</html>

