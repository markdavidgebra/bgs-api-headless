@extends('frontend.layouts.master')
@section('title', 'Clinical Staff Details')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/team.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $doctorDetailsPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::doctorDetailsBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Clinical Staff Details' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Clinical Staff Details' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Team Details Start-->
<div class="team-details">
    <div class="container">
        <div class="team-details__top">
            <div class="row">
                <div class="col-xl-6">
                    <div class="team-details__left">
                        <div class="team-details__img">
                            <img src="{{ asset('frontend/assets/images/team/team-details-img-1.jpg') }}" alt="">
                            <div class="team-details__social">
                                <a href="#"><span class="icon-facebook"></span></a>
                                <a href="#"><span class="icon-twitter"></span></a>
                                <a href="#"><span class="icon-instagram"></span></a>
                                <a href="#"><span class="icon-pinterest"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="team-details__right">
                        <h3 class="team-details__name">Dr.William Barbara</h3>
                        <p class="team-details__sub-title">Neurology Expert</p>
                        <ul class="team-details__counter list-unstyled">
                            <li>
                                <div class="icon">
                                    <span class="icon-patient"></span>
                                </div>
                                <div class="team-details__counter-content">
                                    <div class="team-details__counter-count-box">
                                        <h3 class="odometer" data-count="20">00</h3>
                                        <span>K</span>
                                    </div>
                                    <p class="team-details__counter-count-text">Patients</p>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-tofi"></span>
                                </div>
                                <div class="team-details__counter-content">
                                    <div class="team-details__counter-count-box">
                                        <h3 class="odometer" data-count="11">00</h3>
                                        <span>+ Year</span>
                                    </div>
                                    <p class="team-details__counter-count-text">Experiences</p>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-certification"></span>
                                </div>
                                <div class="team-details__counter-content">
                                    <div class="team-details__counter-count-box">
                                        <h3 class="odometer" data-count="10">00</h3>
                                        <span>+</span>
                                    </div>
                                    <p class="team-details__counter-count-text">Certification</p>
                                </div>
                            </li>
                        </ul>
                        <div class="team-details__biography">
                            <h3 class="team-details__biography-title">Biography</h3>
                            <p class="team-details__biography-text">Health care is a vital aspect of maintaining
                                overall well-being, encompassing a range of services from and the a preventive
                                care to treatment of illnesses. It focuses on promoting health, preventing
                                diseases, and the a the and providing medical attention when needed.Health care
                                is a vital aspect of maintaining overall well-being, they a encompassing a range
                                of services from preventive care to treatment </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="team-details__bottom">
            <ul class="team-details__bottom-list list-unstyled">
                <li>
                    <p>Spaciality:</p>
                    <span>Neurology Expert</span>
                </li>
                <li>
                    <p>Degrees:</p>
                    <span>General Madicine, Neurology Expert </span>
                </li>
                <li>
                    <p>Training:</p>
                    <span>Interventional Neurology Expert </span>
                </li>
                <li>
                    <p>Working Days:</p>
                    <span>Monday /Wednesday/Thusday/Saturdy</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<!--Team Details End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection