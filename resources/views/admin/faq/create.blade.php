@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">New FAQ</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.faqs') }}" class="btn">Cancel</a>
            <button type="submit" form="faq-create-form" class="btn btn-primary">Save FAQ</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form action="{{ route('admin.faqs.store') }}" method="POST" id="faq-create-form">
        @csrf
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label required" for="question">Question</label>
                <input id="question" name="question" type="text" class="form-control @error('question') is-invalid @enderror"
                  value="{{ old('question') }}" required>
                @error('question')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <label class="form-label required" for="answer">Answer</label>
                <textarea id="answer" name="answer" rows="6" class="form-control @error('answer') is-invalid @enderror" required>{{ old('answer') }}</textarea>
                @error('answer')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label" for="sort_order">Order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror"
                  value="{{ old('sort_order', 0) }}">
                @error('sort_order')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                  <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
                  <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                </select>
                @error('status')
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
