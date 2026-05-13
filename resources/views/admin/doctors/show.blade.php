@extends('admin.layouts.master')



@section('content')
  @if (session('status'))
    <div class="container-xl mb-3">
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif
  @if (session('temporary_password'))
    <div class="container-xl mb-3">
      <div class="alert alert-warning" role="alert">
        <strong>Temporary password</strong> (copy now; it is not stored in plain text):
        <code class="user-select-all ms-1">{{ session('temporary_password') }}</code>
      </div>
    </div>
  @endif
  @if (session('doctor_portal_credentials'))
    @php($creds = session('doctor_portal_credentials'))
    <div class="container-xl mb-3">
      <div class="alert alert-warning" role="alert">
        <strong>Portal login (copy now)</strong>
        <div class="small mt-2 mb-1">Email: <code class="user-select-all">{{ $creds['email'] ?? '—' }}</code></div>
        <div class="small mb-1">Password: <code class="user-select-all">{{ $creds['password'] ?? '—' }}</code></div>
        @if (! empty($creds['login_url']))
          <div class="small"><a href="{{ $creds['login_url'] }}" class="alert-link">Open login page</a></div>
        @endif
      </div>
    </div>
  @endif
  @if (! empty($decryptedPendingPassword))
    <div class="container-xl mb-3">
      <div class="alert alert-info" role="alert">
        <strong>Pending approval</strong> — provisional password (shown until this doctor is approved and the password is cleared):
        <code class="user-select-all ms-1">{{ $decryptedPendingPassword }}</code>
      </div>
    </div>
  @endif

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          @if (! empty($doctor['image_url']))
            <span class="avatar avatar-xl rounded" style="background-image: url({{ $doctor['image_url'] }})"></span>
          @else
            <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ $doctor->initial }}</span>
          @endif
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Doctor profile</div>
          <h2 class="page-title mb-1">{{ $doctor['name'] ?? 'Doctor' }}</h2>
          <ul class="list-inline list-inline-dots text-secondary mb-0">
            <li class="list-inline-item">{{ $doctor['specialty'] ?? '—' }}</li>
            <li class="list-inline-item"><span class="badge {{ $doctor->status_badge }}">{{ ucfirst($doctor->status_badge) }}</span></li>
          </ul>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.doctors') }}" class="btn">{{ __('Back') }}</a>
            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-primary">{{ __('Edit') }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <ul class="nav nav-tabs card-header-tabs nav-fill bg-transparent" data-bs-toggle="tabs">
                <li class="nav-item">
                  <a href="#tab-doc-overview" class="nav-link active" data-bs-toggle="tab">Overview</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-doc-services" class="nav-link" data-bs-toggle="tab">Services</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-doc-schedule" class="nav-link" data-bs-toggle="tab">Schedule</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-doc-appointments" class="nav-link" data-bs-toggle="tab">Recent appointments</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-doc-overview">
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Name</div>
                      <div class="datagrid-content fw-medium">{{ $doctor['name'] ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Specialty</div>
                      <div class="datagrid-content">{{ $doctor['specialty'] ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Status</div>
                      <div class="datagrid-content"><span class="badge {{ $doctor->status_badge }}">{{ ucfirst($doctor->status_badge) }}</span></div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">License no.</div>
                      <div class="datagrid-content font-monospace">{{ $doctor['license_no'] ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Experience</div>
                      <div class="datagrid-content">{{ $doctor['experience_years'] ?? '—' }} years</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Email</div>
                      <div class="datagrid-content">
                        @if (! empty($doctor['email']))
                          <a href="mailto:{{ $doctor['email'] }}" class="text-reset">{{ $doctor['email'] }}</a>
                        @else
                          —
                        @endif
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Phone</div>
                      <div class="datagrid-content">{{ $doctor['phone'] ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Bio</div>
                      <div class="datagrid-content text-secondary">{{ $doctor['bio'] ?? '—' }}</div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-doc-services">
                  <div class="card card-sm border-0 bg-light-lt">
                    <div class="card-body">
                      <h3 class="card-title">Assigned services</h3>
                      @if (! empty($doctor['assigned_services']))
                        <div class="d-flex flex-wrap gap-2">
                          @foreach ($doctor['assigned_services'] as $svc)
                            <span class="badge bg-blue-lt">{{ $svc }}</span>
                          @endforeach
                        </div>
                      @else
                        <div class="text-secondary">No services assigned.</div>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-doc-schedule">
                  <p class="text-secondary small mb-3">
                    Weekly hours from the doctor’s availability settings (Doctor portal → Availability).
                  </p>
                  <div class="table-responsive">
                    <table class="table table-vcenter table-sm table-striped mb-0">
                      <thead>
                        <tr>
                          <th>Day</th>
                          <th>Status</th>
                          <th>Time</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($doctor->weeklySchedules as $schedule)
                          <tr>
                            <td class="fw-medium">{{ $schedule->day_label }}</td>
                            <td>
                              @if ($schedule->is_active)
                                <span class="badge bg-green-lt">Active</span>
                              @else
                                <span class="badge bg-secondary-lt">Off</span>
                              @endif
                            </td>
                            <td class="text-secondary">
                              @if ($schedule->is_active)
                                {{ $schedule->time_slot_label }}
                              @else
                                —
                              @endif
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="3" class="text-secondary">
                              No weekly schedule saved yet. It appears after the doctor opens Availability (defaults are created on first visit).
                            </td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="tab-doc-appointments">
                  <p class="text-secondary small mb-3">This list can grow large; it scrolls.</p>
                  <div class="border rounded">
                    <div class="table-responsive" style="max-height: min(70vh, 28rem); overflow-y: auto;">
                      <table class="table table-vcenter table-sm table-striped mb-0">
                        <thead class="sticky-top bg-body border-bottom">
                          <tr>
                            <th>Code</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse (($doctor['recent_appointments_sample'] ?? []) as $a)
                            <tr>
                              <td class="font-monospace">{{ $a['code'] ?? '—' }}</td>
                              <td class="fw-medium">{{ $a['patient'] ?? '—' }}</td>
                              <td class="text-secondary text-nowrap">{{ $a['date'] ?? '—' }}</td>
                              <td class="text-secondary">{{ $a['time'] ?? '—' }}</td>
                              <td><span class="badge bg-azure-lt">{{ $a['status'] ?? '—' }}</span></td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="5" class="text-secondary">No appointments yet.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="row row-cards">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Contact</h3>
                  <div class="btn-list">
                    <a class="btn btn-outline-secondary" href="{{ ! empty($doctor['email']) ? 'mailto:' . $doctor['email'] : '#' }}">
                      Email
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ ! empty($doctor['phone']) ? 'tel:' . $doctor['phone'] : '#' }}">
                      Call
                    </a>
                  </div>
                  <div class="text-secondary small mt-2">
                    Links work once real email/phone values are stored.
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Clinical portal role</h3>
                  <p class="text-secondary small mb-3">
                    Optional: restrict what this person sees in the doctor portal. Leave as full access when they should see every section.
                    <a href="{{ route('admin.doctor-roles.index') }}">Clinical roles</a>
                  </p>
                  <form method="POST" action="{{ route('admin.doctors.role', $doctor->id) }}">
                    @csrf
                    <label class="form-label" for="doctor_role_id">Role</label>
                    <select id="doctor_role_id" name="doctor_role_id" class="form-select @error('doctor_role_id') is-invalid @enderror">
                      <option value="" @selected(old('doctor_role_id', $doctor->doctor_role_id) === null || old('doctor_role_id', $doctor->doctor_role_id) === '')>Full portal access</option>
                      @foreach ($doctorRoles as $r)
                        <option value="{{ $r->id }}" @selected((string) old('doctor_role_id', $doctor->doctor_role_id) === (string) $r->id)>{{ $r->name }}</option>
                      @endforeach
                    </select>
                    @error('doctor_role_id')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-primary w-100 mt-3">Save portal role</button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Quick stats</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Services</div>
                      <div class="datagrid-content">{{ is_array($doctor['assigned_services'] ?? null) ? count($doctor['assigned_services']) : 0 }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Appointments (sample)</div>
                      <div class="datagrid-content">{{ is_array($doctor['recent_appointments_sample'] ?? null) ? count($doctor['recent_appointments_sample']) : 0 }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection