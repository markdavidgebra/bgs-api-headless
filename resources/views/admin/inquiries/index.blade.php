@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Contact</div>
          <h2 class="page-title mb-0">Inquiries</h2>
          <div class="text-secondary small mt-1">Messages submitted from the public contact page.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Received</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Preferred date</th>
                <th>Message</th>
                <th class="w-1">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($inquiries as $inquiry)
                <tr>
                  <td class="text-secondary small text-nowrap">{{ $inquiry->created_at->format('M j, Y g:i A') }}</td>
                  <td class="fw-medium">{{ $inquiry->name }}</td>
                  <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                  <td>{{ $inquiry->phone ?? '—' }}</td>
                  <td>{{ $inquiry->preferred_date ?: '—' }}</td>
                  <td>
                    <div class="text-secondary small" style="max-width: 280px;">{{ \Illuminate\Support\Str::limit($inquiry->message ?? '', 120) }}</div>
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="btn btn-sm">View</a>
                      <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" class="m-0"
                        onsubmit="return confirm('Delete this inquiry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-secondary py-5">No inquiries yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($inquiries->hasPages())
          <div class="card-footer">{{ $inquiries->links() }}</div>
        @endif
      </div>
    </div>
  </div>
@endsection
