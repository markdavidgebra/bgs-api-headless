@extends('frontend.layouts.master')
@section('title', 'Contact')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />

@endpush

@section('content')
@php($sf = $siteFooter ?? \App\Support\SiteFooterConfig::get())

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $contactPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::contactBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Contact' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Contact' }}</li>
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Contact Page Start-->
<section class="contact-page">
    <div class="container">
        <div class="row">
            <div class="col-xl-7 col-lg-7">
                <div class="contact-page__left">
                    <h3 class="contact-page__title">Appiontment Now</h3>
                    @if (session('inquiry_sent'))
                        <div class="alert alert-success mb-3" role="alert">Thank you. Your inquiry has been sent—we will get back to you soon.</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="contact-page__form contact-inquiry-form" method="POST" action="{{ route('contact.inquiry.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="contact-page__input-box">
                                    <input type="text" name="name" placeholder="Your Name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="contact-page__input-box">
                                    <input type="email" name="email" placeholder="Your Email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="contact-page__input-box">
                                    <input type="text" name="number" placeholder="Your Number" value="{{ old('number') }}" required>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="contact-page__input-box">
                                    <input type="text" placeholder="mm/dd/yyy" name="date" id="datepicker" value="{{ old('date') }}">
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="contact-page__input-box text-message-box">
                                    <textarea name="message" placeholder="Message here..">{{ old('message') }}</textarea>
                                </div>
                                <div class="contact-page__btn-box">
                                    <button type="submit" class="thm-btn">Appointment Now <span
                                            class="icon-plus"></span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-5 col-lg-5">
                <div class="contact-page__right">
                    <div class="section-title text-left sec-title-animation animation-style2">
                        <h6 class="section-title__tagline"><span class="icon-broken-bone"></span>Get In Touch
                        </h6>
                        <h3 class="section-title__title title-animation">Health First Always
                        </h3>
                    </div>
                    <p class="contact-page__text">Health care is a vital aspect of maintaining overall wel
                        encompassing a range of services from preventive care to treatment </p>
                    <ul class="contact-page__contact-list list-unstyled">
                        <li>
                            <div class="icon">
                                <span class="icon-call"></span>
                            </div>
                            <div class="content">
                                <h3>{{ $sf['contact_phone_label'] ?? 'Phone' }}</h3>
                                <p><a href="{{ \App\Support\SiteFooterConfig::telHref($sf['contact_phone'] ?? '') }}">{{ $sf['contact_phone'] ?? '' }}</a></p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <span class="icon-envolope"></span>
                            </div>
                            <div class="content">
                                <h3>{{ $sf['contact_email_label'] ?? 'Email' }}</h3>
                                <p><a href="{{ \App\Support\SiteFooterConfig::mailtoHref($sf['contact_email'] ?? '') }}">{{ $sf['contact_email'] ?? '' }}</a></p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <span class="icon-pin"></span>
                            </div>
                            <div class="content">
                                <h3>{{ $sf['contact_address_label'] ?? 'Location' }}</h3>
                                <p>{{ $sf['contact_address'] ?? '' }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Contact Page End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection