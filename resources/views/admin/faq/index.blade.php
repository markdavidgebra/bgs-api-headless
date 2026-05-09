@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Pages</div>
          <h2 class="page-title mb-0">FAQs</h2>
          <div class="text-secondary small mt-1">Manage frequently asked questions shown on the frontend FAQ page.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">New FAQ</a>
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
                <th>Question</th>
                <th>Order</th>
                <th>Status</th>
                <th class="w-1">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($faqs as $faq)
                <tr>
                  <td>
                    <div class="fw-medium">{{ $faq->question }}</div>
                    <div class="text-secondary small">{{ \Illuminate\Support\Str::limit(strip_tags($faq->answer), 100) }}</div>
                  </td>
                  <td>{{ $faq->sort_order }}</td>
                  <td>
                    <span class="badge {{ $faq->status === 'published' ? 'bg-green-lt text-green' : 'bg-secondary-lt text-secondary' }}">
                      {{ ucfirst($faq->status) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-sm">Edit</a>
                      <form method="POST" action="{{ route('admin.faqs.destroy', $faq->id) }}" class="m-0"
                        onsubmit="return confirm('Delete this FAQ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-secondary py-5">No FAQs yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($faqs->hasPages())
          <div class="card-footer">{{ $faqs->links() }}</div>
        @endif
      </div>
    </div>
  </div>
@endsection
