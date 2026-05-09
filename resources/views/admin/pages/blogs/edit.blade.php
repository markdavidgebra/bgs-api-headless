@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">{{ old('title', $blog->title) }}</h2>
          <div class="text-secondary small mt-1">Update blog post content and publish settings.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.blogs') }}" class="btn">Cancel</a>
            <button type="submit" form="blog-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data" id="blog-edit-form">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Post details</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required" for="title">Title</label>
                    <input id="title" name="title" type="text" class="form-control @error('title') is-invalid @enderror"
                      value="{{ old('title', $blog->title) }}" required>
                    @error('title')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="slug">Slug</label>
                    <input id="slug" type="text" class="form-control bg-secondary-lt @error('slug') is-invalid @enderror"
                      value="{{ old('slug', $blog->slug) }}" disabled>
                    <input type="hidden" name="slug" id="slug-hidden" value="{{ old('slug', $blog->slug) }}">
                    @error('slug')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="excerpt">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="3"
                      class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    @error('excerpt')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label required" for="content">Content</label>
                    <textarea id="content" name="content" rows="10"
                      class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title mb-0">Publish</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label" for="author_name">Author</label>
                  <input id="author_name" name="author_name" type="text"
                    class="form-control @error('author_name') is-invalid @enderror"
                    value="{{ old('author_name', $blog->author_name ?: 'Admin') }}">
                  @error('author_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label" for="category">Category</label>
                  <input id="category" name="category" type="text"
                    class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $blog->category) }}">
                  @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label" for="status">Status</label>
                  <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="published" @selected(old('status', $blog->status) === 'published')>Published</option>
                    <option value="draft" @selected(old('status', $blog->status) === 'draft')>Draft</option>
                  </select>
                  @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-0">
                  <label class="form-label" for="published_at">Publish date</label>
                  <input id="published_at" name="published_at" type="datetime-local"
                    class="form-control @error('published_at') is-invalid @enderror"
                    value="{{ old('published_at', $blog->published_at?->format('Y-m-d\\TH:i')) }}">
                  @error('published_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Featured image</h3>
              </div>
              <div class="card-body">
                @if ($blog->image_url)
                  <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" class="img-fluid rounded mb-3">
                @endif
                <input id="image" name="image" type="file" accept="image/*"
                  class="form-control @error('image') is-invalid @enderror">
                @error('image')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      function slugify(text) {
        return text
          .toString()
          .normalize('NFKD')
          .replace(/[\u0300-\u036f]/g, '')
          .trim()
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
      }

      var titleEl = document.getElementById('title');
      var slugEl = document.getElementById('slug');
      var slugHidden = document.getElementById('slug-hidden');
      if (!titleEl || !slugEl || !slugHidden) return;

      function syncSlug() {
        var s = slugify(titleEl.value || '');
        slugEl.value = s;
        slugHidden.value = s;
      }

      titleEl.addEventListener('input', syncSlug);
      titleEl.addEventListener('change', syncSlug);
    })();
  </script>
@endpush
