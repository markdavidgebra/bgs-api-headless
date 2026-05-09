@props([
    'title' => 'About Us',
    'subtitle' => null,
    'bg' => 'frontend/assets/images/backgrounds/page-header-bg.jpg',
])

<section class="page-header">
    <div class="page-header__bg" style="background-image: url('{{ asset($bg) }}');"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? $title }}</li>
            </ul>
        </div>
    </div>
</section>

