@extends('frontend.layouts.master')
@section('title', 'Appoinment || Careon || Careon Laravel Template')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/sliding-text.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/newsletter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/why-choose.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/appiontment.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/counter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $meta = $about?->meta ?? [];
    $clinicHours = collect(data_get($meta, 'clinic_hours', []))
        ->map(function ($row) {
            $day = trim((string) data_get($row, 'day', ''));
            $time = trim((string) data_get($row, 'time', ''));
            if ($day === '' && $time === '') {
                return null;
            }

            return ['day' => $day, 'time' => $time];
        })
        ->filter()
        ->values();
    if ($clinicHours->isEmpty()) {
        $clinicHours = collect([
            ['day' => 'Monday - Friday', 'time' => '8:00 AM - 7:00 PM'],
            ['day' => 'Saturday', 'time' => '9:00 AM - 5:00 PM'],
            ['day' => 'Sunday', 'time' => 'By Appointment'],
            ['day' => 'Emergency', 'time' => '24/7 Support Line'],
        ]);
    }
@endphp

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $appointmentPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::appointmentBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Appoinment' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Appoinment' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Appoinment Page Start-->
<section class="appoinment-page">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="appoinment-page__left">
                    <h3 class="appoinment-page__title">Appointment Now</h3>
                    <form class="contact-form-validated appoinment-page__form" method="POST" action="assets/inc/sendemail.php" novalidate="novalidate">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="appoinment-page__input-box">
                                    <input type="text" name="name" placeholder="Your Name" required="">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="appoinment-page__input-box">
                                    <input type="email" name="email" placeholder="Your Email" required="">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="appoinment-page__input-box">
                                    <input type="text" name="number" placeholder="Your Number" required="">
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6">
                                <div class="appoinment-page__input-box">
                                    <input type="text" placeholder="mm/dd/yyy" name="date" id="datepicker">
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="appoinment-page__input-box text-message-box">
                                    <textarea name="message" placeholder="Message here.."></textarea>
                                </div>
                                <div class="appoinment-page__btn-box">
                                    <button type="submit" class="thm-btn">Appointment Now<span
                                            class="icon-plus"></span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="result"></div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5">
                <div class="appoinment-page__right">
                    <div class="appoinment-page__working-hour">
                        <h3 class="appoinment-page__working-hour-title">{{ data_get($meta, 'clinic_hours_title') ?: 'Clinic Hours' }}</h3>
                        <p class="appoinment-page__working-hour-text">{{ data_get($meta, 'clinic_hours_text') ?: 'Health care is a vital aspect of maintain overall well-being, encompassing a range' }}</p>
                        <ul class="appoinment-page__working-hour-list list-unstyled">
                            @foreach ($clinicHours as $row)
                                <li>
                                    <span>{{ data_get($row, 'day', '') }}</span>
                                    <p>{{ data_get($row, 'time', '') !== '' ? data_get($row, 'time', '') : 'Closed' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Appoinment Page End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection