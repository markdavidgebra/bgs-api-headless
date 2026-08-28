@extends('admin.layouts.master')

@php
  $p = $appointment->patient;
@endphp

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">
            {{ strtoupper(substr($appointment->patient->name ?? '—', 0, 1)) }}
          </span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Appointment</div>
          <h2 class="page-title mb-1">{{ $appointment->appointment_no }}</h2>
          <ul class="list-inline list-inline-dots text-secondary mb-0">
            <li class="list-inline-item"><strong class="text-body">{{ $appointment->patient->name ?? '—' }}</strong></li>
            <li class="list-inline-item">{{ $appointment->service->name ?? '—' }}</li>
            <li class="list-inline-item">{{ $appointment->clinicalStaff->name ?? '—' }}</li>
          </ul>
        </div>
        <div class="col-auto ms-auto">
          <div class="btn-list">
            <a href="{{ route('admin.appointments') }}" class="btn">Back</a>
            <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Booked at</div>
              <div class="h3 mb-0">{{ $appointment->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '—' }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Date</div>
              <div class="h3 mb-0">{{ $appointment->appointment_date?->format('Y-m-d') ?? '—' }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Time</div>
              <div class="h3 mb-0">{{ $appointment->time_display }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Service</div>
              <div class="h3 mb-0 text-truncate" title="{{ $appointment->service->name ?? '—' }}">
                {{ $appointment->service->name ?? '—' }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg">
          <div class="card">
            <div class="card-body">
              <div class="subheader text-secondary">Clinical staff</div>
              <div class="h3 mb-0 text-truncate" title="{{ $appointment->clinicalStaff->name ?? '—' }}">
                {{ $appointment->clinicalStaff->name ?? '—' }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <ul class="nav nav-tabs card-header-tabs nav-fill bg-transparent" data-bs-toggle="tabs">
                <li class="nav-item">
                  <a href="#tab-appt-overview" class="nav-link active" data-bs-toggle="tab">Overview</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-appt-patient" class="nav-link" data-bs-toggle="tab">Patient</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-appt-notes" class="nav-link" data-bs-toggle="tab">Notes</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-appt-payment" class="nav-link" data-bs-toggle="tab">Payment</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-appt-timeline" class="nav-link" data-bs-toggle="tab">Timeline</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active show" id="tab-appt-overview">
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Appointment</div>
                      <div class="datagrid-content fw-medium">{{ $appointment->appointment_no }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Status</div>
                      <div class="datagrid-content">
                        <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span>
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Booked at</div>
                      <div class="datagrid-content">{{ $appointment->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Date</div>
                      <div class="datagrid-content">{{ $appointment->appointment_date?->format('Y-m-d') ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Time</div>
                      <div class="datagrid-content">{{ $appointment->time_display }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Clinical staff</div>
                      <div class="datagrid-content">{{ $appointment->clinicalStaff->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Service</div>
                      <div class="datagrid-content">{{ $appointment->service->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Location</div>
                      <div class="datagrid-content">—</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Created by</div>
                      <div class="datagrid-content">{{ $appointment->createdByAdmin?->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Updated by</div>
                      <div class="datagrid-content">{{ $appointment->updatedByAdmin?->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Updated</div>
                      <div class="datagrid-content">{{ $appointment->updated_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-appt-patient">
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Name</div>
                      <div class="datagrid-content fw-medium">{{ $p?->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Gender</div>
                      <div class="datagrid-content">{{ $p?->gender ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Birthdate</div>
                      <div class="datagrid-content">{{ $p?->birthdate?->format('M-d-Y') ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Age</div>
                      <div class="datagrid-content">{{ $p?->age ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Phone</div>
                      <div class="datagrid-content">{{ $p?->phone ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Email</div>
                      <div class="datagrid-content">
                        @if (filled($p?->email))
                          <a href="mailto:{{ $p->email }}" class="text-reset">{{ $p->email }}</a>
                        @else
                          —
                        @endif
                      </div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Address</div>
                      <div class="datagrid-content">{{ $p?->address ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Emergency contact</div>
                      <div class="datagrid-content">{{ $p?->emergency_contact ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Subscription</div>
                      <div class="datagrid-content">{{ $p?->subscription ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">History summary</div>
                      <div class="datagrid-content text-secondary">{{ $p?->history_summary ?? '—' }}</div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-appt-notes">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="card card-sm border-0 bg-light-lt">
                        <div class="card-body text-body">
                          <div class="datagrid mb-0">
                            @php
                              $apptNoteAuthors = is_array($appointmentNote?->section_authors) ? $appointmentNote->section_authors : [];
                            @endphp
                            <div class="datagrid-item">
                              <div class="datagrid-title">Patient concern</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->patient_concern ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'patient_concern', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                            <div class="datagrid-item">
                              <div class="datagrid-title">Post procedures</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->appointment_remarks ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'appointment_remarks', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                            <div class="datagrid-item">
                              <div class="datagrid-title">Medical history</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->admin_notes ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'admin_notes', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                            <div class="datagrid-item">
                              <div class="datagrid-title">Clinical notes</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->clinical_notes ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'clinical_notes', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                            <div class="datagrid-item">
                              <div class="datagrid-title">Take home medications</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->instructions ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'instructions', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                            <div class="datagrid-item">
                              <div class="datagrid-title">Allergy</div>
                              <div class="datagrid-content">
                                {{ $appointmentNote?->alerts ?: '—' }}
                                @if ($lbl = \App\Models\AppointmentNote::creatorLabelForSection($apptNoteAuthors, 'alerts', $appointment->patient, $appointment->clinicalStaff))
                                  <div class="text-secondary small mt-1">{{ __('By :name', ['name' => $lbl]) }}</div>
                                @endif
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-appt-payment">
                  @if ($appointmentPayment)
                    <div class="datagrid mb-0">
                      <div class="datagrid-item">
                        <div class="datagrid-title">Payment status</div>
                        <div class="datagrid-content">
                          @php
                            $payStatus = $appointmentPayment->payment_status ?: 'pending';
                            $payPaid = $appointmentPayment->is_paid || $payStatus === 'paid';
                          @endphp
                          <span class="badge {{ $payPaid ? 'bg-green-lt' : 'bg-yellow-lt' }}">
                            {{ ucfirst($payStatus) }}
                          </span>
                        </div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Amount</div>
                        <div class="datagrid-content fw-semibold">₱
                          {{ number_format((float) $appointmentPayment->amount, 2) }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Method</div>
                        <div class="datagrid-content">{{ $appointmentPayment->payment_method ?: '—' }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Invoice</div>
                        <div class="datagrid-content font-monospace">{{ $appointmentPayment->invoice_no ?: '—' }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Reference no.</div>
                        <div class="datagrid-content font-monospace">{{ $appointmentPayment->reference_no ?: '—' }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Paid</div>
                        <div class="datagrid-content">{{ $appointmentPayment->is_paid ? 'Yes' : 'No' }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Paid at</div>
                        <div class="datagrid-content">{{ $appointmentPayment->paid_at?->format('Y-m-d H:i') ?: '—' }}</div>
                      </div>
                      <div class="datagrid-item">
                        <div class="datagrid-title">Deposit / notes</div>
                        <div class="datagrid-content">{{ $appointmentPayment->deposit_notes ?: '—' }}</div>
                      </div>
                    </div>
                  @else
                    <p class="text-secondary mb-0">No payment record on file for this appointment.</p>
                  @endif
                </div>

                <div class="tab-pane" id="tab-appt-timeline">
                  <p class="text-secondary small mb-3">Activity log. This can grow large, so it scrolls.</p>
                  <div class="border rounded">
                    <div class="table-responsive" style="max-height: min(70vh, 28rem); overflow-y: auto;">
                      <table class="table table-vcenter table-sm table-striped mb-0">
                        <thead class="sticky-top bg-body border-bottom">
                          <tr>
                            <th>At</th>
                            <th>Event</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($appointmentTimelines as $t)
                            <tr>
                              <td class="text-secondary text-nowrap">{{ $t->event_at?->format('Y-m-d H:i') ?? '—' }}</td>
                              <td class="fw-medium">
                                {{ $t->event }}
                                @if (filled($t->description))
                                  <div class="text-secondary small fw-normal mt-1">{{ $t->description }}</div>
                                @endif
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="2" class="text-secondary">No timeline events yet.</td>
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
                  <h3 class="card-title">Snapshot</h3>
                  <div class="datagrid mb-0">
                    <div class="datagrid-item">
                      <div class="datagrid-title">Patient</div>
                      <div class="datagrid-content fw-medium">{{ $appointment->patient->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Status</div>
                      <div class="datagrid-content"><span
                          class="badge {{ $appointment->status_badge }}">{{ $appointment->status_label }}</span></div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Service</div>
                      <div class="datagrid-content">{{ $appointment->service->name ?? '—' }}</div>
                    </div>
                    <div class="datagrid-item">
                      <div class="datagrid-title">Clinical staff</div>
                      <div class="datagrid-content">{{ $appointment->clinicalStaff->name ?? '—' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Quick actions</h3>
                  <div class="btn-list">
                    <a href="#" class="btn btn-outline-secondary disabled">Reschedule</a>
                    <a href="#" class="btn btn-outline-danger disabled">Cancel appointment</a>
                  </div>
                  <div class="text-secondary small mt-2">
                    Buttons are disabled until the backend actions are implemented.
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