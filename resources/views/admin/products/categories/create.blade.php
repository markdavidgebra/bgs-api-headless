@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Products</div>
          <h2 class="page-title">Add Category</h2>
          <div class="text-secondary small mt-1">Create a reusable category for products.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.products.categories') }}" class="btn">Cancel</a>
            <button type="submit" form="category-create-form" class="btn btn-primary">Save category</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="category-create-form" method="POST" action="{{ route('admin.products.categories.store') }}">
        @csrf
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label required" for="name">Category name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                  value="{{ old('name') }}" placeholder="e.g. Skincare" required>
                @error('name')
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
