@extends('frontend.layouts.master')

@section('content')
    @include('frontend.components.strickyHeader')


<!--Main Slider Start-->
@include('frontend.pages.home.sections.sec-1-main-slider')
@include('frontend.pages.home.sections.sec-2-sliding-text')
@include('frontend.pages.home.sections.sec-3-about')
@include('frontend.pages.home.sections.sec-4-services')

@include('frontend.pages.home.sections.sec-9-team')



@include('frontend.components.mobileMenu')
@include('frontend.components.searchPopup')
@include('frontend.components.scroll-to-top')
@endsection