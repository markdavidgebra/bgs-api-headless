@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Reports</div>
          <h2 class="page-title">Patients report</h2>
          <div class="text-secondary small mt-1">
            KPIs <strong>new / returning / inactive</strong> use <strong>all-time</strong> appointments (returning = 2+ visits).
            @if ($hasActivityRange)
              Table <strong>visits</strong> and <strong>spent</strong> use your date range. Top spender uses the same range.
            @else
              Table shows <strong>all-time</strong> visits and paid + partial spend.
            @endif
            <strong>New / month</strong> is account registrations (<code>created_at</code>).
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <a href="{{ route('admin.reports') }}" class="btn">Overview</a>
          <a href="{{ route('admin.patients') }}" class="btn btn-primary">Patients</a>
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
              <div class="text-secondary">Total (filtered)</div>
              <div class="h3 mb-0">{{ number_format($totalPatients) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">New / prospect</div>
              <div class="h3 mb-0">{{ number_format($newPatients) }}</div>
              <div class="text-secondary small mt-1">Active, &lt;2 visits</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Returning</div>
              <div class="h3 mb-0">{{ number_format($returningPatients) }}</div>
              <div class="text-secondary small mt-1">Active, 2+ visits</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Inactive accounts</div>
              <div class="h3 mb-0">{{ number_format($inactivePatients) }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary">Top spending patient</div>
              <div class="h3 mb-0">{{ $topSpendingName }}</div>
              <div class="text-secondary small mt-1">Paid + partial @if ($hasActivityRange)(range) @endif</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.patients') }}">
            <div class="col-md-6 col-lg-3">
              <label class="form-label" for="search">Search</label>
              <input id="search" name="search" type="search" class="form-control" value="{{ request('search') }}"
                placeholder="Name or email" autocomplete="off">
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="account_status">Account status</label>
              <select id="account_status" name="account_status" class="form-select">
                <option value="">All</option>
                @foreach ($accountStatusOptions as $value => $label)
                  <option value="{{ $value }}" @selected(request('account_status') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="from">Activity from</label>
              <input id="from" name="from" type="date" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-6 col-lg-2">
              <label class="form-label" for="to">to</label>
              <input id="to" name="to" type="date" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-lg-2 d-grid">
              <button type="submit" class="btn btn-primary">Apply</button>
            </div>
          </form>
        </div>
      </div>

      <div class="row row-cards mb-3">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">New registrations (last 6 months)</h3>
            </div>
            <div class="card-body">
              @foreach ($newPerMonth as $m)
                @php
                  $pct = $maxNew > 0 ? round(($m['count'] / $maxNew) * 100) : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $m['month'] }}</span>
                    <span>{{ number_format($m['count']) }}</span>
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
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Returning vs new (active patients)</h3>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                  <span>Returning</span><span>{{ $returningPct }}%</span>
                </div>
                <div class="progress progress-sm">
                  <div class="progress-bar bg-green" style="width: {{ $returningPct }}%"></div>
                </div>
              </div>
              <div class="mb-0">
                <div class="d-flex justify-content-between small mb-1">
                  <span>New / prospect</span><span>{{ $newPct }}%</span>
                </div>
                <div class="progress progress-sm">
                  <div class="progress-bar bg-azure" style="width: {{ $newPct }}%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Patients</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Patient</th>
                <th class="text-end">Visits</th>
                <th class="text-end">Spent</th>
                <th>Last visit</th>
                <th>Subscription</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($patients as $row)
                <tr>
                  <td>
                    <a href="{{ route('admin.patients.show', $row['patient']->id) }}">{{ $row['patient']->name }}</a>
                    <div class="text-secondary small">{{ $row['patient']->email }}</div>
                  </td>
                  <td class="text-end">{{ number_format($row['visits']) }}</td>
                  <td class="text-end">₱{{ number_format($row['spent'], 2) }}</td>
                  <td>
                    @if ($row['last_visit'])
                      {{ \Illuminate\Support\Carbon::parse($row['last_visit'])->format('M d, Y') }}
                    @else
                      —
                    @endif
                  </td>
                  <td>
                    @if ($row['membership_active'])
                      <span class="badge bg-green-lt">Active plan</span>
                    @else
                      <span class="badge bg-secondary-lt">None</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-secondary py-4">No patients match the filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($patients->hasPages())
          <div class="card-footer d-flex justify-content-center">
            {{ $patients->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
