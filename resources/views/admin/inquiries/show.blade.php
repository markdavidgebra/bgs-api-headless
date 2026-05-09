@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col">
          <div class="page-pretitle text-secondary">Inquiries</div>
          <h2 class="page-title mb-0">Inquiry from {{ $inquiry->name }}</h2>
          <div class="text-secondary small mt-1">Received {{ $inquiry->created_at->format('F j, Y \a\t g:i A') }}</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.inquiries') }}" class="btn">Back to list</a>
            <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" class="d-inline"
              onsubmit="return confirm('Delete this inquiry?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="text-secondary small">Name</div>
              <div class="fw-medium">{{ $inquiry->name }}</div>
            </div>
            <div class="col-md-6">
              <div class="text-secondary small">Email</div>
              <div><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
            </div>
            <div class="col-md-6">
              <div class="text-secondary small">Phone</div>
              <div>{{ $inquiry->phone ?: '—' }}</div>
            </div>
            <div class="col-md-6">
              <div class="text-secondary small">Preferred date</div>
              <div>{{ $inquiry->preferred_date ?: '—' }}</div>
            </div>
            <div class="col-12">
              <div class="text-secondary small">Message</div>
              <div class="mt-1" style="white-space: pre-wrap;">{{ $inquiry->message ?: '—' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
