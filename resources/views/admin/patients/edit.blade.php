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
    </div>
  </div>
@endsection
