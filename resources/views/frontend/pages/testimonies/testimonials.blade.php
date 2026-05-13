@extends('frontend.layouts.master')
@section('title', 'Testimonials')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $testimonialsPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::testimonialsBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Testimonials' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Testimonials' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Testimonials Page Start-->
<section class="testimonials-page">
    <div class="container">
        <div class="row">
            @forelse ($testimonials as $testimonial)
                <div class="col-xl-6 col-lg-6 col-md-6">
                    <div class="testimonial-four__single">
                        <div class="testimonial-four__quote">
                            <span class="icon-quote-2"></span>
                        </div>
                        <div class="testimonial-four__client-info">
                            <div class="testimonial-four__client-img">
                                <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}">
                            </div>
                            <div class="testimonial-four__client-content">
                                <h3><a href="{{ route('testimonials.show', $testimonial->id) }}">{{ $testimonial->name }}</a></h3>
                                <p>{{ $testimonial->designation ?: 'Client' }}</p>
                            </div>
                        </div>
                        <p class="testimonial-four__text">{{ \Illuminate\Support\Str::limit($testimonial->quote, 170) }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="testimonial-four__single">
                        <p class="testimonial-four__text mb-0">No testimonials available yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!--Testimonials Page End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection