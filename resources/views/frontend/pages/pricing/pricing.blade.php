@extends('frontend.layouts.master')
@section('title', 'Pricing')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/pricing.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $pricingPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::pricingBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Pricing' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Pricing' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Pricing One Start -->
<section class="pricing-one pricing-one--memberships">
    <div class="container">
        <div class="section-title-three text-center sec-title-animation animation-style1">
            <h6 class="section-title-three__tagline">Membership</h6>
            <h3 class="section-title-three__title title-animation">Membership made <br> simple and fair</h3>
            <p class="pricing-one__intro">Choose the pace that fits your life. Every tier spells out your benefits in plain language—so you always know what membership means for you.</p>
        </div>
        <div class="row g-4 justify-content-center align-items-stretch">
            @forelse ($membershipPlans as $plan)
                @php
                    $delayMs = 100 + $loop->index * 100;
                    $animClass = match ($loop->index % 3) {
                        0 => 'fadeInLeft',
                        1 => 'fadeInUp',
                        default => 'fadeInRight',
                    };
                    $billingLabel = match ($plan->billing_cycle) {
                        'monthly' => 'Billed monthly',
                        'yearly' => 'Billed yearly',
                        'quarterly' => 'Billed quarterly',
                        default => $plan->billing_cycle
                            ? \Illuminate\Support\Str::headline(str_replace('_', ' ', $plan->billing_cycle))
                            : null,
                    };
                    $middle = $loop->count % 2 === 1 && $loop->index === (int) (($loop->count - 1) / 2);
                @endphp
                <div class="col-xl-4 col-lg-4 col-md-6 d-flex wow {{ $animClass }}" data-wow-delay="{{ $delayMs }}ms">
                    <div class="pricing-one__single membership-card w-100 @if ($middle) pricing-one__single--featured @endif">
                        @if (filled($plan->type))
                            <span class="pricing-card-m__badge">{{ \Illuminate\Support\Str::headline($plan->type) }}</span>
                        @endif
                        <h3 class="pricing-one__price-pack-name pricing-card-m__title">{{ $plan->name }}</h3>
                        @if (filled($plan->description))
                            <p class="pricing-card-m__lede">{{ \Illuminate\Support\Str::limit(strip_tags($plan->description), 155) }}</p>
                        @endif
                        <p class="pricing-card-m__included">What's included</p>
                        <ul class="list-unstyled pricing-one__point pricing-card-m__list">
                            @if (filled($plan->max_usage_per_month))
                                <li>
                                    <div class="icon">
                                        <span class="fas fa-check"></span>
                                    </div>
                                    <div class="text">
                                        <p>Up to {{ (int) $plan->max_usage_per_month }} visit{{ (int) $plan->max_usage_per_month === 1 ? '' : 's' }} per month</p>
                                    </div>
                                </li>
                            @endif
                            @if ($plan->services->isNotEmpty())
                                @foreach ($plan->services->take(6) as $service)
                                    <li>
                                        <div class="icon">
                                            <span class="fas fa-check"></span>
                                        </div>
                                        <div class="text">
                                            <p>
                                                <span class="pricing-card-m__feature-name">{{ $service->name }}</span>
                                                @if ((int) ($service->pivot->sessions ?? 0) > 0)
                                                    <span class="pricing-card-m__feature-meta">{{ (int) $service->pivot->sessions }} session{{ (int) $service->pivot->sessions === 1 ? '' : 's' }} per cycle</span>
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            @elseif (filled($plan->description))
                                @php
                                    $descLines = array_slice(
                                        array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', strip_tags($plan->description)))),
                                        0,
                                        5
                                    );
                                    if ($descLines === []) {
                                        $descLines = [\Illuminate\Support\Str::limit(strip_tags($plan->description), 200)];
                                    }
                                @endphp
                                @foreach ($descLines as $line)
                                    <li>
                                        <div class="icon">
                                            <span class="fas fa-check"></span>
                                        </div>
                                        <div class="text">
                                            <p>{{ \Illuminate\Support\Str::limit($line, 120) }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <div class="icon">
                                        <span class="fas fa-check"></span>
                                    </div>
                                    <div class="text">
                                        <p>Contact us for a full benefit summary.</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                        <div class="pricing-one__btn-box pricing-card-m__cta mt-auto">
                            <a href="{{ route('contact') }}" class="thm-btn">Choose this plan <span class="icon-arrow-right"></span></a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 col-lg-8 mx-auto">
                    <div class="pricing-card-m pricing-card-m--empty text-center">
                        <h4 class="pricing-card-m__empty-title">Plans coming soon</h4>
                        <p class="pricing-card-m__empty-text">Active membership tiers will show here. We'd love to hear what you're looking for.</p>
                        <a href="{{ route('contact') }}" class="thm-btn">Talk to us <span class="icon-arrow-right"></span></a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!--Pricing One End -->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection