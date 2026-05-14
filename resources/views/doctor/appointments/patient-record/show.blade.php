@extends('doctor.layouts.master')

@section('title', 'Patient Details')

@section('content')
  <style>
    .tab-btn {
      border: 1px solid #cbd5e1;
      background: #fff;
      color: #1f2937;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
    }

    .tab-btn.active {
      background: #1d4ed8;
      border-color: #1d4ed8;
      color: #fff;
    }

    .tab-panel {
      display: none;
    }

    .tab-panel.active {
      display: block;
    }

    .vital-open-btn {
      white-space: normal;
      word-break: break-word;
      line-height: 1.35;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Patient Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ $patient->name }} - Patient Details</h3>
                    <a href="{{ route('doctor.patient-records') }}" class="btn btn-sm btn-outline">Back to records</a>
                  </div>

                  @if (session('success'))
                    <div class="alert alert-success mb-20">{{ session('success') }}</div>
                  @endif

                  @if ($errors->any())
                    <div class="alert alert-danger mb-20">
                      <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="d-flex flex-wrap gap-2 mb-20" id="tabButtons">
                    <button type="button" class="tab-btn active" data-target="tab-overview">Overview</button>
                    <button type="button" class="tab-btn" data-target="tab-notes">Treatment Notes</button>
                    <button type="button" class="tab-btn" data-target="tab-appointments">Appointments</button>
                    <button type="button" class="tab-btn" data-target="tab-packages">Packages / Memberships</button>
                    <button type="button" class="tab-btn" data-target="tab-payments">Payments</button>
                  </div>

                  <div class="card tab-panel active" id="tab-overview">
                    <div class="card-header"><h5 class="mb-0">Overview</h5></div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><strong>Patient:</strong> {{ $patient->name }}</div>
                        <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $patient->email ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Contact:</strong> {{ $patient->phone ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Birthdate:</strong> {{ $patient->birthdate?->format('Y-m-d') ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $patient->gender ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Address:</strong> {{ $patient->address ?? '—' }}</div>
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ ucfirst((string) ($patient->status ?? 'active')) }}</div>
                        <div class="col-md-6 mb-2"><strong>Last visit (with you):</strong>
                          @if ($lastVisit)
                            {{ $lastVisit->date_display }} {{ $lastVisit->time_display }}
                          @else
                            —
                          @endif
                        </div>
                        <div class="col-md-6 mb-2"><strong>Total visits (with you):</strong> {{ $totalVisits }}</div>
                        <div class="col-12 mt-2"><strong>Notes summary (clinic):</strong>
                          @php
                            $summary = $latestNote ? $latestNote->treatmentSummarySnippet(180) : '';
                          @endphp
                          {{ $summary !== '' ? $summary : 'No notes yet.' }}</div>
                        <div class="col-12 mt-2"><strong>Alerts:</strong> {{ $latestAlerts ?: 'No alerts' }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-notes">
                    <div class="card-header"><h5 class="mb-0">Treatment Notes</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Past Notes</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Doctor</th>
                              <th>Vitals</th>
                              <th>Observation</th>
                              <th>Procedure Done</th>
                              <th>Recommendation</th>
                              <th>Follow-up</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($notesHistory as $row)
                              <tr>
                                <td>{{ $row->appointment->date_display }} {{ $row->appointment->time_display }}</td>
                                <td>{{ $row->appointment->doctor_name }}</td>
                                <td class="small">
                                  @php
                                    $vitalLine = $row->note->vitalSignsSummary();
                                  @endphp
                                  @if ($vitalLine !== '')
                                    <button type="button"
                                      class="btn btn-sm btn-outline-primary vital-open-btn"
                                      aria-label="View vital signs: {{ $vitalLine }}"
                                      data-vital-bp="{{ $row->note->vital_blood_pressure }}"
                                      data-vital-hr="{{ $row->note->vital_heart_rate }}"
                                      data-vital-temp="{{ $row->note->vital_temperature }}"
                                      data-vital-rr="{{ $row->note->vital_respiratory_rate }}"
                                      data-vital-spo2="{{ $row->note->vital_oxygen_saturation }}"
                                      data-vital-wt="{{ $row->note->vital_weight }}"
                                      data-vital-ht="{{ $row->note->vital_height }}"
                                      data-vital-when="{{ $row->appointment->date_display }} {{ $row->appointment->time_display }} · {{ $row->appointment->doctor_name }}">
                                      View
                                    </button>
                                  @else
                                    —
                                  @endif
                                </td>
                                <td>{{ $row->note->doctor_notes ?: $row->note->patient_concern ?: '—' }}</td>
                                <td>{{ $row->note->appointment_remarks ?: '—' }}</td>
                                <td>{{ $row->note->instructions ?: '—' }}</td>
                                <td>{{ $row->note->alerts ?: '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="7" class="text-center text-secondary py-4">No notes yet.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Add New Note</h6>
                      @if ($myAppointments->isEmpty())
                        <p class="text-secondary mb-0">You do not have any appointments with this patient yet. Notes can be added once an appointment exists between you and this patient.</p>
                      @else
                        <form method="POST" action="{{ route('doctor.patient-records.notes.store', $patient) }}" class="row g-3">
                          @csrf
                          <div class="col-md-6">
                            <label class="form-label">Appointment</label>
                            <select name="appointment_id" class="form-control">
                              @foreach ($myAppointments as $appointment)
                                <option value="{{ $appointment->id }}">
                                  {{ $appointment->appointment_no }} - {{ $appointment->date_display }} {{ $appointment->time_display }}
                                </option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Observation</label>
                            <textarea name="observation" class="form-control" rows="2">{{ old('observation') }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Procedure Done</label>
                            <textarea name="procedure_done" class="form-control" rows="2">{{ old('procedure_done') }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Recommendation</label>
                            <textarea name="recommendation" class="form-control" rows="2">{{ old('recommendation') }}</textarea>
                          </div>
                          <div class="col-md-12">
                            <label class="form-label">Follow-up</label>
                            <textarea name="follow_up" class="form-control" rows="2">{{ old('follow_up') }}</textarea>
                          </div>
                          <div class="col-md-12">
                            <button type="submit" class="btn btn-sm">Save Note</button>
                          </div>
                        </form>
                      @endif
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-appointments">
                    <div class="card-header"><h5 class="mb-0">Appointments</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Upcoming Appointments</h6>
                      <ul class="mb-20 ps-3">
                        @forelse ($upcomingAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} — {{ $appointment->doctor_name }} — {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
                        @empty
                          <li>No upcoming appointments.</li>
                        @endforelse
                      </ul>

                      <h6 class="mb-10">Past Appointments</h6>
                      <ul class="mb-0 ps-3">
                        @forelse ($pastAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} — {{ $appointment->doctor_name }} — {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
                        @empty
                          <li>No past appointments.</li>
                        @endforelse
                      </ul>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-packages">
                    <div class="card-header"><h5 class="mb-0">Packages / Memberships</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Packages</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Plan Name</th><th>Sessions Used</th><th>Sessions Remaining</th><th>Expiry Date</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($packages as $package)
                              <tr>
                                <td>{{ $package->treatmentPackage->name ?? '—' }}</td>
                                <td>{{ (int) $package->used_sessions }}</td>
                                <td>{{ (int) $package->remaining_sessions }}</td>
                                <td>{{ $package->end_date?->format('Y-m-d') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No package records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Memberships</h6>
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Plan Name</th><th>Sessions Used</th><th>Sessions Remaining</th><th>Expiry Date</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($subscriptions as $subscription)
                              <tr>
                                <td>{{ $subscription->membershipPlan->name ?? '—' }}</td>
                                <td>{{ (int) $subscription->sessions_used }}</td>
                                <td>{{ (int) $subscription->sessions_remaining }}</td>
                                <td>{{ $subscription->end_date?->format('Y-m-d') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No membership records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-payments">
                    <div class="card-header"><h5 class="mb-0">Payments</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Payment History</h6>
                      <div class="table-responsive mb-20">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Date</th><th>Amount</th><th>Status</th><th>Reference</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($payments as $payment)
                              <tr>
                                <td>{{ $payment->payment_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $payment->formatted_amount }}</td>
                                <td>{{ ucfirst((string) $payment->payment_status) }}</td>
                                <td>{{ $payment->reference_type_label }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No payment records.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Appointment Payments</h6>
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr><th>Invoice</th><th>Amount</th><th>Status</th><th>Paid At</th></tr>
                          </thead>
                          <tbody>
                            @forelse ($appointmentPayments as $payment)
                              <tr>
                                <td>{{ $payment->invoice_no ?? '—' }}</td>
                                <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucfirst((string) ($payment->payment_status ?? 'pending')) }}</td>
                                <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-secondary py-4">No appointment payment records.</td></tr>
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
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="vitalSignsModal" tabindex="-1" aria-labelledby="vitalSignsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0" id="vitalSignsModalLabel">Vital signs</h5>
            <div class="small text-muted mt-5" id="vitalSignsModalWhen"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <table class="table table-striped mb-0" id="vitalSignsModalTable">
            <tbody></tbody>
          </table>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const buttons = document.querySelectorAll('#tabButtons .tab-btn');
      const panels = document.querySelectorAll('.tab-panel');
      buttons.forEach((button) => {
        button.addEventListener('click', () => {
          buttons.forEach((b) => b.classList.remove('active'));
          panels.forEach((p) => p.classList.remove('active'));
          button.classList.add('active');
          const panel = document.getElementById(button.dataset.target);
          if (panel) panel.classList.add('active');
        });
      });

      const vitalModalEl = document.getElementById('vitalSignsModal');
      const vitalModalWhen = document.getElementById('vitalSignsModalWhen');
      const vitalModalTbody = document.querySelector('#vitalSignsModalTable tbody');
      if (vitalModalEl && vitalModalWhen && vitalModalTbody && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        let vitalModalInstance = null;
        function getVitalModalInstance() {
          if (!vitalModalInstance) {
            vitalModalInstance = bootstrap.Modal.getInstance(vitalModalEl);
            if (!vitalModalInstance) {
              vitalModalInstance = new bootstrap.Modal(vitalModalEl);
            }
          }
          return vitalModalInstance;
        }
        const rows = [
          ['Blood pressure', 'vitalBp'],
          ['Heart rate (pulse)', 'vitalHr'],
          ['Temperature', 'vitalTemp'],
          ['Respiratory rate', 'vitalRr'],
          ['Oxygen (SpO2)', 'vitalSpo2'],
          ['Weight', 'vitalWt'],
          ['Height', 'vitalHt'],
        ];
        document.querySelectorAll('.vital-open-btn').forEach((btn) => {
          btn.addEventListener('click', function () {
            const d = this.dataset;
            vitalModalWhen.textContent = d.vitalWhen || '';
            vitalModalTbody.innerHTML = '';
            rows.forEach(([label, key]) => {
              const raw = d[key];
              const val = raw && String(raw).trim() !== '' ? String(raw).trim() : '—';
              const tr = document.createElement('tr');
              const th = document.createElement('th');
              th.className = 'ps-4 py-2';
              th.scope = 'row';
              th.textContent = label;
              const td = document.createElement('td');
              td.className = 'py-2 pe-4';
              td.textContent = val;
              tr.appendChild(th);
              tr.appendChild(td);
              vitalModalTbody.appendChild(tr);
            });
            getVitalModalInstance().show();
          });
        });
      }
    });
  </script>
@endsection
