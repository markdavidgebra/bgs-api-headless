@extends('frontend.layouts.master')
@section('title', 'Blog')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/blog.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/module-css/page-header.css') }}" />
@endpush
@section('content')

<x-strickyHeader />

<!--Page Header Start-->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url({{ asset('frontend/assets/images/backgrounds/page-header-bg.jpg') }});"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title ?? 'Blog ' }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span>-</span></li>
                <li>{{ $subtitle ?? 'Blog' }}</li>
            </ul>
        </div>
    </div>
</section>
<!--Page Header End-->

<!--Blog List Start-->
<section class="blog-list">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="blog-list__left">
                    @forelse ($posts as $post)
                        <div class="blog-list__single">
                            <div class="blog-list__img-box">
                                <div class="blog-list__img">
                                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                                    <div class="blog-list__date-box">
                                        <div class="blog-list__date-icon">
                                            <span class="icon-calender"></span>
                                        </div>
                                        <div class="blog-list__date-text">
                                            <p>{{ optional($post->published_at)->format('d M Y') ?? $post->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="blog-list__plus">
                                        <a href="{{ route('blog.show', $post->slug) }}"><i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="blog-list__content">
                                <ul class="blog-list__meta list-unstyled">
                                    <li>
                                        <div class="icon">
                                            <span class="icon-user"></span>
                                        </div>
                                        <p>{{ $post->author_name ?: 'Admin' }}</p>
                                    </li>
                                    <li>
                                        <div class="icon">
                                            <span class="icon-file"></span>
                                        </div>
                                        <p>{{ $post->category ?: 'General' }}</p>
                                    </li>
                                </ul>
                                <h3 class="blog-list__title">
                                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160) }}</p>
                                <div class="blog-list__read-more">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="thm-btn">Read More <span class="icon-plus"></span></a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="blog-list__single">
                            <div class="blog-list__content">
                                <h3 class="blog-list__title">No blog posts yet</h3>
                                <p>Once you create and publish posts from Admin > Pages > Blog, they will appear here.</p>
                            </div>
                        </div>
                    @endforelse

                    @if ($posts->hasPages())
                        <div class="blog-list__pagination">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xl-4 col-lg-5">
                <div class="sidebar">
                    <div class="sidebar__single sidebar__post-box">
                        <h3 class="sidebar__title">Recent News</h3>
                        <ul class="sidebar__post-list list-unstyled">
                            @forelse ($recentPosts as $recent)
                                <li>
                                    <div class="sidebar__post-content">
                                        <h3>
                                            <a href="{{ route('blog.show', $recent->slug) }}">{{ \Illuminate\Support\Str::limit($recent->title, 70) }}</a>
                                        </h3>
                                        <p class="sidebar__post-date">
                                            <span class="icon-calender"></span>
                                            {{ optional($recent->published_at)->format('d M, Y') ?? $recent->created_at->format('d M, Y') }}
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
                                    <a href="{{ url('blog') }}">
                                        <span class="icon-arrow-right"></span>{{ $category->category }} ({{ $category->total }})
                                    </a>
                                </li>
                            @empty
                                <li><a href="{{ url('blog') }}"><span class="icon-arrow-right"></span>General</a></li>
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
<!--Blog List End-->


<x-mobileMenu />
<x-searchPopup />
<x-scroll-to-top />
@endsection