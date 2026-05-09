@extends('frontend.layouts.master')
@section('title', 'Sign Up || Careon || Careon Laravel Template')
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
    <div class="page-header__bg" style="background-image: url({{ $signUpPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::signUpPageBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Sign Up' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Sign Up' }}</li>
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Start Sign Up One-->
<section class="sign-up-one">
    <div class="container">
        <div class="sign-up-one__form">
            <div class="inner-title text-center">
                <h2>Sign Up</h2>
            </div>
            <form id="sign-up-one__form" name="sign-up-one_form" action="{{ route('register') }}" method="POST">
                @csrf
                @if (session('status'))
                    <div class="alert alert-success mb-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
               
                <div class="row">
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="text" name="name" id="formName" placeholder="Name..."
                                 value="{{ old('name') }}" autofocus>
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="email" name="email" id="formEmail" placeholder="Email..."
                                     value="{{ old('email') }}">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="password" name="password" id="formPassword"
                                    placeholder="Password..." >
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <input type="password" name="password_confirmation" id="formPasswordConfirmation"
                                    placeholder="Confirm Password...">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <button class="thm-btn" type="submit" data-loading-text="Please wait...">Sign
                                Up <span class="icon-right-arrow"></span> </button>
                        </div>
                    </div>
                </div>

                <div class="create-account text-center">
                    <p>Already have an account? <a href="{{ route('login') }}">Login Here</a></p>
                </div>
            </form>
        </div>
    </div>
</section>
<!--End Sign Up One-->


@include('frontend.components.mobileMenu')
@include('frontend.components.searchPopup')
@include('frontend.components.scroll-to-top')
@endsection
