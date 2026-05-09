@extends('admin.layouts.master')

@section('content')
 

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-3 align-items-center">
        <div class="col-auto">
            <span class="avatar avatar-xl rounded" style="background-image: url('{{ $service->image_url }}')"></span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Service profile</div>
          <h2 class="page-title">{{ $service->name }}</h2>
          <div class="text-secondary mt-1">
            @if (! empty($subtitleParts))
              {{ implode(' · ', $subtitleParts) }}
              @if ($service->status_label !== '')
                ·
              @endif
            @endif
            @if ($service->status_label !== '')
              <span class="badge {{ $service->getStatusBadgeAttribute() }}">{{ ucfirst($service->status_label) }}</span>
            @endif
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-primary">Edit service</a>
            <a href="{{ route('admin.services') }}" class="btn">Back to services</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Price</div>
              <div class="h2 mb-0">
                  @if ($service->promo_price !== null)
                  <span class="text-secondary text-decoration-line-through h3 d-block">₱{{ number_format($service->price, 2) }}</span>
             
                  ₱{{ number_format($service->promo_price, 2) }}
                @else
                  ₱{{ number_format($service->price, 2) }}
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Duration</div>
              <div class="h2 mb-0">{{ $service->duration_label }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Assigned doctors</div>
              <div class="mb-0 small">
                @if ($service->doctors->isEmpty())
                  <span class="text-secondary">Any (not restricted)</span>
                @else
                  {{ $service->doctors->pluck('name')->join(', ') }}
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="text-secondary mb-1">Status</div>
              <div class="h2 mb-0">
                @if ($service->status_label !== '')
                  <span class="badge {{ $service->getStatusBadgeAttribute() }}">{{ ucfirst($service->status_label) }}</span>
                @endif
              </div>
              <div class="text-secondary small mt-1"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Service details</h3>
            </div>
            <div class="card-body">
              <div class="datagrid">
                <div class="datagrid-item">
                  <div class="datagrid-title">Service ID</div>
                  <div class="datagrid-content font-monospace">#{{ $service->id }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Slug</div>
                  <div class="datagrid-content">{{ $service->slug ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Short description</div>
                  <div class="datagrid-content">{{ $service->short_description ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Description</div>
                  <div class="datagrid-content">{{ $service->description ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Sessions</div>
                  <div class="datagrid-content">{{ $service->session_count ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Recovery time</div>
                  <div class="datagrid-content">{{ $service->recovery_time ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Max appointments / day</div>
                  <div class="datagrid-content">{{ $service->max_appointments_per_day ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Featured</div>
                  <div class="datagrid-content">{{ $service->is_featured ? 'Yes' : '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Bookable</div>
                  <div class="datagrid-content">{{ $service->is_bookable ? 'Yes' : '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Before care</div>
                  <div class="datagrid-content">{{ $service->before_care ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">After care</div>
                  <div class="datagrid-content">{{ $service->after_care ?? '' }}</div>
                </div>
                <div class="datagrid-item">
                  <div class="datagrid-title">Internal notes</div>
                  <div class="datagrid-content">{{ $service->notes ?? '' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent appointments</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-vcenter">
                  <thead>
                    <tr>
                      <th>Code</th>
                      <th>Patient</th>
                      <th class="text-nowrap">Date/Time</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse (($service->appointments ?? []) as $appt)
                      <tr>
                        <td class="font-monospace">{{ $appt->code ?? '' }}</td>
                        <td>{{ $appt->patient->name ?? '' }}</td>
                        <td class="text-nowrap">{{ $appt->date->format('M d, Y') ?? '' }} {{ $appt->time ?? '' }}</td>
                        <td>
                          <span class="badge {{ $appt->status_badge }}">{{ ucfirst($appt->status_label) }}</span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-secondary py-4"></td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">Quick actions</h3>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-primary">Edit details</a>
                <a href="{{ route('admin.services') }}" class="btn btn-outline-secondary">Back to list</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
