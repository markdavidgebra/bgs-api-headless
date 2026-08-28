<ul class="main-menu__list">
    <!-- Home Dropdown -->
    <li class="{{ Request::is('/') ? 'current' : '' }}">
        <a href="{{ url('/') }}">Home</a>
        
    </li>

    <!-- About Us -->
    <li class="{{ Request::is('about-us') ? 'current' : '' }}">
        <a href="{{ route('about') }}">About Us</a>
    </li>
   
    

     <!-- Pricing Page -->
     <li class="{{ Request::is('pricing') ? 'current' : '' }}">
        <a href="{{ url('pricing') }}">Be A Member</a>
    </li>

    <!-- Services Dropdown -->
    <li class="{{ Request::is('our-services') ? 'current' : '' }}">
        <a href="{{ url('our-services') }}">Our Services</a>
    </li>

    <li class="{{ Request::is('our-packages') ? 'current' : '' }}">
        <a href="{{ route('our-packages') }}">Programs</a>
    </li>

    <li class="{{ Request::is('our-products') || Request::is('our-products/*') ? 'current' : '' }}">
        <a href="{{ route('our-products') }}">Products</a>
    </li>
<!-- Testimonials Page -->
<li class="{{ Request::is('our-testimonials') ? 'current' : '' }}">
        <a href="{{ route('testimonials') }}">Testimonials</a>
    </li>
   
    <li class="{{ Request::is('faq') ? 'current' : '' }}">
        <a href="{{ url('faq') }}">Faq</a>
    </li>

    <!-- Contact -->
  

    @if(auth('web')->check() || auth('clinical_staff')->check())
    <li class="{{ Request::is('patient', 'patient/*', 'doctor', 'doctor/*') ? 'current' : '' }}">
        <a href="{{ auth('clinical_staff')->check() ? route('clinical_staff.dashboard') : route('patient.dashboard') }}">Dashboard</a>
    </li>
    @else
    <!-- Members Dropdown -->
    <li class="dropdown {{ Request::is('login', 'register') ? 'current' : '' }}">
        <a href="#">Members</a>
        <ul>
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register Here</a></li>
        </ul>
    </li>
    @endif
</ul>

