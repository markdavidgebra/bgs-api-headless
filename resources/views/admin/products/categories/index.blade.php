@extends('admin.layouts.master')

@section('content')
  @php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $categories */
  @endphp
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Products</div>
          <h2 class="page-title">Categories</h2>
          <div class="text-secondary small mt-1">List of product categories currently used by products.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.products') }}" class="btn">Back to products</a>
            <a href="{{ route('admin.products.categories.create') }}" class="btn btn-primary">Add category</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.products.categories') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="form-label" for="search">Search category</label>
              <input id="search" type="text" name="search" class="form-control" placeholder="e.g. Skincare" value="{{ request('search') }}">
            </div>
            <div class="col-auto">
              <button class="btn btn-primary" type="submit">Apply</button>
            </div>
            @if (request()->filled('search'))
              <div class="col-auto">
                <a class="btn" href="{{ route('admin.products.categories') }}">Clear</a>
              </div>
            @endif
          </form>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Category</th>
                <th class="text-end">Products</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($categories as $category)
                <tr>
                  <td class="fw-medium">{{ $category->name }}</td>
                  <td class="text-end">{{ number_format((int) $category->total_products) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" class="text-center text-secondary py-4">No product categories found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $categories->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection
