@extends('clinical-staff.layouts.master')

@section('title', 'Treatment Note')

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

    .note-block {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 14px 16px;
      background: #f9fafb;
      min-height: 48px;
      white-space: pre-wrap;
    }

    .note-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #6b7280;
      margin-bottom: 6px;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Clinical staff <span></span> Treatment Note
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
                  <div class="section-title mb-20 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                      <h3 class="mb-5">Treatment Note</h3>
                      <p class="mb-0 text-muted">
                        {{ $appointment->appointment_no }} · {{ $appointment->date_display }} {{ $appointment->time_display }}
                      </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <a href="{{ route('doctor.treatment-notes') }}" class="btn btn-sm tn-btn tn-btn-light">Back to list</a>
                      <a href="{{ route('doctor.appointments.notes.create', $appointment) }}" class="btn btn-sm tn-btn tn-btn-primary">Edit notes</a>
                      <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm tn-btn tn-btn-light">Appointment</a>
                    </div>
                  </div>

                  <div class="card mb-25">
                    <div class="card-header">
                      <h5 class="mb-0">Appointment context</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $appointment->patient_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Email:</strong> {{ optional($appointment->patient)->email ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Phone:</strong> {{ optional($appointment->patient)->phone ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Service:</strong> {{ $appointment->service_name }}</div>
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ $appointment->status_label }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">Clinical note</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Patient concern</div>
                          <div class="note-block">{{ $note->patient_concern ?: '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Post procedures</div>
                          <div class="note-block">{{ $note->appointment_remarks ?: '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Medical history</div>
                          <div class="note-block">{{ $note->admin_notes ?: '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Clinical notes (observation)</div>
                          <div class="note-block">{{ $note->doctor_notes ?: '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Take home medications</div>
                          <div class="note-block">{{ $note->instructions ?: '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-4">
                          <div class="note-label">Allergy</div>
                          <div class="note-block">{{ $note->alerts ?: '—' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mt-25">
                    <div class="card-header">
                      <h5 class="mb-0">Prescription given</h5>
                    </div>
                    <div class="card-body p-0">
                      @if ($appointment->prescribedProducts->isEmpty())
                        <div class="p-4 text-muted">No prescribed products recorded for this treatment note.</div>
                      @else
                        @php $prescriptionGrandTotal = 0; @endphp
                        <div class="table-responsive">
                          <table class="table table-striped mb-0">
                            <thead>
                              <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit price</th>
                                <th class="text-end">Total price</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($appointment->prescribedProducts as $product)
                                @php
                                  $qty = (int) ($product->pivot->quantity ?? 1);
                                  $unitPrice = (float) ($product->discount_price ?? $product->selling_price ?? 0);
                                  $lineTotal = $unitPrice * $qty;
                                  $prescriptionGrandTotal += $lineTotal;
                                @endphp
                                <tr>
                                  <td>{{ $product->name }}</td>
                                  <td>{{ $product->sku ?: '—' }}</td>
                                  <td class="text-center">{{ $qty }}</td>
                                  <td class="text-end">₱{{ number_format($unitPrice, 2) }}</td>
                                  <td class="text-end fw-semibold">₱{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                            <tfoot>
                              <tr>
                                <th colspan="4" class="text-end">Grand total</th>
                                <th class="text-end">₱{{ number_format($prescriptionGrandTotal, 2) }}</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      @endif
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
