@extends('patient.layouts.master')

@section('title', 'Aftercare instructions')

@section('content')
  @php
    $sourceBadge = fn (string $source) => match ($source) {
        'appointment' => 'text-primary',
        'treatment' => 'text-success',
        'membership' => 'text-info',
        default => 'text-muted',
    };

    $sourceLabel = fn (string $source) => match ($source) {
        'appointment' => 'Appointment',
        'treatment' => 'Treatment',
        'membership' => 'Membership',
        default => ucfirst($source),
    };
  @endphp

  <main class="main pages">
    <div class="page-header breadcrumb-wrap">
      <div class="container">
        <div class="breadcrumb">
          <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
          <span></span> Patient <span></span> Aftercare instructions
        </div>
      </div>
    </div>

    <div class="page-content pt-70 pb-60">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="row">
              @include('patient.layouts.sidebar')
              <div class="col-md-9">
                <div class="account dashboard-content pl-50">
                  <div class="card mb-25">
                    <div class="card-header p-0 pb-10">
                      <h3 class="mb-0">My aftercare instructions</h3>
                      <p class="mb-0 text-muted font-sm">
                        Follow these care reminders after appointments, treatments, or memberships.
                      </p>
                    </div>
                  </div>

                  @if ($instructions->isEmpty())
                    <div class="card">
                      <div class="card-body text-center text-muted py-40">
                        No aftercare instructions yet. Once your doctor or clinic adds instructions, they will appear here.
                      </div>
                    </div>
                  @else
                    <div class="card">
                      <div class="card-body p-0">
                        <div class="table-responsive">
                          <table class="order_table table mt-20">
                            <thead>
                              <tr>
                                <th>Source</th>
                                <th>Title</th>
                                <th>Reference</th>
                                <th>Instructions</th>
                                <th>Updated</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($instructions as $item)
                                <tr>
                                  <td>
                                    <span class="{{ $sourceBadge($item->source) }}">{{ $sourceLabel($item->source) }}</span>
                                  </td>
                                  <td>{{ $item->title }}</td>
                                  <td>{{ $item->subtitle }}</td>
                                  <td style="white-space: pre-line;">{{ \Illuminate\Support\Str::limit($item->instructions, 180) }}</td>
                                  <td>{{ $item->updated_at ? $item->updated_at->format('M j, Y g:i A') : '—' }}</td>
                                  <td>
                                    <a href="{{ route('patient.aftercare-instructions.show', ['source' => $item->source, 'record' => $item->record_id]) }}"
                                      class="btn btn-sm btn-outline-primary">
                                      View
                                    </a>
                                  </td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
