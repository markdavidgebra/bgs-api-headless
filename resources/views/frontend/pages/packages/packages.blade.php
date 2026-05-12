@extends('frontend.layouts.master')
@section('title', 'Packages')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/packages-page.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@php
    $packageCardFallbackImage = asset('frontend/assets/images/resources/about-one-img-1.jpg');
@endphp

@section('content')

<x-strickyHeader />

<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $packagesPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::servicesBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>Packages</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>Treatment packages</li>
            </ul>
        </div>
    </div>
</section>

<section class="packages-catalog">
    <div class="container">
        <div class="section-title-three text-center sec-title-animation animation-style1">
            <h6 class="section-title-three__tagline">Treatment packages</h6>
            <h3 class="section-title-three__title title-animation">Curated bundles for<br> your care goals</h3>
            <p class="pricing-one__intro">Each package combines selected services into one clear bundle. Read more for full details, or book when you are ready.</p>
        </div>
        <div class="row g-4 justify-content-center packages-catalog__grid">
            @forelse ($packages as $package)
                @php
                    $delayMs = 100 + $loop->index * 100;
                    $animClass = match ($loop->index % 3) {
                        0 => 'fadeInLeft',
                        1 => 'fadeInUp',
                        default => 'fadeInRight',
                    };
                    $maxMins = $package->services->pluck('duration_minutes')->filter()->max();
                    $totalSessions = $package->total_sessions;
                    $metaParts = array_values(array_filter([
                        $maxMins ? (int) $maxMins.' mins' : null,
                        $totalSessions ? $totalSessions.' session'.($totalSessions === 1 ? '' : 's') : null,
                    ]));
                    $metaLine = $metaParts !== [] ? implode(' · ', $metaParts) : ($package->validity_label ?? 'Treatment package');
                    $excerpt = filled($package->description)
                        ? \Illuminate\Support\Str::limit(strip_tags($package->description), 140)
                        : 'View details and session inclusions for this package.';
                    $img = $package->image_url ?: $packageCardFallbackImage;
                @endphp
                <div class="col-xl-4 col-lg-6 d-flex wow {{ $animClass }}" data-wow-delay="{{ $delayMs }}ms">
                    <article class="package-card w-100">
                        <a href="{{ route('our-packages.show', $package) }}" class="package-card__media d-block text-decoration-none">
                            <img src="{{ $img }}" alt="{{ $package->name }}" loading="lazy" width="480" height="360">
                        </a>
                        <div class="package-card__body">
                            <h3 class="package-card__title">{{ $package->name }}</h3>
                            @if ($package->price !== null)
                                <p class="package-card__price-note">From ₱{{ number_format((float) $package->price, 0) }}</p>
                            @endif
                            <p class="package-card__meta">{{ $metaLine }}</p>
                            <p class="package-card__text">{{ $excerpt }}</p>
                            <div class="package-card__actions">
                                <a href="{{ route('our-packages.show', $package) }}" class="package-card__btn">
                                    Read more <span class="icon-plus"></span>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="packages-catalog__empty">
                        <h4>Packages coming soon</h4>
                        <p>Active treatment packages will appear here. Tell us what you need and we will help you choose.</p>
                        <a href="{{ route('contact') }}" class="package-card__btn">Contact us <span class="icon-plus"></span></a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
