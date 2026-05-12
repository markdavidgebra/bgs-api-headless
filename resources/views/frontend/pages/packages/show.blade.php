@extends('frontend.layouts.master')
@section('title', $package->name.' — Packages')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/packages-page.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $packageCardFallbackImage = asset('frontend/assets/images/resources/about-one-img-1.jpg');
    $maxMins = $package->services->pluck('duration_minutes')->filter()->max();
    $totalSessions = $package->total_sessions;
    $metaParts = array_values(array_filter([
        $maxMins ? (int) $maxMins.' mins' : null,
        $totalSessions ? $totalSessions.' session'.($totalSessions === 1 ? '' : 's') : null,
    ]));
    $metaLine = $metaParts !== [] ? implode(' · ', $metaParts) : ($package->validity_label ?? 'Treatment package');
    $img = $package->image_url ?: $packageCardFallbackImage;
@endphp

@section('content')

<x-strickyHeader />

<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $packagesPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::servicesBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $package->name }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li><a href="{{ route('our-packages') }}">Packages</a></li>
                <li><span>-</span></li>
                <li>{{ \Illuminate\Support\Str::limit($package->name, 42) }}</li>
            </ul>
        </div>
    </div>
</section>

<section class="package-detail">
    <div class="container">
        <div class="package-detail__card">
            <div class="package-detail__media">
                <img src="{{ $img }}" alt="{{ $package->name }}" loading="eager" width="900" height="380">
            </div>
            <div class="package-detail__body">
                <h3 class="package-card__title mb-2">{{ $package->name }}</h3>
                @if ($package->price !== null)
                    <p class="package-card__price-note mb-1">
                        ₱{{ number_format((float) $package->price, 0) }}
                        @if ($package->original_price && (float) $package->original_price > (float) $package->price)
                            <span class="text-decoration-line-through text-secondary fw-normal ms-2">₱{{ number_format((float) $package->original_price, 0) }}</span>
                        @endif
                    </p>
                @endif
                <p class="package-card__meta">{{ $metaLine }}</p>
                @if (filled($package->description))
                    <div class="package-detail__prose">
                        {!! nl2br(e(strip_tags($package->description))) !!}
                    </div>
                @endif
                @if ($package->services->isNotEmpty())
                    <h4 class="h6 fw-bold mt-4 mb-2" style="color: #2a2426;">Included services</h4>
                    <ul class="package-detail__list">
                        @foreach ($package->services as $service)
                            <li>
                                <i class="fas fa-check me-2" style="color: var(--base); font-size: 0.85rem;"></i>
                                <span>{{ $service->name }}</span>
                                @if ((int) ($service->pivot->sessions ?? 0) > 0)
                                    <span class="text-secondary ms-1">— {{ (int) $service->pivot->sessions }} session{{ (int) $service->pivot->sessions === 1 ? '' : 's' }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="package-detail__actions">
                    <a href="{{ route('appointment') }}" class="thm-btn">Book an appointment <span class="icon-arrow-right"></span></a>
                    <a href="{{ route('our-packages') }}" class="thm-btn" style="background: transparent; color: var(--base); border: 1px solid rgba(var(--base-rgb), 0.35);">All packages</a>
                </div>
            </div>
        </div>
    </div>
</section>

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
