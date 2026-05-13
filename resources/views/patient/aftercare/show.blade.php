@extends('patient.layouts.master')

@section('title', 'Aftercare details')

@section('content')
  @php
    /** @var object $item */
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
          <span></span> Patient <span></span>
          <a href="{{ route('patient.aftercare-instructions') }}">Aftercare instructions</a>
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
                  <div class="card mb-25">
                    <div class="card-header p-0 pb-10 d-flex align-items-center justify-content-between flex-wrap">
                      <h3 class="mb-0">Aftercare details</h3>
                      <a href="{{ route('patient.aftercare-instructions') }}" class="btn btn-sm btn-outline-secondary">Back to list</a>
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-body">
                      <div class="row mb-15">
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Source</h6>
                          <p class="mb-0"><span class="{{ $sourceBadge($item->source) }}">{{ $sourceLabel($item->source) }}</span></p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Last Updated</h6>
                          <p class="mb-0">{{ $item->updated_at ? $item->updated_at->format('M j, Y g:i A') : '—' }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Title</h6>
                          <p class="mb-0">{{ $item->title }}</p>
                        </div>
                        <div class="col-md-6 mb-10">
                          <h6 class="text-muted font-sm text-uppercase mb-5">Reference</h6>
                          <p class="mb-0">{{ $item->subtitle }}</p>
                        </div>
                      </div>

                      <hr>

                      <h5 class="mb-10">Instructions</h5>
                      <div class="alert alert-light mb-0" style="white-space: pre-line;">
                        {{ $item->instructions }}
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
