@extends('frontend.layouts.master')
@section('title', $testimonial->name . ' - Testimonial')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

<x-strickyHeader />

<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $testimonialShowPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::testimonialShowBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>Testimonial Details</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>-</span></li>
                <li><a href="{{ route('testimonials') }}">Testimonials</a></li>
                <li><span>-</span></li>
                <li>{{ $testimonial->name }}</li> -->
            </ul>
        </div>
    </div>
</section>

<section class="testimonials-page">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="testimonial-four__single">
                    <div class="testimonial-four__quote">
                        <span class="icon-quote-2"></span>
                    </div>
                    <div class="testimonial-four__client-info">
                        <div class="testimonial-four__client-img">
                            <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}">
                        </div>
                        <div class="testimonial-four__client-content">
                            <h3>{{ $testimonial->name }}</h3>
                            <p>{{ $testimonial->designation ?: 'Client' }}</p>
                        </div>
                    </div>
                    <p class="testimonial-four__text">{{ $testimonial->quote }}</p>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="sidebar">
                    <div class="sidebar__single sidebar__post-box">
                        <h3 class="sidebar__title">More Testimonials</h3>
                        <ul class="sidebar__post-list list-unstyled">
                            @forelse ($recentTestimonials as $item)
                                <li>
                                    <div class="sidebar__post-content">
                                        <h3>
                                            <a href="{{ route('testimonials.show', $item->id) }}">{{ $item->name }}</a>
                                        </h3>
                                        <p class="sidebar__post-date">{{ $item->designation ?: 'Client' }}</p>
                                    </div>
                                </li>
                            @empty
                                <li>
                                    <div class="sidebar__post-content">
                                        <p>No additional testimonials yet.</p>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="sidebar__single sidebar__need-help">
                        <h3 class="sidebar__need-help-title">Need Help? Call Us</h3>
                        <div class="sidebar__need-help-icon">
                            <span class="icon-call"></span>
                        </div>
                        <div class="sidebar__need-help-call">
                            <a href="tel:+888178456765">(+888) 178 456 765</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection
