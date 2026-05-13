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
<style>
    .login-one__tabbed-card {
        position: relative;
        display: block;
        background-color: var(--white);
        box-shadow: 0px 0px 80px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .login-one__form .login-one__tabbed-card form {
        box-shadow: none;
        padding: 48px 50px 52px;
    }

    .login-one__tabs {
        display: flex;
        border-bottom: 1px solid rgba(var(--bdr-color-rgb), .35);
    }

    .login-one__tab {
        flex: 1;
        margin: 0;
        padding: 18px 16px;
        font-size: 18px;
        font-weight: 600;
        font-family: var(--font);
        color: var(--gray);
        background: rgba(var(--bdr-color-rgb), .35);
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .login-one__tab:hover {
        color: var(--heading-color);
        background: rgba(var(--bdr-color-rgb), .22);
    }

    .login-one__tab.is-active {
        color: var(--heading-color);
        background: var(--white);
        border-bottom-color: var(--base);
    }

    .login-one__tab:focus-visible {
        outline: 2px solid var(--base);
        outline-offset: -2px;
    }

    .login-one__tab-panel[hidden] {
        display: none !important;
    }

    .login-one__card-alert {
        margin: 0;
        padding: 16px 50px;
        border-bottom: 1px solid rgba(var(--bdr-color-rgb), .25);
        background-color: rgba(220, 53, 69, 0.08);
        border-left: none;
        border-right: none;
        color: #842029;
        font-size: 15px;
        font-family: var(--font);
        line-height: 1.5;
    }

    .login-one__card-alert ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .login-one__staff-note {
        text-align: center;
        margin-top: 8px;
    }

    .login-one__staff-note p {
        margin: 0;
        font-size: 15px;
        color: var(--gray);
        font-family: var(--font);
    }
</style>
@endpush

@section('content')

@include('frontend.components.strickyHeader')

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ $loginPageHeaderBgUrl ?? \App\Support\PageHeaderConfig::loginPageBackgroundUrl() }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <!-- <h2>{{ $title ?? 'Login Page' }}</h2> -->
            <ul class="thm-breadcrumb list-unstyled">
                <!-- <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Login Page' }}</li> -->
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

@php
    $loginTab = request('tab') === 'staff' ? 'staff' : 'patient';
@endphp
<!--Start Login One-->
<section class="login-one">
    <div class="container">
        <div class="login-one__form">
            <div class="inner-title text-center">
                <h2>Login Here</h2>
            </div>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <div class="login-one__tabbed-card">
                @if ($errors->any())
                    <div class="login-one__card-alert" role="alert">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="login-one__tabs" role="tablist" aria-label="{{ __('Login type') }}">
                    <button type="button" class="login-one__tab {{ $loginTab === 'patient' ? 'is-active' : '' }}"
                        id="login-tab-patient" role="tab" aria-selected="{{ $loginTab === 'patient' ? 'true' : 'false' }}"
                        aria-controls="login-panel-patient" data-login-tab="patient">{{ __('Patient') }}</button>
                    <button type="button" class="login-one__tab {{ $loginTab === 'staff' ? 'is-active' : '' }}"
                        id="login-tab-staff" role="tab" aria-selected="{{ $loginTab === 'staff' ? 'true' : 'false' }}"
                        aria-controls="login-panel-staff" data-login-tab="staff">{{ __('Staff') }}</button>
                </div>
                <div id="login-panel-patient" class="login-one__tab-panel" role="tabpanel" aria-labelledby="login-tab-patient"
                    @if ($loginTab !== 'patient') hidden @endif>
                    <form id="login-one__form" name="Login-one_form" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="email" name="email" id="formEmailPatient" value="{{ old('email') }}"
                                            placeholder="Email..." required="" {{ $loginTab === 'patient' ? 'autofocus' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="password" name="password" id="formPasswordPatient"
                                            placeholder="Password..." required="">
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
                                    <input type="checkbox" name="remember" id="saveinfo" checked="">
                                    <label for="saveinfo">
                                        <span></span>
                                        Remember me
                                    </label>
                                </div>
                                <div class="forget">
                                    <a href="{{ route('password.request') }}">Forget password?</a>
                                </div>
                            </div>

                            <div class="create-account text-center">
                                <p>Not registered yet? <a href="{{ route('register') }}">Create an Account</a></p>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="login-panel-staff" class="login-one__tab-panel" role="tabpanel" aria-labelledby="login-tab-staff"
                    @if ($loginTab !== 'staff') hidden @endif>
                    <form id="login-staff-form" name="Login_staff_form" action="{{ route('admin.login.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="email" name="email" id="formEmailStaff" value="{{ old('email') }}"
                                            placeholder="Email..." required="" {{ $loginTab === 'staff' ? 'autofocus' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="password" name="password" id="formPasswordStaff"
                                            placeholder="Password..." required="">
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
                                    <input type="checkbox" name="remember" id="saveinfoStaff">
                                    <label for="saveinfoStaff">
                                        <span></span>
                                        Remember me
                                    </label>
                                </div>
                                <div class="forget">
                                    <a href="{{ route('admin.password.request') }}">Forget password?</a>
                                </div>
                            </div>
                            <div class="login-one__staff-note">
                                <p>{{ __('Use this tab for staff or doctor portal sign-in.') }}</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!--End Login One-->

@push('scripts')
<script>
    (function () {
        var tabs = document.querySelectorAll('[data-login-tab]');
        var panels = {
            patient: document.getElementById('login-panel-patient'),
            staff: document.getElementById('login-panel-staff')
        };
        if (!tabs.length || !panels.patient || !panels.staff) return;

        function activate(name) {
            tabs.forEach(function (btn) {
                var on = btn.getAttribute('data-login-tab') === name;
                btn.classList.toggle('is-active', on);
                btn.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.patient.hidden = name !== 'patient';
            panels.staff.hidden = name !== 'staff';
            try {
                history.replaceState(null, '', name === 'staff' ? '{{ url('/login') }}?tab=staff' : '{{ url('/login') }}');
            } catch (e) { /* ignore */ }
        }

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activate(btn.getAttribute('data-login-tab'));
            });
        });
    })();
</script>
@endpush



@include('frontend.components.mobileMenu')
@include('frontend.components.searchPopup')
@include('frontend.components.scroll-to-top')
@endsection