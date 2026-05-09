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
    <div class="page-header__bg" style="background-image: url({{ $loginPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::loginPageBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Login Page' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Login Page' }}</li>
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
                <h2>Login Here</h2>
            </div>
            <form id="login-one__form" name="Login-one_form" action="#" method="post">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="email" name="form_email" id="formEmail" placeholder="Email..."
                                    required="" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="text" name="form_password" id="formPassword"
                                    placeholder="Password..." required="" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <button class="thm-btn" type="submit" data-loading-text="Please wait...">Login
                                Here<span class="icon-right-arrow"></span></button>
                        </div>
                    </div>
                    <div class="remember-forget">
                        <div class="checked-box1">
                            <input type="checkbox" name="saveMyInfo" id="saveinfo" checked="">
                            <label for="saveinfo">
                                <span></span>
                                Remember me
                            </label>
                        </div>
                        <div class="forget">
                            <a href="#">Forget password?</a>
                        </div>
                    </div>

                    <div class="create-account text-center">
                        <p>Not registered yet? <a href="{{ url("sign-up") }}">Create an Account</a></p>
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