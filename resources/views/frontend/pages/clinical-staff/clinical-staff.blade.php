@extends('frontend.layouts.master')
@section('title', 'Clinical staff')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/team.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/appiontment.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

<x-strickyHeader />



<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $doctorPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::doctorBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Clinical staff' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Clinical staff' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Team Two Start -->
<section class="team-two">
    <div class="container">
        <div class="row">
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInLeft" data-wow-delay="100ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-1.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.William Barbara</a></h3>
                        <p class="team-two__sub-title">Neurology Expert</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-2.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.Richard Susan</a></h3>
                        <p class="team-two__sub-title">Dental Care</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInRight" data-wow-delay="300ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-3.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.Joseph Jessica</a></h3>
                        <p class="team-two__sub-title">Eye Expert</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInLeft" data-wow-delay="400ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-4.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.Mukesh Kummer</a></h3>
                        <p class="team-two__sub-title">Heart Spacialist</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="500ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-5.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.David Jons</a></h3>
                        <p class="team-two__sub-title">Nero Spacialist</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
            <!--Team Two Single Start-->
            <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInRight" data-wow-delay="600ms">
                <div class="team-two__single">
                    <div class="team-two__img-box">
                        <div class="team-two__img">
                            <img src="{{ asset('frontend/assets/images/team/team-2-6.jpg') }}" alt="">
                        </div>
                        <div class="team-two__plus-and-social">
                            <div class="team-two__plus">
                                <span class="icon-plus"></span>
                            </div>
                            <div class="team-two__social">
                                <a href="{{ url("doctor-details") }}"><span class="icon-facebook"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-twitter"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-instagram"></span></a>
                                <a href="{{ url("doctor-details") }}"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-two__content">
                        <h3 class="team-two__title"><a href="{{ url("doctor-details") }}">Dr.Andew Hope</a></h3>
                        <p class="team-two__sub-title">Medicine Specialists</p>
                    </div>
                </div>
            </div>
            <!--Team Two Single End-->
        </div>
    </div>
</section>
<!--Team Two End -->

<!--Appiontment One Start -->
<section class="appiontment-one appiontment-four">
    <div class="container">
        <div class="appiontment-one__inner">
            <div class="appiontment-one__img">
                <img src="{{ asset('frontend/assets/images/resources/appiontment-one-img-1.jpg') }}" alt="">
                <div class="appiontment-one__appoin-and-working-hour">
                    <div class="appiontment-one__appion-box wow slideInLeft" data-wow-delay="100ms"
                        data-wow-duration="2500ms">
                        <h3 class="appiontment-one__appion-title">Appiontment Now</h3>
                        <form class="contact-form-validated appiontment-one__appion-form" method="POST" action="assets/inc/sendemail.php" novalidate="novalidate">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="text" name="name" placeholder="Your Name" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="email" name="email" placeholder="Your Email" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box">
                                        <input type="text" name="number" placeholder="Your Number" required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="appiontment-one__appion-input-box text-message-box">
                                        <textarea name="message" placeholder="Message here.."></textarea>
                                    </div>
                                    <div class="appiontment-one__appion-btn-box">
                                        <button type="submit" class="thm-btn">Appointment Now<span
                                                class="icon-plus"></span></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="result"></div>
                    </div>
                    <div class="appiontment-one__working-hour wow slideInRight" data-wow-delay="100ms"
                        data-wow-duration="2500ms">
                        <h3 class="appiontment-one__working-hour-title">Working Hours</h3>
                        <p class="appiontment-one__working-hour-text">Health care is a vital aspect of maintain
                            overall well-being, encompassing a range</p>
                        <ul class="appiontment-one__working-hour-list list-unstyled">
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
    </div>
</section>
<!--Appiontment One End -->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection