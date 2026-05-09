@extends('frontend.layouts.site.master')
@section('title', 'About || Careon || Careon Laravel Template')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/sliding-text.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/newsletter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/why-choose.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/appiontment.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/counter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush
@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<x-page-header title="About Us" subtitle="About Us" />
<!--Page Header End-->

<!--About One Start -->
<section class="about-one about-six">
    <div class="container">
        <div class="about-one__inner">
            <div class="about-one__img-box">
                <div class="about-one__content-box wow slideInLeft" data-wow-delay="100ms"
                    data-wow-duration="2500ms">
                    <div class="section-title text-left sec-title-animation animation-style2">
                        <h6 class="section-title__tagline"><span class="icon-broken-bone"></span>About Us
                        </h6>
                        <h3 class="section-title__title title-animation">Health care maintenance or improvement
                        </h3>
                    </div>
                    <p class="about-one__text">Health care is a vital aspect of maintaining overall well-being,
                        encompassing a range of services from preventive care to treatment</p>
                    <ul class="about-one__points-box list-unstyled">
                        <li>
                            <div class="icon">
                                <span class="icon-left-arrows"></span>
                            </div>
                            <p>Where Health Matters Most</p>
                        </li>
                        <li>
                            <div class="icon">
                                <span class="icon-left-arrows"></span>
                            </div>
                            <p>Caring for You, Always</p>
                        </li>
                    </ul>
                </div>
                <div class="about-one__img">
                    <img src="{{ asset("frontend/assets/images/resources/about-one-img-1.jpg") }}" alt="">
                </div>
                <div class="about-one__working-hour wow slideInRight" data-wow-delay="100ms"
                    data-wow-duration="2500ms">
                    <h3 class="about-one__working-hour-title">Working Hours</h3>
                    <ul class="about-one__working-hour-list list-unstyled">
                        <li>
                            <span>Saturday-Sunday</span>
                            <p>9 Am To 5 Pm</p>
                        </li>
                        <li>
                            <span>Monday-Tuesday</span>
                            <p>1 Pm To 7 Pm</p>
                        </li>
                        <li>
                            <span>Wednesday-Thusday</span>
                            <p>2 Am To 6 Pm</p>
                        </li>
                        <li>
                            <span>Friday</span>
                            <p>Off Day</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!--About One End -->






<!--Feature One Start -->
<section class="feature-one">
    <div class="container">
        <div class="feature-one__inner">
            <div class="section-title text-center sec-title-animation animation-style1">
                <h6 class="section-title__tagline"><span class="icon-broken-bone"></span>Our Feature
                </h6>
                <h3 class="section-title__title title-animation">Your Wellness, Our Priority<br>
                    Empowering Healthier </h3>
            </div>
            <ul class="feature-one__feature-list list-unstyled">
                <li class="wow fadeInLeft" data-wow-delay="100ms">
                    <div class="feature-one__feature-list-left">
                        <div class="feature-one__feature-list-icon">
                            <span class="icon-quaity-care"></span>
                        </div>
                        <h3 class="feature-one__feature-list-title"><a
                                href="{{ url("wellSpring-wellness-center") }}">Quality Care </a></h3>
                    </div>
                    <div class="feature-one__feature-list-right">
                        <p class="feature-one__feature-list-sub-title">CareMed Clinic</p>
                        <div class="feature-one__feature-list-arrow">
                            <a href="{{ url("wellSpring-wellness-center") }}"><span class="icon-arrow-up"></span></a>
                        </div>
                    </div>
                </li>
                <li class="wow fadeInRight" data-wow-delay="200ms">
                    <div class="feature-one__feature-list-left">
                        <div class="feature-one__feature-list-icon">
                            <span class="icon-quaity-care-2"></span>
                        </div>
                        <h3 class="feature-one__feature-list-title"><a
                                href="{{ url("evergreen-medical-center") }}">Enhancing Quality Care </a></h3>
                    </div>
                    <div class="feature-one__feature-list-right">
                        <p class="feature-one__feature-list-sub-title">CareMed Clinic</p>
                        <div class="feature-one__feature-list-arrow">
                            <a href="{{ url("evergreen-medical-center") }}"><span class="icon-arrow-up"></span></a>
                        </div>
                    </div>
                </li>
                <li class="wow fadeInLeft" data-wow-delay="300ms">
                    <div class="feature-one__feature-list-left">
                        <div class="feature-one__feature-list-icon">
                            <span class="icon-quaity-care-3"></span>
                        </div>
                        <h3 class="feature-one__feature-list-title"><a
                                href="{{ url("pure-life-health-services") }}">Lives Through Care</a></h3>
                    </div>
                    <div class="feature-one__feature-list-right">
                        <p class="feature-one__feature-list-sub-title">CareMed Clinic</p>
                        <div class="feature-one__feature-list-arrow">
                            <a href="{{ url("pure-life-health-services") }}"><span class="icon-arrow-up"></span></a>
                        </div>
                    </div>
                </li>
                <li class="wow fadeInRight" data-wow-delay="400ms">
                    <div class="feature-one__feature-list-left">
                        <div class="feature-one__feature-list-icon">
                            <span class="icon-quaity-care-4"></span>
                        </div>
                        <h3 class="feature-one__feature-list-title"><a
                                href="{{ url("vitality-health-solutions") }}">Compassionate Care</a></h3>
                    </div>
                    <div class="feature-one__feature-list-right">
                        <p class="feature-one__feature-list-sub-title">CareMed Clinic</p>
                        <div class="feature-one__feature-list-arrow">
                            <a href="{{ url("vitality-health-solutions") }}"><span class="icon-arrow-up"></span></a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>
<!--Feature One End -->





<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection