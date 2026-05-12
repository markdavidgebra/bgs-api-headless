@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row align-items-center g-3">
        <div class="col-auto">
          <span class="avatar avatar-xl rounded bg-azure-lt text-azure">{{ strtoupper(substr($staff->name ?? '?', 0, 1)) }}</span>
        </div>
        <div class="col">
          <div class="page-pretitle text-secondary">Staff profile</div>
          <h2 class="page-title mb-1">{{ $staff->name }}</h2>
          <div class="text-secondary">{{ $staff->email }}</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.staffs.edit', $staff->id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('admin.staffs') }}" class="btn">Back</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-body">
          <div class="datagrid">
            <div class="datagrid-item">
              <div class="datagrid-title">ID</div>
              <div class="datagrid-content font-monospace">#{{ $staff->id }}</div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Name</div>
              <div class="datagrid-content">{{ $staff->name }}</div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Email</div>
              <div class="datagrid-content">{{ $staff->email }}</div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Role</div>
              <div class="datagrid-content"><span class="badge bg-azure-lt">{{ $staff->role ?: '—' }}</span></div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Status</div>
              <div class="datagrid-content">
                @php
                  $staffStatus = strtolower((string) ($staff->status ?? 'draft'));
                  $statusMap = [
                    'approved' => ['label' => 'Approved', 'badge' => 'bg-green-lt'],
                    'disapproved' => ['label' => 'Disapproved', 'badge' => 'bg-red-lt'],
                    'draft' => ['label' => 'Draft', 'badge' => 'bg-secondary-lt'],
                  ];
                  $statusMeta = $statusMap[$staffStatus] ?? $statusMap['draft'];
                @endphp
                <span class="badge {{ $statusMeta['badge'] }}">
                  {{ $statusMeta['label'] }}
                </span>
              </div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Approved At</div>
              <div class="datagrid-content">{{ $staff->approved_at?->format('M j, Y h:i A') ?? '—' }}</div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Created</div>
              <div class="datagrid-content">{{ $staff->created_at?->format('M j, Y h:i A') ?? '—' }}</div>
            </div>
            <div class="datagrid-item">
              <div class="datagrid-title">Updated</div>
              <div class="datagrid-content">{{ $staff->updated_at?->format('M j, Y h:i A') ?? '—' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
