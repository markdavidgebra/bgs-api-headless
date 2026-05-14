@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title">Edit Patient</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn">Cancel</a>
            <button type="submit" form="patient-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
      @endif

      <form id="patient-edit-form" method="POST" action="{{ route('admin.patients.update', $patient->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $patient->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email) }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone</label>
                <input id="phone" type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone) }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="birthdate">Birthdate</label>
                <input id="birthdate" type="date" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate', optional($patient->birthdate)->toDateString()) }}">
                @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="gender">Gender</label>
                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                  <option value="">Select gender</option>
                  <option value="male" @selected(old('gender', $patient->gender) === 'male')>Male</option>
                  <option value="female" @selected(old('gender', $patient->gender) === 'female')>Female</option>
                  <option value="other" @selected(old('gender', $patient->gender) === 'other')>Other</option>
                </select>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" @disabled(! $canManageStatus)>
                  <option value="active" @selected(old('status', $patient->status) === 'active')>Active</option>
                  <option value="inactive" @selected(old('status', $patient->status) === 'inactive')>Inactive</option>
                </select>
                @if (! $canManageStatus)
                  <small class="form-hint">Your role cannot update patient status.</small>
                @endif
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="address">Address</label>
                <input id="address" type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $patient->address) }}">
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="emergency_contact">Emergency Contact</label>
                <input id="emergency_contact" type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" value="{{ old('emergency_contact', $patient->emergency_contact) }}">
                @error('emergency_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="history_summary">History Summary</label>
                <textarea id="history_summary" name="history_summary" rows="4" class="form-control @error('history_summary') is-invalid @enderror">{{ old('history_summary', $patient->history_summary) }}</textarea>
                @error('history_summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
      </form>

      <div class="card mt-3">
        <div class="card-header">
          <h3 class="card-title mb-0">Treatment packages</h3>
          <p class="text-secondary small mb-0 mt-2">Assign a treatment package to this patient. Session counts follow the package’s service configuration.</p>
        </div>
        <div class="card-body">
          <h4 class="mb-3">Assigned packages</h4>
          @if ($patientPackages->isEmpty())
            <p class="text-secondary mb-4">No treatment packages on file yet.</p>
          @else
            <div class="table-responsive mb-4">
              <table class="table table-vcenter card-table table-striped table-sm">
                <thead>
                  <tr>
                    <th>Package</th>
                    <th class="text-end">Sessions</th>
                    <th>Start</th>
                    <th>Expires</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($patientPackages as $pp)
                    <tr>
                      <td>{{ $pp->treatmentPackage->name ?? '—' }}</td>
                      <td class="text-end">{{ (int) $pp->used_sessions }} / {{ (int) $pp->total_sessions }}</td>
                      <td>{{ $pp->start_date?->format('Y-m-d') ?? '—' }}</td>
                      <td>{{ $pp->end_date?->format('Y-m-d') ?? '—' }}</td>
                      <td>{{ $pp->status ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          <h4 class="mb-3">Add package</h4>
          @if ($treatmentPackagesForAssign->isEmpty())
            <p class="text-secondary mb-0">There are no active treatment packages to assign. Create one under Treatment Packages first.</p>
          @else
            <form method="POST" action="{{ route('admin.patients.treatment-packages.store', $patient->id) }}" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label required" for="treatment_package_id">Package</label>
                <select id="treatment_package_id" name="treatment_package_id" class="form-select @error('treatment_package_id') is-invalid @enderror" required>
                  <option value="">Select a package</option>
                  @foreach ($treatmentPackagesForAssign as $tp)
                    <option value="{{ $tp->id }}" @selected(old('treatment_package_id') == $tp->id)>
                      {{ $tp->name }}@if ($tp->price !== null) — ₱{{ number_format((float) $tp->price, 2) }}@endif
                    </option>
                  @endforeach
                </select>
                @error('treatment_package_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <label class="form-label" for="pkg_purchased_at">Purchase date</label>
                <input id="pkg_purchased_at" type="date" name="purchased_at" class="form-control @error('purchased_at') is-invalid @enderror" value="{{ old('purchased_at', now()->toDateString()) }}">
                @error('purchased_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <label class="form-label" for="pkg_start_date">Start date</label>
                <input id="pkg_start_date" type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', now()->toDateString()) }}">
                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="package_admin_notes">Notes (optional)</label>
                <textarea id="package_admin_notes" name="package_admin_notes" rows="2" class="form-control @error('package_admin_notes') is-invalid @enderror" placeholder="e.g. complimentary add-on, promo code, staff initials">{{ old('package_admin_notes') }}</textarea>
                @error('package_admin_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Add package to patient</button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
