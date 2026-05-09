@extends('frontend.layouts.master')
@section('title', $blog->title)
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/blog.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush
@section('content')

<x-strickyHeader />

<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ asset('frontend/assets/images/backgrounds/page-header-bg.jpg') }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $blog->title }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>-</span></li>
                <li><a href="{{ route('blog') }}">Blog</a></li>
                <li><span>-</span></li>
                <li>{{ \Illuminate\Support\Str::limit($blog->title, 45) }}</li>
            </ul>
        </div>
    </div>
</section>

<section class="blog-details">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="blog-details__left">
                    <div class="blog-details__img">
                        <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}">
                    </div>
                    <div class="blog-details__content">
                        <ul class="blog-details__meta list-unstyled">
                            <li>
                                <div class="icon"><span class="icon-calender"></span></div>
                                <p>{{ optional($blog->published_at)->format('F d, Y') ?? $blog->created_at->format('F d, Y') }}</p>
                            </li>
                            <li>
                                <div class="icon"><span class="icon-user"></span></div>
                                <p>By {{ $blog->author_name ?: 'Admin' }}</p>
                            </li>
                            <li>
                                <div class="icon"><span class="icon-file"></span></div>
                                <p>{{ $blog->category ?: 'General' }}</p>
                            </li>
                        </ul>

                        <h3 class="blog-details__title">{{ $blog->title }}</h3>
                        @if ($blog->excerpt)
                            <p class="blog-details__text-1">{{ $blog->excerpt }}</p>
                        @endif

                        <div class="blog-details__text-2">{!! nl2br(e($blog->content)) !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="sidebar">
                    <div class="sidebar__single sidebar__post-box">
                        <h3 class="sidebar__title">Recent News</h3>
                        <ul class="sidebar__post-list list-unstyled">
                            @forelse ($recentPosts as $post)
                                <li>
                                    <div class="sidebar__post-content">
                                        <h3>
                                            <a href="{{ route('blog.show', $post->slug) }}">{{ \Illuminate\Support\Str::limit($post->title, 70) }}</a>
                                        </h3>
                                        <p class="sidebar__post-date">
                                            <span class="icon-calender"></span>{{ optional($post->published_at)->format('d M, Y') ?? $post->created_at->format('d M, Y') }}
                                        </p>
                                    </div>
                                </li>
                            @empty
                                <li><div class="sidebar__post-content"><p>No recent posts.</p></div></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="sidebar__single sidebar__all-category">
                        <h3 class="sidebar__title">Category</h3>
                        <ul class="sidebar__all-category-list list-unstyled">
                            @forelse ($categories as $category)
                                <li>
                                    <a href="{{ route('blog') }}">
                                        <span class="icon-arrow-right"></span>{{ $category->category }} ({{ $category->total }})
                                    </a>
                                </li>
                            @empty
                                <li><a href="{{ route('blog') }}"><span class="icon-arrow-right"></span>General</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="sidebar__single sidebar__need-help">
                        <h3 class="sidebar__need-help-title">Need Help?Call Us</h3>
                        <div class="sidebar__need-help-icon">
                            <span class="icon-call"></span>
                        </div>
                        <div class="sidebar__need-help-call">
                            <a href="tel:+888178456765">(+888) 178 456 765</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection