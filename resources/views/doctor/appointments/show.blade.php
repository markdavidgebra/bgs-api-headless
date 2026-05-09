@extends('doctor.layouts.master')

@section('title', 'Appointment Details')

@section('content')
  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Appointment Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')
              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Appointment #{{ $appointment->appointment_no }}</h3>
                    <a href="{{ route('doctor.appointments') }}" class="btn btn-sm btn-outline">Back to list</a>
                  </div>

                  <div class="card mb-20">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><strong>Date:</strong> {{ $appointment->date_display }}</div>
                        <div class="col-md-6 mb-2"><strong>Time:</strong> {{ $appointment->time_display }}</div>
                        <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $appointment->patient_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Service:</strong> {{ $appointment->service_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ $appointment->status_label }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">Treatment Notes</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Patient concern</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->patient_concern ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Appointment remarks</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->appointment_remarks ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Admin notes</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->admin_notes ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Doctor notes</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->doctor_notes ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Instructions</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->instructions ?: '—' }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label mb-1">Alerts</label>
                          <div class="form-control bg-light" style="min-height: 44px;">
                            {{ optional($appointment->note)->alerts ?: '—' }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  @if ($appointment->prescribedProducts->isNotEmpty())
                    <div class="card mt-20">
                      <div class="card-header">
                        <h5 class="mb-0">Prescribed products</h5>
                      </div>
                      <div class="card-body p-0">
                        <div class="table-responsive">
                          <table class="table table-striped mb-0">
                            <thead>
                              <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($appointment->prescribedProducts as $p)
                                <tr>
                                  <td>{{ $p->name }}</td>
                                  <td class="text-center">{{ (int) ($p->pivot->quantity ?? 1) }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
