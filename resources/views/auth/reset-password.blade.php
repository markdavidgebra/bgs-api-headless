@extends('frontend.layouts.master')
@section('title', 'Login || Careon || Careon Laravel Template')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/sliding-text.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/newsletter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/why-choose.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/appiontment.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/counter.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/shop.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush

@section('content')

@include('frontend.components.strickyHeader')

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ asset('frontend/assets/images/backgrounds/page-header-bg.jpg') }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Reset Password' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Reset Password' }}</li>
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Start Login One-->
<section class="login-one">
    <div class="container">
        <div class="login-one__form">
            <div class="inner-title text-center">
                <h2>Reset Password</h2>
            </div>
            <form id="login-one__form" name="Login-one_form" action="{{ route('password.store') }}" method="POST">
                @csrf
                 <!-- Password Reset Token -->
                 <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="email" name="email" id="formEmail" value="{{ old('email', $request->email) }}" placeholder="Email..."
                                    required="" autofocus>
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="password" name="password" id="formPassword"
                                    placeholder="Password..." required="">
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="password" name="password_confirmation" id="formPasswordConfirmation"
                                    placeholder="Confirm Password..." required="">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <button class="thm-btn" type="submit" data-loading-text="Please wait...">Reset Password
                                <span class="icon-right-arrow"></span></button>
                        </div>
                    </div>
                    

                </div>
            </form>
        </div>
    </div>
</section>
<!--End Login One-->



@include('frontend.components.mobileMenu')
@include('frontend.components.searchPopup')
@include('frontend.components.scroll-to-top')
@endsection