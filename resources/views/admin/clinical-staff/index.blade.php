@extends('admin.layouts.master')

@section('content')
  @php
    $canManageClinicalStaff = \App\Support\AdminPermissions::canAccess(auth('admin')->user(), 'clinical_staff.manage');
  @endphp
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Clinical staff</h2>
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
            @if ($canManageClinicalStaff)
              <a href="{{ route('admin.clinical-staff.create') }}" class="btn btn-primary">New doctor</a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if (session('clinical_staff_portal_credentials'))
        @php
          $cred = session('clinical_staff_portal_credentials');
        @endphp
        <div class="alert alert-info alert-dismissible" role="alert">
          <strong>{{ __('Share with the doctor (email did not deliver)') }}</strong>
          <p class="mb-2 small text-secondary">{{ __('Copy these once; refresh clears this message.') }}</p>
          <div class="mb-1"><strong>{{ __('Login URL') }}:</strong> <a href="{{ $cred['login_url'] ?? url('/login') }}">{{ $cred['login_url'] ?? url('/login') }}</a></div>
          <div class="mb-1"><strong>{{ __('Email') }}:</strong> <code class="user-select-all">{{ $cred['email'] ?? '' }}</code></div>
          <div class="mb-0"><strong>{{ __('Temporary password') }}:</strong> <code class="user-select-all">{{ $cred['password'] ?? '' }}</code></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
      @endif
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
                <input id="search" type="text" class="form-control" name="search" placeholder="Name, email, phone…"
                  value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-lg-4">
              <label class="form-label" for="status">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="">All</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
              </select>
            </div>
            <div class="col-lg-2 d-grid">
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
                <th>Status</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($clinicalStaff as $doctor)
                @php
                  $status = strtolower((string) ($doctor->status ?? 'pending'));
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
                        <a href="{{ route('admin.clinical-staff.show', $doctor->id) }}" class="text-reset">{{ $doctor->name }}</a>
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
                  <td>
                    @if ($canManageClinicalStaff)
                      <form method="POST" action="{{ route('admin.clinical-staff.status', $doctor->id) }}">
                        @csrf
                        <select
                          name="status"
                          class="form-select form-select-sm"
                          onchange="this.form.submit()"
                          aria-label="Update doctor status"
                        >
                          <option value="pending" @selected($status === 'pending')>Pending</option>
                          <option value="active" @selected($status === 'active')>Active</option>
                          <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        </select>
                      </form>
                    @else
                      <span class="text-reset">{{ ucfirst($status) }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-list flex-nowrap">
                      <a href="{{ route('admin.clinical-staff.show', $doctor->id) }}" class="btn btn-ghost-primary btn-sm">{{ __('View') }}</a>
                      @if ($canManageClinicalStaff)
                        <a href="{{ route('admin.clinical-staff.edit', $doctor->id) }}" class="btn btn-ghost-secondary btn-sm">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('admin.clinical-staff.destroy', $doctor->id) }}" class="d-inline"
                          onsubmit="return confirm(@json(__('Delete this doctor? This cannot be undone.')));">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-ghost-danger btn-sm">Delete</button>
                        </form>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-secondary py-4">No doctors found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($clinicalStaff instanceof \Illuminate\Pagination\LengthAwarePaginator && $clinicalStaff->hasPages())
          <div class="card-footer d-flex align-items-center">
            {{ $clinicalStaff->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
