@extends('frontend.layouts.master')
@section('title', $service->name)
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $careBulletLines = static function (?string $text): array {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $text) ?: []
        )));
    };
    $beforeLines = $careBulletLines($service->before_care);
    $afterLines = $careBulletLines($service->after_care);
    $appointmentHref = $service->slug
        ? route('appointment', ['service' => $service->slug])
        : route('appointment');
@endphp

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $serviceShowPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::serviceShowBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $service->name }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li><a href="{{ route('our-services') }}">Our Services</a></li>
                <li><span>-</span></li>
                <li>{{ $service->name }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Service Details Start-->
<section class="service-details">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="service-details__left">
                    <div class="service-details__img">
                        <img src="{{ $service->image_url }}" alt="{{ $service->name }}" width="870" height="500" loading="eager">
                    </div>
                    <div class="service-details__content">
                        @php
                            $metaParts = array_filter([
                                $service->duration_minutes ? $service->duration_label : null,
                                $service->session_count
                                    ? $service->session_count.' session'.($service->session_count !== 1 ? 's' : '')
                                    : null,
                                filled($service->recovery_time) ? 'Recovery: '.$service->recovery_time : null,
                            ]);
                            $hasPrice = (float) $service->price > 0 || ($service->promo_price !== null && (float) $service->promo_price > 0);
                        @endphp

                        @if ($metaParts !== [])
                            <p class="service-details__text-1 mb-2">
                                {{ implode(' · ', $metaParts) }}
                            </p>
                        @endif

                        

                        @if ($service->is_bookable)
                            <div class="mb-4">
                                <a href="{{ $appointmentHref }}" class="thm-btn">
                                    Book this service <span class="icon-plus"></span>
                                </a>
                            </div>
                        @endif

                        <h3 class="service-details__title-1">
                            {{ $service->short_description ?: 'About this service' }}
                        </h3>

                        @if (filled($service->description))
                            <div class="service-details__text-1" style="white-space: pre-line;">{{ $service->description }}</div>
                        @endif

                        @if ($beforeLines !== [])
                            <h4 class="service-details__title-2">Before your visit</h4>
                            <div class="service-details__points-box">
                                <ul class="service-details__points-list list-unstyled">
                                    @foreach ($beforeLines as $line)
                                        <li>
                                            <div class="icon">
                                                <span class="icon-left-arrows"></span>
                                            </div>
                                            <p>{{ $line }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($afterLines !== [])
                            <h4 class="service-details__title-2">Aftercare</h4>
                            <div class="service-details__points-box">
                                <ul class="service-details__points-list list-unstyled">
                                    @foreach ($afterLines as $line)
                                        <li>
                                            <div class="icon">
                                                <span class="icon-left-arrows"></span>
                                            </div>
                                            <p>{{ $line }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (filled($service->notes))
                            <h4 class="service-details__title-3">Good to know</h4>
                            <div class="service-details__text-4" style="white-space: pre-line;">{{ $service->notes }}</div>
                        @endif

                        @if ($service->is_bookable)
                            <div class="mt-4">
                                <a href="{{ $appointmentHref }}" class="thm-btn">
                                    Book this service <span class="icon-plus"></span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5">
                <div class="service-details__right">
                    <div class="service-details__services-box">
                        <h3 class="service-details__service-title">Services</h3>
                        <ul class="service-details__service-list list-unstyled">
                            @foreach ($sidebarServices as $s)
                                @continue(! filled($s->slug))
                                <li @class(['active' => $s->is($service)])>
                                    <a href="{{ route('services.show', $s->slug) }}">
                                        <span class="icon-left-arrows"></span>{{ $s->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="service-details__need-help-inner">
                        <div class="service-details__need-help">
                            <div class="service-details__need-help-bg"
                                style="background-image: url({{ asset('frontend/assets/images/resources/service-details-need-help-bg.jpg') }});">
                            </div>
                            <h3 class="service-details__need-help-title">Need help? Call us</h3>
                            <div class="service-details__need-help-icon">
                                <span class="icon-call"></span>
                            </div>
                            <div class="service-details__need-help-call">
                                <a href="tel:+888178456765">(+888) 178 456 765</a>
                            </div>
                        </div>
                    </div>
                    @if ($service->is_bookable)
                        <div class="service-details__download-box">
                            <ul class="service-details__download-list list-unstyled">
                                <li>
                                    <a href="{{ $appointmentHref }}"><span class="fas fa-calendar-check me-1"></span>Book an appointment</a>
                                </li>
                                <li>
                                    <a href="{{ route('contact') }}"><span class="fas fa-envelope me-1"></span>Contact us</a>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<!--Service Details End-->

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
