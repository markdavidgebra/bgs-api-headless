@extends('frontend.layouts.master')

@section('content')
    @include('frontend.components.strickyHeader')


<!--Main Slider Start-->
@include('frontend.pages.home.sections.sec-1-main-slider')
@include('frontend.pages.home.sections.sec-2-sliding-text')
@include('frontend.pages.home.sections.sec-3-about')
@include('frontend.pages.home.sections.sec-4-services')
<!-- @include('frontend.pages.home.sections.sec-5-sliding-text-two') -->
<!-- @include('frontend.pages.home.sections.sec-6-project') -->
<!-- @include('frontend.pages.home.sections.sec-7-counter') -->
<!-- @include('frontend.pages.home.sections.sec-8-why-choose') -->
@include('frontend.pages.home.sections.sec-9-team')
<!-- @include('frontend.pages.home.sections.sec-10-appointment') -->
<!-- @include('frontend.pages.home.sections.sec-11-faq') -->
<!-- @include('frontend.pages.home.sections.sec-12-newsletter') -->
<!-- @include('frontend.pages.home.sections.sec-13-testimonial') -->
@include('frontend.pages.home.sections.sec-14-blog')
<!--Main Slider End-->


<!--Sliding Text Start-->
@include('frontend.pages.home.sections.sec-2-sliding-text')
<!--Sliding Text End-->

<!--About Two Start-->
@include('frontend.pages.home.sections.sec-3-about')
<!--About Two End-->

<!--Services Two Start-->
@include('frontend.pages.home.sections.sec-4-services')
<!--Services Two End-->

<!--Sliding Text Two Start-->
@include('frontend.pages.home.sections.sec-5-sliding-text-two')
<!--Sliding Text Two End-->

<!--Project Two Start -->
@include('frontend.pages.home.sections.sec-6-project')
<!--Project Two End -->

<!--Counter One Start -->
@include('frontend.pages.home.sections.sec-7-counter')
<!--Counter One End -->

<!--Why Choose One Start -->
@include('frontend.pages.home.sections.sec-8-why-choose')
<!--Why Choose One End -->

<!--Team Two Start -->
@include('frontend.pages.home.sections.sec-9-team')
<!--Team Two End -->

<!--Appiontment One Start -->
@include('frontend.pages.home.sections.sec-10-appointment')
<!--Appiontment One End -->

<!--FAQ Two Start -->
@include('frontend.pages.home.sections.sec-11-faq')
<!--FAQ Two End -->

<!--Newsletter One Start -->
@include('frontend.pages.home.sections.sec-12-newsletter')
<!--Newsletter One End -->

<!--Testimonial Two Start -->
@include('frontend.pages.home.sections.sec-13-testimonial')
<!--Testimonial Two End -->

<!--Blog Two Start -->
@include('frontend.pages.home.sections.sec-14-blog')    
<!--Blog Two End -->

<!--Project Two Start -->
@include('frontend.pages.home.sections.sec-6-project')
<!--Project Two End -->
@include('frontend.pages.home.sections.sec-14-blog')

@include('frontend.components.mobileMenu')
@include('frontend.components.searchPopup')
@include('frontend.components.scroll-to-top')
@endsection