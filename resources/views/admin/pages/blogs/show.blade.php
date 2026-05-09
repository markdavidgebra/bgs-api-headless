@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">{{ $blog->title }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.blogs') }}" class="btn">Back</a>
            <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-primary">Edit</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <div class="mb-3 text-secondary">
            <strong>Slug:</strong> /{{ $blog->slug }}<br>
            <strong>Author:</strong> {{ $blog->author_name ?: 'Admin' }}<br>
            <strong>Category:</strong> {{ $blog->category ?: '—' }}<br>
            <strong>Status:</strong> {{ ucfirst($blog->status) }}<br>
            <strong>Published:</strong> {{ $blog->published_at?->format('M d, Y H:i') ?? '—' }}
          </div>

          @if ($blog->image_url)
            <div class="mb-3">
              <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" class="img-fluid rounded">
            </div>
          @endif

          @if ($blog->excerpt)
            <p class="lead">{{ $blog->excerpt }}</p>
          @endif

          <div>{!! nl2br(e($blog->content)) !!}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
