@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Doctors</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a class="btn" data-bs-toggle="collapse" href="#doctor-filters" role="button" aria-expanded="true"
              aria-controls="doctor-filters">
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
            <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">New doctor</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <form class="row g-3 align-items-end collapse show" id="doctor-filters" method="GET" action="">
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
                <input id="search" type="text" class="form-control" name="search" placeholder="Name, email, phone, bio…"
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
              <label class="form-label" for="specialty">Specialty</label>
              <input id="specialty" type="text" class="form-control" name="specialty" value="{{ request('specialty') }}"
                placeholder="e.g. Dermatology">
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
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Specialty</th>
                <th class="text-nowrap">Years exp.</th>
                <th>Bio</th>
                <th>Image</th>
                <th>Status</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($doctors as $doctor)
                @php
                  $status = $doctor->status ?? 'active';
                  $badge = $status === 'active' ? 'bg-green-lt' : 'bg-secondary-lt';
                  $imageUrl = $doctor->image_url;
                  $initial = strtoupper(substr($doctor->name ?? '?', 0, 1));
                @endphp
                <tr>
                  <td>
                    <div class="d-flex py-1 align-items-center">
                      @if ($imageUrl)
                        <span class="avatar me-2 rounded"
                          style="background-image: url({{ $imageUrl }})"></span>
                      @else
                        <span class="avatar me-2 rounded bg-azure-lt text-azure">{{ $initial }}</span>
                      @endif
                      <div class="fw-medium">
                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="text-reset">{{ $doctor->name }}</a>
                      </div>
                    </div>
                  </td>
                  <td class="text-secondary">
                    @if ($doctor->email)
                      <a href="mailto:{{ $doctor->email }}" class="text-reset">{{ $doctor->email }}</a>
                    @else
                      —
                    @endif
                  </td>
                  <td class="text-secondary">
                    @if ($doctor->phone)
                      <a href="tel:{{ $doctor->phone }}" class="text-reset">{{ $doctor->phone }}</a>
                    @else
                      —
                    @endif
                  </td>
                  <td>{{ $doctor->specialty ?? '—' }}</td>
                  <td class="text-secondary">{{ $doctor->experience_years !== null ? $doctor->experience_years : '—' }}</td>
                  <td>
                    @if ($doctor->bio)
                      <span class="text-secondary small d-inline-block text-truncate" style="max-width: 14rem;"
                        title="{{ e($doctor->bio) }}">{{ \Illuminate\Support\Str::limit($doctor->bio, 90) }}</span>
                    @else
                      —
                    @endif
                  </td>
                  <td class="small text-secondary font-monospace text-break" style="max-width: 10rem;">
                    {{ $doctor->image_path ?? '—' }}
                  </td>
                  <td>
                    <span class="badge {{ $badge }}">{{ ucfirst($status) }}</span>
                  </td>
                  <td>
                    <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-ghost-primary btn-sm">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-secondary py-4">No doctors found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($doctors instanceof \Illuminate\Pagination\LengthAwarePaginator && $doctors->hasPages())
          <div class="card-footer d-flex align-items-center">
            {{ $doctors->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
