@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Subscriptions report</h2>
          <div class="text-secondary small mt-1">
            KPI counts and the table respect filters below. <strong>Membership revenue</strong> and the <strong>monthly chart</strong> are all-time
            (payments with reference <strong>membership</strong>, paid + partial).
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.reports') }}" class="btn">Overview</a>
          <a href="{{ route('admin.subscriptions') }}" class="btn btn-primary">Plans</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Active</div>
              <div class="h3 mb-0">{{ number_format($active) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Expired</div>
              <div class="h3 mb-0">{{ number_format($expired) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Cancelled</div>
              <div class="h3 mb-0">{{ number_format($cancelled) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Paused</div>
              <div class="h3 mb-0">{{ number_format($paused) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">New (this month)</div>
              <div class="h3 mb-0">{{ number_format($newThisMonth) }}</div>
              <div class="text-secondary small mt-1">By start date</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Membership revenue</div>
              <div class="h3 mb-0">₱{{ number_format($membershipRevenueTotal, 2) }}</div>
              <div class="text-secondary small mt-1">All-time payments</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.subscriptions') }}">
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="from">Start from</label>
              <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="to">Start to</label>
              <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label" for="plan_id">Plan</label>
              <select id="plan_id" name="plan_id" class="form-select">
                <option value="">All</option>
                @foreach ($plans as $plan)
                  <option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" name="status" class="form-select">
                <option value="">All</option>
                @foreach ($statusOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-auto d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
            @if (request()->hasAny(['from', 'to', 'plan_id', 'status']))
              <div class="col-lg-auto d-flex align-items-end">
                <a href="{{ route('admin.reports.subscriptions') }}" class="btn btn-link">Clear filters</a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Active vs not active</h3>
            </div>
            <div class="card-body">
              <div class="text-secondary small mb-3">Not active = expired + cancelled + paused (filtered rows).</div>
              <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                  <span>Active</span><span>{{ $activePct }}%</span>
                </div>
                <div class="progress progress-sm">
                  <div class="progress-bar bg-green" style="width: {{ $activePct }}%" role="progressbar"
                    aria-valuenow="{{ $activePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
              <div class="mb-0">
                <div class="d-flex justify-content-between small mb-1">
                  <span>Not active</span><span>{{ $notActivePct }}%</span>
                </div>
                <div class="progress progress-sm">
                  <div class="progress-bar bg-red" style="width: {{ $notActivePct }}%" role="progressbar"
                    aria-valuenow="{{ $notActivePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Membership payment revenue (last 6 months)</h3>
            </div>
            <div class="card-body">
              @foreach ($revenueByMonth as $row)
                @php
                  $pct = $maxRevMonth > 0 ? round(($row['value'] / $maxRevMonth) * 100) : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $row['month'] }}</span>
                    <span>₱{{ number_format($row['value'], 2) }}</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-azure" style="width: {{ $pct }}%" role="progressbar"
                      aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Patient subscriptions</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Plan</th>
                <th>Start</th>
                <th>Expiry / renewal</th>
                <th>Status</th>
                <th class="text-end">Sessions left</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($subscriptions as $sub)
                <tr>
                  <td>
                    <a href="{{ route('admin.patients.show', $sub->patient_id) }}">{{ $sub->patient->name ?? '—' }}</a>
                    <div class="text-secondary small">{{ $sub->patient->email ?? '' }}</div>
                  </td>
                  <td>
                    <a href="{{ route('admin.subscriptions.show', $sub->membership_plan_id) }}">{{ $sub->membershipPlan->name ?? '—' }}</a>
                  </td>
                  <td>{{ $sub->start_date?->format('M d, Y') ?? '—' }}</td>
                  <td>
                    @if ($sub->end_date)
                      {{ $sub->end_date->format('M d, Y') }} <span class="text-secondary small">(end)</span>
                    @elseif ($sub->renewal_date)
                      {{ $sub->renewal_date->format('M d, Y') }} <span class="text-secondary small">(renewal)</span>
                    @else
                      —
                    @endif
                  </td>
                  <td>
                    <span class="badge {{ $sub->status_badge }}">{{ ucfirst((string) $sub->status) }}</span>
                  </td>
                  <td class="text-end">{{ number_format((int) $sub->sessions_remaining) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-secondary py-4">No subscriptions match the filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($subscriptions->hasPages())
          <div class="card-footer d-flex justify-content-center">
            {{ $subscriptions->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
