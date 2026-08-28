@extends('clinical-staff.layouts.master')

@section('title', 'Treatment Notes')

@section('content')
  <style>
    .tn-btn {
      border-radius: 8px;
      font-weight: 700;
      font-size: 12px;
      line-height: 1.2;
      padding: 8px 12px;
    }

    .tn-btn-primary {
      border: 1px solid #1d4ed8;
      background: #1d4ed8;
      color: #fff !important;
    }

    .tn-btn-primary:hover,
    .tn-btn-primary:focus {
      background: #1e40af;
      border-color: #1e40af;
      color: #fff !important;
    }

    .tn-btn-light {
      border: 1px solid #94a3b8;
      background: #fff;
      color: #0f172a !important;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Treatment Notes
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('clinical-staff.layouts.sidebar')

              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3>Treatment Notes</h3>
                    <p class="mb-0">All notes recorded from your handled appointments.</p>
                  </div>

                  <div class="card mb-25">
                    <div class="card-body">
                      <form method="GET" action="{{ route('clinical_staff.treatment-notes') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                          <label for="search" class="form-label">Search</label>
                          <input type="text" id="search" name="search" class="form-control"
                            value="{{ $search }}" placeholder="Appointment #, patient, or service">
                        </div>
                        <div class="col-md-3">
                          <label for="date" class="form-label">Date</label>
                          <input type="date" id="date" name="date" class="form-control" value="{{ $date }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                          <button type="submit" class="btn btn-sm tn-btn tn-btn-primary">Apply</button>
                          <a href="{{ route('clinical_staff.treatment-notes') }}" class="btn btn-sm tn-btn tn-btn-light">Reset</a>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Notes List</h5>
                      <span class="small text-muted">{{ $noteRows->total() }} record(s)</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Appointment</th>
                              <th>Date</th>
                              <th>Patient</th>
                              <th>Service</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($noteRows as $appointment)
                              <tr>
                                <td>{{ $appointment->appointment_no }}</td>
                                <td>{{ $appointment->date_display }} {{ $appointment->time_display }}</td>
                                <td>{{ $appointment->patient_name }}</td>
                                <td>{{ $appointment->service_name }}</td>
                                <td>
                                  <a href="{{ route('clinical_staff.treatment-notes.show', $appointment) }}" class="btn btn-xs tn-btn tn-btn-light">View</a>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No treatment notes found.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="card-footer">
                      {{ $noteRows->links() }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
