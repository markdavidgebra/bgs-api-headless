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
              <div class="col-md-9">
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
                    <button type="button" class="tab-btn" data-target="tab-history">Medical / Treatment History</button>
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
                        <div class="col-md-6 mb-2"><strong>Status:</strong> {{ ucfirst((string) ($patient->status ?? 'active')) }}</div>
                        <div class="col-md-6 mb-2"><strong>Last Visit:</strong> {{ $lastVisit?->date_display }} {{ $lastVisit?->time_display }}</div>
                        <div class="col-md-6 mb-2"><strong>Total Visits:</strong> {{ $totalVisits }}</div>
                        <div class="col-12 mt-2"><strong>Notes Summary:</strong> {{ \Illuminate\Support\Str::limit((string) ($latestNote?->doctor_notes ?? 'No notes yet.'), 180) }}</div>
                        <div class="col-12 mt-2"><strong>Alerts:</strong> {{ $latestAlerts ?: 'No alerts' }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-history">
                    <div class="card-header"><h5 class="mb-0">Medical / Treatment History</h5></div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Service</th>
                              <th>Doctor</th>
                              <th>Notes</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($appointments as $appointment)
                              <tr>
                                <td>{{ $appointment->date_display }} {{ $appointment->time_display }}</td>
                                <td>{{ $appointment->service_name }}</td>
                                <td>{{ $appointment->doctor_name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit((string) optional($appointment->note)->doctor_notes, 80) ?: '—' }}</td>
                                <td>{{ $appointment->status_label }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="5" class="text-center text-secondary py-4">No history found.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
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
                                <td>{{ $row->note->doctor_notes ?: '—' }}</td>
                                <td>{{ $row->note->appointment_remarks ?: '—' }}</td>
                                <td>{{ $row->note->instructions ?: '—' }}</td>
                                <td>{{ $row->note->alerts ?: '—' }}</td>
                              </tr>
                            @empty
                              <tr><td colspan="5" class="text-center text-secondary py-4">No notes yet.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <h6 class="mb-10">Add New Note</h6>
                      <form method="POST" action="{{ route('doctor.patient-records.notes.store', $patient) }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                          <label class="form-label">Appointment</label>
                          <select name="appointment_id" class="form-control">
                            @foreach ($appointments as $appointment)
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
                    </div>
                  </div>

                  <div class="card tab-panel" id="tab-appointments">
                    <div class="card-header"><h5 class="mb-0">Appointments</h5></div>
                    <div class="card-body">
                      <h6 class="mb-10">Upcoming Appointments</h6>
                      <ul class="mb-20 ps-3">
                        @forelse ($upcomingAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} - {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
                        @empty
                          <li>No upcoming appointments.</li>
                        @endforelse
                      </ul>

                      <h6 class="mb-10">Past Appointments</h6>
                      <ul class="mb-0 ps-3">
                        @forelse ($pastAppointments as $appointment)
                          <li class="mb-2">{{ $appointment->date_display }} {{ $appointment->time_display }} - {{ $appointment->service_name }} ({{ $appointment->status_label }})</li>
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
    });
  </script>
@endsection
