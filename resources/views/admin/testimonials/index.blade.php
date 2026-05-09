@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">Testimonials</h2>
          <div class="text-secondary small mt-1">Manage testimonials shown on the frontend testimonials page.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">New testimonial</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Order</th>
                <th>Status</th>
                <th class="w-1">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($testimonials as $testimonial)
                <tr>
                  <td>
                    <div class="fw-medium">{{ $testimonial->name }}</div>
                    <div class="text-secondary small">{{ \Illuminate\Support\Str::limit(strip_tags($testimonial->quote), 90) }}</div>
                  </td>
                  <td>{{ $testimonial->designation ?: '—' }}</td>
                  <td>{{ $testimonial->sort_order }}</td>
                  <td>
                    <span class="badge {{ $testimonial->status === 'published' ? 'bg-green-lt text-green' : 'bg-secondary-lt text-secondary' }}">
                      {{ ucfirst($testimonial->status) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.testimonials.show', $testimonial->id) }}" class="btn btn-sm btn-primary">View</a>
                      <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" class="m-0"
                        onsubmit="return confirm('Delete this testimonial?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-secondary py-5">No testimonials yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($testimonials->hasPages())
          <div class="card-footer">{{ $testimonials->links() }}</div>
        @endif
      </div>
    </div>
  </div>
@endsection
