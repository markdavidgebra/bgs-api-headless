@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Patient</div>
          <h2 class="page-title">New Registrations</h2>
          <div class="text-secondary small mt-1">Review and approve/disapprove pending sign-ups.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.registrations') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label" for="search">Search</label>
              <input id="search" type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name or email">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary">Search</button>
            </div>
            @if (request()->filled('search'))
              <div class="col-auto">
                <a href="{{ route('admin.registrations') }}" class="btn">Clear</a>
              </div>
            @endif
          </form>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Registered At</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($registrations as $registration)
                <tr>
                  <td class="fw-medium">{{ $registration->name }}</td>
                  <td>{{ $registration->email }}</td>
                  <td class="text-secondary">{{ $registration->created_at?->format('M j, Y h:i A') ?? '—' }}</td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <form method="POST" action="{{ route('admin.registrations.approve', $registration->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                      </form>
                      <form method="POST" action="{{ route('admin.registrations.disapprove', $registration->id) }}" class="d-inline" onsubmit="return confirm('Disapprove this registration?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">Disapprove</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-secondary py-4">No pending registrations found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($registrations instanceof \Illuminate\Pagination\LengthAwarePaginator && $registrations->hasPages())
          <div class="card-footer">
            {{ $registrations->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
