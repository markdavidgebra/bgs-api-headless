@extends('doctor.layouts.master')

@section('title', 'Availability')

@section('content')
  <style>
    .av-btn {
      border-radius: 8px;
      font-weight: 700;
      font-size: 12px;
      padding: 6px 12px;
    }

    .av-btn-primary {
      border: 1px solid #1d4ed8;
      background: #1d4ed8;
      color: #fff !important;
    }

    .av-btn-danger {
      border: 1px solid #dc2626;
      background: #dc2626;
      color: #fff !important;
    }

    .av-btn-light {
      border: 1px solid #94a3b8;
      background: #fff;
      color: #0f172a !important;
    }

    .section-title-sm {
      font-size: 14px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #6b7280;
      margin-bottom: 12px;
    }

    .form-switch .form-check-input {
      width: 2.5em;
      cursor: pointer;
    }

    /* Doctor theme may not define admin Tabler classes like bg-success-lt */
    .av-status-active {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
    }

    .av-status-off {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #cbd5e1;
    }
  </style>

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Doctor <span></span> Availability
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('doctor.layouts.sidebar')

              <div class="col-12">
                <div class="account dashboard-content pl-50">
                  <div class="section-title mb-20">
                    <h3>Availability</h3>
                    <p class="mb-0">Manage your weekly schedule and blocked dates.</p>
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

                  {{-- Section 3 — Quick Toggle (Optional) --}}
                  <div class="card mb-25">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Quick toggle</h5>
                      <span class="text-muted small">Enable / disable each day</span>
                    </div>
                    <div class="card-body">
                      <div class="row g-3">
                        @foreach ($weeklySchedules as $schedule)
                          <div class="col-md-4 col-sm-6">
                            <div class="d-flex align-items-center justify-content-between border rounded p-3">
                              <span class="font-sm fw-bold">{{ $schedule->day_label }}</span>
                              <form method="POST" action="{{ route('doctor.availability.toggle', $schedule->weekday) }}" class="m-0">
                                @csrf
                                <div class="form-check form-switch mb-0">
                                  <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="toggle-{{ $schedule->weekday }}"
                                    {{ $schedule->is_active ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                </div>
                              </form>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>

                  {{-- Section 1 — Weekly Schedule --}}
                  <div class="card mb-25">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Weekly schedule</h5>
                      <i class="fa-regular fa-copy text-muted small" title="Copy schedule (coming soon)"></i>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Day</th>
                              <th>Status</th>
                              <th>Time slots</th>
                              <th class="text-end">
                                <span class="me-1">Action</span>
                                <i class="fa-regular fa-copy text-muted small" title="Copy"></i>
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($weeklySchedules as $schedule)
                              <tr>
                                <td>{{ $schedule->day_label }}</td>
                                <td>
                                  @if ($schedule->is_active)
                                    <span class="av-status-active">Active</span>
                                  @else
                                    <span class="av-status-off">Off</span>
                                  @endif
                                </td>
                                <td>{{ $schedule->time_slot_label }}</td>
                                <td class="text-end">
                                  <a href="{{ route('doctor.availability.day.edit', $schedule->weekday) }}" class="btn btn-xs av-btn av-btn-light">Edit</a>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  {{-- Section 2 — Blocked Dates --}}
                  <div class="card mb-25">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Blocked dates</h5>
                      <i class="fa-regular fa-copy text-muted small" title="Copy"></i>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Reason</th>
                              <th class="text-end">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($blockedDates as $blocked)
                              <tr>
                                <td>{{ $blocked->blocked_date?->format('M d, Y') }}</td>
                                <td>{{ $blocked->reason ?: '—' }}</td>
                                <td class="text-end">
                                  <form method="POST" action="{{ route('doctor.availability.blocked.destroy', $blocked) }}" class="d-inline"
                                    onsubmit="return confirm('Remove this blocked date?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs av-btn av-btn-danger">Delete</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="3" class="text-center text-secondary py-4">No blocked dates yet.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="card-body border-top">
                      <p class="section-title-sm mb-2">Add block date</p>
                      <form method="POST" action="{{ route('doctor.availability.blocked.store') }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                          <label class="form-label">Date</label>
                          <input type="date" name="blocked_date" class="form-control" value="{{ old('blocked_date') }}" required>
                        </div>
                        <div class="col-md-5">
                          <label class="form-label">Reason</label>
                          <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="Leave, holiday, etc.">
                        </div>
                        <div class="col-md-3">
                          <button type="submit" class="btn btn-sm av-btn av-btn-primary w-100">+ Add block date</button>
                        </div>
                      </form>
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
