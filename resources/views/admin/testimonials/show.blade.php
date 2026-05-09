@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">{{ $testimonial->name }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.testimonials') }}" class="btn">Back</a>
            <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="btn btn-primary">Edit</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          @if ($testimonial->image_url)
            <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="img-fluid rounded mb-3" style="max-width: 320px;">
          @endif
          <div class="mb-2"><strong>Role:</strong> {{ $testimonial->designation ?: '—' }}</div>
          <div class="mb-2"><strong>Order:</strong> {{ $testimonial->sort_order }}</div>
          <div class="mb-3"><strong>Status:</strong> {{ ucfirst($testimonial->status) }}</div>
          <p class="mb-0">{{ $testimonial->quote }}</p>
        </div>
      </div>
    </div>
  </div>
@endsection
