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
<style>
  /* Match date/select fields to existing sign-up input style (shop.css) */
  .sign-up-one__form form input[type="date"],
  .sign-up-one__form form select {
    position: relative;
    display: block;
    width: 100%;
    height: 60px;
    padding-left: 30px;
    padding-right: 30px;
    border-radius: 10px;
    border: 1px solid rgba(var(--bdr-color-rgb), .50);
    background-color: rgba(var(--bdr-color-rgb), .50);
    color: var(--gray);
    font-size: 16px;
    font-family: var(--font);
    font-weight: 400;
    transition: all 500ms ease;
  }

  .sign-up-one__form form input[type="date"]:focus,
  .sign-up-one__form form select:focus {
    border-color: var(--base);
    background-color: var(--white);
    outline: none;
  }

  .sign-up-one__form form select {
    appearance: none;
    -webkit-appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, #6d6a83 50%), linear-gradient(135deg, #6d6a83 50%, transparent 50%);
    background-position: calc(100% - 28px) calc(50% - 3px), calc(100% - 20px) calc(50% - 3px);
    background-size: 8px 8px, 8px 8px;
    background-repeat: no-repeat;
    cursor: pointer;
  }

  .sign-up-one__form form .input-label {
    display: block;
    margin-bottom: 10px;
    color: var(--black);
    font-size: 15px;
    font-weight: 600;
    line-height: 1.2;
  }
</style>
@endpush

@section('content')
@include('frontend.components.strickyHeader')

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $signUpPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::signUpPageBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Sign Up' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Sign Up' }}</li> -->
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
                                <label class="input-label" for="formName">Name</label>
                                <input type="text" name="name" id="formName" placeholder="Name..."
                                 value="{{ old('name') }}" autofocus>
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formEmail">Email</label>
                                <input type="email" name="email" id="formEmail" placeholder="Email..."
                                     value="{{ old('email') }}">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formBirthdate">Birthdate</label>
                                <input type="date" name="birthdate" id="formBirthdate" value="{{ old('birthdate') }}" required>
                                <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formGender">Gender</label>
                                <select name="gender" id="formGender" required>
                                    <option value="" disabled @selected(old('gender') === null)>Select gender...</option>
                                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                                    <option value="other" @selected(old('gender') === 'other')>Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formAddress">Address</label>
                                <input type="text" name="address" id="formAddress" placeholder="Address..."
                                    value="{{ old('address') }}" required>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formPassword">Password</label>
                                <input type="password" name="password" id="formPassword"
                                    placeholder="Password..." >
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="form-group">
                            <div class="input-box">
                                <label class="input-label" for="formPasswordConfirmation">Confirm Password</label>
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
