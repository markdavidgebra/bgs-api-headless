@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Patients</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#patient-filters" role="button" aria-expanded="true"
              aria-controls="patient-filters">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5.5 5h13" />
                <path d="M5.5 12h13" />
                <path d="M5.5 19h13" />
                <path d="M4 5l0 .01" />
                <path d="M4 12l0 .01" />
                <path d="M4 19l0 .01" />
              </svg>
              Filters
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="patient-filters" method="GET" action="">
            <div class="col-lg-6">
              <label class="form-label" for="search">Search</label>
              <div class="input-icon">
                <span class="input-icon-addon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
                  </svg>
                </span>
                <input id="search" type="text" class="form-control" name="search" placeholder="Name, email, phone…"
                  value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-lg-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="subscription">Subscription</label>
              <input id="subscription" type="text" class="form-control" name="subscription"
                value="{{ request('subscription') }}" placeholder="Plan">
            </div>
            <div class="col-lg-1 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Phone</th>
                <th>Last appointment</th>
                <th>Subscription</th>
                <th>Status</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($patients as $patient)
                @php
                  $status = $patient->status ?? 'active';
                  $badge = $status === 'active' ? 'bg-green-lt' : 'bg-secondary-lt';
                  $lastAppt = $patient->latestAppointmentDateFromHistory();
                @endphp
                <tr>
                  <td>
                    <div class="d-flex py-1 align-items-center">
                      <span class="avatar me-2 rounded bg-azure-lt text-azure">{{ strtoupper(substr($patient->name ?? '?', 0, 1)) }}</span>
                      <div class="flex-fill">
                        <div class="fw-medium">{{ $patient->name }}</div>
                        <div class="text-secondary small">
                          @if ($patient->email)
                            <a href="mailto:{{ $patient->email }}" class="text-reset">{{ $patient->email }}</a>
                          @else
                            —
                          @endif
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>{{ $patient->phone ?: '—' }}</td>
                  <td class="text-secondary">{{ $lastAppt ?? '—' }}</td>
                  <td>{{ $patient->subscription ?: '—' }}</td>
                  <td><span class="badge {{ $badge }}">{{ ucfirst($status) }}</span></td>
                  <td>
                    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-sm btn-primary">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-secondary">No patients found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($patients instanceof \Illuminate\Pagination\LengthAwarePaginator && $patients->hasPages())
          <div class="card-footer d-flex align-items-center">
            {{ $patients->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

@endsection