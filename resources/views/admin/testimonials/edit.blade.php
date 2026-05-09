@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">Edit testimonial</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.testimonials') }}" class="btn">Cancel</a>
            <button type="submit" form="testimonial-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="testimonial-edit-form" action="{{ route('admin.testimonials.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Name</label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $testimonial->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="designation">Role / designation</label>
                <input id="designation" name="designation" type="text" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $testimonial->designation) }}">
                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label required" for="quote">Testimonial text</label>
                <textarea id="quote" name="quote" rows="5" class="form-control @error('quote') is-invalid @enderror" required>{{ old('quote', $testimonial->quote) }}</textarea>
                @error('quote')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="sort_order">Order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $testimonial->sort_order) }}">
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                  <option value="published" @selected(old('status', $testimonial->status) === 'published')>Published</option>
                  <option value="draft" @selected(old('status', $testimonial->status) === 'draft')>Draft</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="image">Photo</label>
                <input id="image" name="image" type="file" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              @if ($testimonial->image_url)
                <div class="col-12">
                  <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="img-fluid rounded" style="max-width: 200px;">
                </div>
              @endif
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
