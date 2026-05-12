@extends('admin.layouts.master')

@php
  $draftName = old('name');
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure shadow-sm d-flex align-items-center justify-content-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="32" height="32" viewBox="0 0 24 24"
              stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
              aria-hidden="true">
              <circle cx="12" cy="7" r="4" />
              <path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2" />
            </svg>
          </span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Clinic</div>
          <h2 class="page-title mb-0">{{ $draftName ?: 'New doctor' }}</h2>
          <div class="text-secondary small mt-1">A secure password will be created when you save. Share it with the doctor securely.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.doctors') }}" class="btn">Cancel</a>
            <button type="submit" form="doctor-create-form" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M19 21h-14a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h11l4 4v12a2 2 0 0 1 -2 2z" />
                <path d="M17 21v-8h-10v8" />
                <path d="M7 3v4h8" />
              </svg>
              Save doctor
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row g-3">
        <div class="col-lg-8">
          <form id="doctor-create-form" method="POST" action="{{ route('admin.doctors.store') }}">
            @csrf
            <div class="card">
              <div class="card-header">
                <h3 class="card-title mb-0">Doctor details</h3>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required" for="name">Name</label>
                    <input id="name" name="name" type="text"
                      class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name') }}" placeholder="e.g. Dr. Jane Doe" required autocomplete="name">
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label required" for="email">Email</label>
                    <input id="email" name="email" type="email"
                      class="form-control @error('email') is-invalid @enderror"
                      value="{{ old('email') }}" placeholder="name@clinic.com" required autocomplete="email">
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="phone">Phone</label>
                    <input id="phone" name="phone" type="text"
                      class="form-control @error('phone') is-invalid @enderror"
                      value="{{ old('phone') }}" placeholder="09XXXXXXXXX" autocomplete="tel">
                    @error('phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Checklist</h3>
              <ul class="text-secondary mb-0 ps-3">
                <li>Name and email are required.</li>
                <li>Password is generated automatically on save.</li>
                <li>The doctor will receive an email with username and password only after approval.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
