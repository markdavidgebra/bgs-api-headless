@extends('admin.layouts.master')

@section('content')
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle text-secondary">Access</div>
          <h2 class="page-title">Edit Role</h2>
          <div class="text-secondary small mt-1">Update role details and access permissions.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('admin.roles.index') }}" class="btn">Cancel</a>
            <button type="submit" form="role-edit-form" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <form id="role-edit-form" method="POST" action="{{ route('admin.roles.update', $role->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="name">Role name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                  value="{{ old('name', $role->name) }}" placeholder="e.g. Content Manager" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="role_value">Role value (optional)</label>
                <input id="role_value" type="text" name="role_value" class="form-control @error('role_value') is-invalid @enderror"
                  value="{{ old('role_value', $role->role_value) }}" placeholder="e.g. content manager">
                <small class="form-hint">If empty, it will be generated from role name.</small>
                @error('role_value')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional role notes">{{ old('description', $role->description) }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label class="form-label">Access permissions</label>
                @error('permissions')
                  <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                @php
                  $selectedPermissions = old('permissions', $role->permissions ?? []);
                @endphp
                @foreach ($permissionGroups as $groupName => $permissions)
                  <div class="border rounded p-3 mb-3">
                    <div class="fw-semibold mb-2">{{ $groupName }}</div>
                    <div class="row g-2">
                      @foreach ($permissions as $permission)
                        <div class="col-md-4">
                          <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission['key'] }}"
                              @checked(in_array($permission['key'], $selectedPermissions, true))>
                            <span class="form-check-label">{{ $permission['label'] }}</span>
                          </label>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
