@extends('frontend.layouts.master')
@section('title', 'Services')
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush
@section('content')

<x-strickyHeader/>
 <!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $servicesPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::servicesBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Our Services' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                    <li>{{ $subtitle ?? 'Our Services' }}</li> -->
            </ul>
        </div>
    </div>
</section>

@include('frontend.partials.service-cards-grid', [
    'services' => $services,
    'sectionClass' => 'services-page-catalog',
    'emptyCtaRoute' => route('contact'),
    'emptyCtaLabel' => 'Contact us',
])

       

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection