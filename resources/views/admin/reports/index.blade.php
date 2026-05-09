@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Overview dashboard</h2>
          <div class="text-secondary small mt-1">Live metrics from payments, appointments, patients, and subscriptions.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list flex-wrap">
            <a href="{{ route('admin.reports.revenue') }}" class="btn btn-sm">Revenue</a>
            <a href="{{ route('admin.reports.appointments') }}" class="btn btn-sm">Appointments</a>
            <a href="{{ route('admin.reports.services') }}" class="btn btn-sm">Services</a>
            <a href="{{ route('admin.reports.patients') }}" class="btn btn-sm">Patients</a>
            <a href="{{ route('admin.reports.subscriptions') }}" class="btn btn-sm">Subscriptions</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total revenue</div>
              <div class="h3 mb-0">₱{{ number_format($stats['total_revenue'], 2) }}</div>
              <div class="text-secondary small mt-1">Paid + partial (all time)</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total appointments</div>
              <div class="h3 mb-0">{{ number_format($stats['total_appointments']) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Total patients</div>
              <div class="h3 mb-0">{{ number_format($stats['total_patients']) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active subscriptions</div>
              <div class="h3 mb-0">{{ number_format($stats['active_subscriptions']) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Treatment packages assigned</div>
              <div class="h3 mb-0">{{ number_format($stats['total_packages_sold']) }}</div>
              <div class="text-secondary small mt-1">Rows in patient ↔ package</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Monthly revenue (last 6 months)</h3>
            </div>
            <div class="card-body">
              @foreach ($monthlyRevenue as $row)
                @php
                  $pct = $maxRevenue > 0 ? round(($row['value'] / $maxRevenue) * 100) : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $row['month'] }}</span>
                    <span>₱{{ number_format($row['value'], 2) }}</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%" role="progressbar"
                      aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-lg-3">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Appointment status</h3>
            </div>
            <div class="card-body">
              @foreach ($appointmentStatus as $status)
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="d-flex align-items-center gap-2">
                    <span class="badge {{ $status['color'] }} text-white">&nbsp;</span>
                    <span>{{ $status['label'] }}</span>
                  </span>
                  <span class="text-secondary text-nowrap small">{{ $status['value'] }}%
                    @if (($status['count'] ?? 0) > 0)
                      <span class="text-secondary">({{ $status['count'] }})</span>
                    @endif
                  </span>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-lg-3">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Top services</h3>
            </div>
            <div class="card-body">
              @forelse ($topServices as $svc)
                <div class="d-flex justify-content-between mb-2">
                  <span>{{ $svc->name }}</span>
                  <span class="text-secondary">{{ number_format((int) $svc->appointment_count) }}</span>
                </div>
              @empty
                <div class="text-secondary small">No appointments linked to services yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards">
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Recent payments</h3>
              <div class="ms-auto d-print-none">
                <a href="{{ route('admin.payments') }}" class="btn btn-sm">All payments</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentPayments as $payment)
                    <tr>
                      <td class="font-monospace">
                        <a href="{{ route('admin.payments.show', $payment->id) }}">{{ $payment->payment_id }}</a>
                      </td>
                      <td>
                        <div>{{ $payment->patient->name ?? '—' }}</div>
                        <span class="badge {{ $payment->status_badge }}">{{ ucfirst((string) $payment->payment_status) }}</span>
                      </td>
                      <td class="text-end">₱{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-secondary">No payments recorded.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Recent appointments</h3>
              <div class="ms-auto d-print-none">
                <a href="{{ route('admin.appointments') }}" class="btn btn-sm">All appointments</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Patient</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentAppointments as $appt)
                    <tr>
                      <td class="font-monospace">
                        <a href="{{ route('admin.appointments.show', $appt->id) }}">{{ $appt->appointment_no }}</a>
                      </td>
                      <td>
                        <div>{{ $appt->patient->name ?? '—' }}</div>
                        <div class="text-secondary small">{{ $appt->service->name ?? '—' }}</div>
                      </td>
                      <td>
                        <span class="badge {{ $appt->status_badge }}">{{ $appt->status_label }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-secondary">No appointments yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title mb-0">Subscriptions expiring (30 days)</h3>
              <div class="ms-auto d-print-none">
                <a href="{{ route('admin.subscriptions') }}" class="btn btn-sm">Plans</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Plan</th>
                    <th class="text-end">Days</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($expiringSubscriptions as $row)
                    <tr>
                      <td>{{ $row['patient'] }}</td>
                      <td>{{ $row['plan'] }}</td>
                      <td class="text-end">{{ $row['days_left'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-secondary">No active subscriptions expiring in the next 30 days.</td>
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
@endsection
