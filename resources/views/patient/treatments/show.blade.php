@extends('patient.layouts.master')

@section('title', 'Treatment details')

@section('content')
  @php
    $statusClass = fn (?string $key) => match ($key) {
        'completed' => 'text-success',
        'cancelled' => 'text-danger',
        'ongoing' => 'text-primary',
        default => 'text-warning',
    };

    $doctorNames = $treatment?->doctors?->pluck('name')->filter()->unique() ?? collect();
    $doctorLabel = $doctorNames->isNotEmpty() ? $doctorNames->implode(', ') : '—';

    $desc = trim(strip_tags((string) ($treatment?->description ?? '')));
    $aftercare = trim((string) ($treatment?->aftercare ?? ''));

    $notes = trim((string) ($patientPackage->notes ?? ''));
    $sessionsText = $totalSessions > 0 ? "{$sessionsDone} / {$totalSessions} sessions completed" : 'Sessions not set';
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span>
          <a href="{{ route('patient.treatments') }}">My treatments</a>
          <span></span> Details
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-12 col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-4">
                    <div class="card-header p-0 pb-10 d-flex align-items-center justify-content-between flex-wrap">
                      <div>
                        <h3 class="mb-0">{{ $treatment?->name ?? 'Treatment' }}</h3>
                        <p class="mb-0 text-muted font-sm">
                          Package ref. #{{ $patientPackage->id }}
                          <span class="mx-2">&middot;</span>
                          <span class="{{ $statusClass($displayStatus) }}">{{ $displayLabel }}</span>
                        </p>
                      </div>
                      <a href="{{ route('patient.treatments') }}" class="font-sm mb-10">Back</a>
                    </div>
                  </div>

                  <div class="card mb-30">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Category</h6>
                          <p class="mb-0">{{ $treatment?->category ?: '—' }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Doctor</h6>
                          <p class="mb-0">{{ $doctorLabel }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Start date</h6>
                          <p class="mb-0">{{ $dateStarted }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">End date</h6>
                          <p class="mb-0">{{ $endDate }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Last session date</h6>
                          <p class="mb-0">{{ $lastSessionDate }}</p>
                        </div>
                        <div class="col-md-6 mb-15">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Sessions</h6>
                          <p class="mb-0">{{ $sessionsText }}</p>
                        </div>
                      </div>

                      <div class="mt-10">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                          <p class="mb-5 font-sm text-muted">Progress</p>
                          <p class="mb-5 font-sm">{{ $progressPercent }}%</p>
                        </div>
                        <div class="progress" style="height: 10px;">
                          <div
                            class="progress-bar"
                            role="progressbar"
                            style="width: {{ $progressPercent }}%;"
                            aria-valuenow="{{ $progressPercent }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                          ></div>
                        </div>
                        @if ($totalSessions > 0)
                          <p class="mt-10 mb-0 font-sm text-muted">{{ $sessionsDone }} of {{ $totalSessions }} sessions completed</p>
                        @endif
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-7 mb-25">
                      <div class="card h-100 mb-0">
                        <div class="card-body">
                          <h5 class="mb-10">Description</h5>
                          <p class="mb-0">{{ $desc !== '' ? $desc : '—' }}</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-5 mb-25">
                      <div class="card h-100 mb-0">
                        <div class="card-body">
                          <h5 class="mb-10">Notes</h5>
                          <p class="mb-0">{{ $notes !== '' ? $notes : '—' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-30">
                    <div class="card-body">
                      <h5 class="mb-10">Aftercare instruction</h5>
                      @if ($aftercare !== '')
                        <div class="font-sm">{!! nl2br(e($aftercare)) !!}</div>
                      @else
                        <p class="mb-0">—</p>
                      @endif
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header p-0 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Session history</h3>
                      <span class="font-sm text-muted">{{ $patientPackage->usageHistories->count() }} record(s)</span>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table m-0 mt-20">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Session</th>
                              <th>Staff / doctor</th>
                              <th>Notes</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @php
                              $sessionCounter = 0;
                            @endphp
                            @forelse ($patientPackage->usageHistories as $h)
                              @php
                                $sessionCounter++;
                                $historyStatus = strtolower((string) ($h->status ?? 'completed'));
                                $historyStatusClass = match ($historyStatus) {
                                    'completed' => 'text-success',
                                    'cancelled' => 'text-danger',
                                    'ongoing' => 'text-primary',
                                    default => 'text-warning',
                                };
                              @endphp
                              <tr>
                                <td>{{ $h->used_on ? \Illuminate\Support\Carbon::parse((string) $h->used_on)->format('M j, Y') : '—' }}</td>
                                <td>Session {{ $sessionCounter }}</td>
                                <td>{{ $doctorLabel }}</td>
                                <td>
                                  {{ $h->notes ?: ($h->service?->name ? 'Service: '.$h->service->name : '—') }}
                                </td>
                                <td><span class="{{ $historyStatusClass }}">{{ ucfirst($historyStatus) }}</span></td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted py-40">
                                  No sessions recorded yet.
                                </td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card mt-30">
                    <div class="card-body">
                      <h5 class="mb-10">Related package / membership</h5>
                      <p class="mb-0">
                        <span class="text-muted">Package:</span> {{ $treatment?->name ?? '—' }}
                        @if ($treatment?->price !== null)
                          <span class="text-muted">&middot;</span> ₱{{ number_format((float) $treatment->price, 2) }}
                        @endif
                      </p>
                      <p class="mb-0 mt-5 text-muted font-sm">Membership integration is not linked yet in the current data model.</p>
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

