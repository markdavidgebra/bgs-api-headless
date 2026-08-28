@extends('patient.layouts.master')

@section('title', 'My treatments')

@section('content')
  @php
    $treatmentStatusClass = fn (?string $key) => match ($key) {
        'completed' => 'text-success',
        'cancelled' => 'text-danger',
        'ongoing' => 'text-primary',
        default => 'text-warning',
    };

    $filterLinks = [
        'all' => 'All',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> My treatments
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
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">My treatments (packages)</h3>
                      <p class="mb-0 text-muted font-sm">
                        Track session progress, assigned doctors, and status for each treatment package.
                      </p>
                    </div>
                  </div>

                  <div class="card mb-20">
                    <div class="card-body p-20">
                      <p class="font-sm text-muted mb-10 mb-md-0">Filter by status</p>
                      <div class="d-flex flex-wrap">
                        @foreach ($filterLinks as $key => $label)
                          <a
                            href="{{ route('patient.treatments', ['status' => $key]) }}"
                            class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-outline-primary' }} mr-10 mb-10"
                          >
                            {{ $label }}
                          </a>
                        @endforeach
                      </div>
                    </div>
                  </div>

                  <div class="row d-lg-none">
                    @forelse ($treatments as $row)
                      @php
                        $mobileTotalSessions = (int) ($row->total_sessions ?? 0);
                        $mobileDoneSessions = (int) ($row->sessions_done ?? 0);
                        $mobileStatusKey = (string) ($row->display_status ?? 'ongoing');
                        $mobileStatusLabel = (string) ($row->display_label ?? 'Ongoing');
                        $mobileCanBeCompleted = $mobileTotalSessions > 0 && $mobileDoneSessions >= $mobileTotalSessions;
                        if ($mobileStatusKey === 'completed' && ! $mobileCanBeCompleted) {
                            $mobileStatusKey = 'ongoing';
                            $mobileStatusLabel = 'Ongoing';
                        }
                      @endphp
                      <div class="col-md-6 mb-25">
                        <div class="card h-100 mb-0">
                          <div class="card-body p-25">
                            <h5 class="mb-10">
                              <a href="{{ route('patient.treatments.show', $row->id) }}">{{ $row->treatment_name }}</a>
                            </h5>
                            <p class="font-sm text-muted mb-5">{{ $row->category ?: '—' }}</p>
                            <p class="font-sm mb-5"><span class="text-muted">Clinical staff:</span> {{ $row->clinical_staff_label }}</p>
                            <p class="font-sm mb-5"><span class="text-muted">Started:</span> {{ $row->date_started }}</p>
                            <p class="font-sm mb-5"><span class="text-muted">Last session:</span> {{ $row->last_session }}</p>
                            <p class="font-sm mb-5">
                              <span class="text-muted">Sessions:</span>
                              {{ $row->sessions_done }} / {{ $row->total_sessions }}
                              <span class="text-muted">(done / total)</span>
                            </p>
                            <p class="font-sm mb-0">
                              <span class="{{ $treatmentStatusClass($mobileStatusKey) }}">{{ $mobileStatusLabel }}</span>
                            </p>
                            <p class="mt-10 mb-0">
                              <a href="{{ route('patient.treatments.show', $row->id) }}" class="font-sm">View details</a>
                            </p>
                          </div>
                        </div>
                      </div>
                    @empty
                      <div class="col-12">
                        <div class="card">
                          <div class="card-body text-center text-muted py-40">
                            No treatments match this filter.
                          </div>
                        </div>
                      </div>
                    @endforelse
                  </div>

                  <div class="card d-none d-lg-block">
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="order_table table m-0 mt-0">
                          <thead>
                            <tr>
                              <th>Treatment</th>
                              <th>Category</th>
                              <th>Assigned doctor</th>
                              <th>Date started</th>
                              <th>Last session</th>
                              <th class="text-center">Total</th>
                              <th class="text-center">Done</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($treatments as $row)
                              @php
                                $desktopTotalSessions = (int) ($row->total_sessions ?? 0);
                                $desktopDoneSessions = (int) ($row->sessions_done ?? 0);
                                $desktopStatusKey = (string) ($row->display_status ?? 'ongoing');
                                $desktopStatusLabel = (string) ($row->display_label ?? 'Ongoing');
                                $desktopCanBeCompleted = $desktopTotalSessions > 0 && $desktopDoneSessions >= $desktopTotalSessions;
                                if ($desktopStatusKey === 'completed' && ! $desktopCanBeCompleted) {
                                    $desktopStatusKey = 'ongoing';
                                    $desktopStatusLabel = 'Ongoing';
                                }
                              @endphp
                              <tr>
                                <td>
                                  <a href="{{ route('patient.treatments.show', $row->id) }}">{{ $row->treatment_name }}</a>
                                </td>
                                <td>{{ $row->category ?: '—' }}</td>
                                <td>{{ $row->clinical_staff_label }}</td>
                                <td>{{ $row->date_started }}</td>
                                <td>{{ $row->last_session }}</td>
                                <td class="text-center">{{ $row->total_sessions }}</td>
                                <td class="text-center">{{ $row->sessions_done }}</td>
                                <td>
                                  <span class="{{ $treatmentStatusClass($desktopStatusKey) }}">{{ $desktopStatusLabel }}</span>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="8" class="text-center text-muted py-40">
                                  No treatments match this filter.
                                </td>
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
        </div>
      </div>
    </div>
  </main>
@endsection
