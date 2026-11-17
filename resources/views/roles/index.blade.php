@extends('layouts.app')

@section('title', 'Role Management')

@section('content')

<div class="page-header">
    <div>
        <h1>Role Management</h1>
        <p>Manage system roles and their access permissions.</p>
    </div>

    @can('role-management.create')
        <button type="button" class="btn-primary" onclick="openRoleModal()">
            + Add Role
        </button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="user-summary-grid">
    <div class="user-summary-card">
        <span>Total Roles</span>
        <strong>{{ $roles->total() }}</strong>
    </div>

    <div class="user-summary-card">
        <span>System Roles</span>
        <strong>{{ \App\Models\Role::where('is_system', true)->count() }}</strong>
    </div>

    <div class="user-summary-card">
        <span>Custom Roles</span>
        <strong>{{ \App\Models\Role::where('is_system', false)->count() }}</strong>
    </div>
</div>

<div class="content-card">

    <form method="GET" action="{{ route('roles.index') }}" class="filter-form">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search role name or description..."
        >

        <button type="submit" class="btn-secondary">Search</button>

        <a href="{{ route('roles.index') }}" class="btn-secondary">
            Reset
        </a>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Description</th>
                    <th>Users</th>
                    <th>Permissions</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                        </td>

                        <td>{{ $role->description ?: '—' }}</td>

                        <td>{{ $role->users_count }}</td>

                        <td>{{ $role->permissions->count() }}</td>

                        <td>
                            @if($role->is_system)
                                <span class="status-badge status-active">
                                    System
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    Custom
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-buttons">

                                @can('role-management.update')
                                    <button
                                        type="button"
                                        class="btn-small btn-edit"
                                        onclick="editRole(
                                            {{ Js::from([
                                                'id' => $role->id,
                                                'name' => $role->name,
                                                'description' => $role->description,
                                                'permission_ids' => $role->permissions->pluck('id')->values(),
                                            ]) }}
                                        )">
                                        Edit
                                    </button>
                                @endcan

                                @can('role-management.delete')
                                    @if(!$role->is_system)
                                        <form
                                            method="POST"
                                            action="{{ route('roles.destroy', $role) }}"
                                            onsubmit="return confirm('Delete this role?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-small btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No roles found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $roles->links() }}
    </div>
</div>


{{-- ADD / EDIT ROLE MODAL --}}

<div class="modal-overlay" id="roleModal">
    <div class="modal role-modal">

        <div class="modal-header">
            <div>
                <h2 id="roleModalTitle">Add New Role</h2>
                <p>Configure the role and its permissions.</p>
            </div>

            <button type="button" class="modal-close" onclick="closeRoleModal()">
                &times;
            </button>
        </div>

        <form id="roleForm" method="POST" action="{{ route('roles.store') }}">
            @csrf

            <input type="hidden" name="_method" id="roleMethod" value="POST">

            <div class="form-grid">
                <div class="form-group">
                    <label>Role Name *</label>
                    <input
                        type="text"
                        name="name"
                        id="roleName"
                        required
                        maxlength="100"
                        placeholder="Example: Division Encoder"
                    >
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <input
                        type="text"
                        name="description"
                        id="roleDescription"
                        placeholder="Describe the role's responsibilities"
                    >
                </div>
            </div>

            <div class="permission-section">
                <div class="permission-heading">
                    <div>
                        <h3>Access Permissions</h3>
                        <p>Select the functions this role can access.</p>
                    </div>

                    <button type="button" class="btn-secondary" onclick="toggleAllPermissions(true)">
                        Select All
                    </button>

                    <button type="button" class="btn-secondary" onclick="toggleAllPermissions(false)">
                        Clear All
                    </button>
                </div>

                @forelse($permissions as $module => $modulePermissions)
                    <div class="permission-module">
                        <h4>{{ $module }}</h4>

                        <div class="permission-grid">
                            @foreach($modulePermissions as $permission)
                                <label class="permission-option">
                                    <input
                                        type="checkbox"
                                        name="permission_ids[]"
                                        value="{{ $permission->id }}"
                                        class="role-permission"
                                    >

                                    <span>
                                        <strong>{{ $permission->name }}</strong>

                                        @if($permission->description)
                                            <small>{{ $permission->description }}</small>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p>No permissions have been registered yet.</p>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRoleModal()">
                    Cancel
                </button>

                <button type="submit" class="btn-primary">
                    Save Role
                </button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
    const roleStoreUrl = @json(route('roles.store'));
    const roleUpdateUrl = @json(url('/role-management'));

    function openRoleModal() {
        document.getElementById('roleForm').reset();

        document.getElementById('roleForm').action = roleStoreUrl;
        document.getElementById('roleMethod').value = 'POST';
        document.getElementById('roleModalTitle').textContent = 'Add New Role';

        document.getElementById('roleModal').classList.add('show');
    }

    function closeRoleModal() {
        document.getElementById('roleModal').classList.remove('show');
    }

    function editRole(role) {
        const form = document.getElementById('roleForm');

        form.reset();

        form.action = roleUpdateUrl + '/' + role.id;
        document.getElementById('roleMethod').value = 'PUT';

        document.getElementById('roleModalTitle').textContent = 'Edit Role';
        document.getElementById('roleName').value = role.name;
        document.getElementById('roleDescription').value = role.description ?? '';

        const selectedPermissions = role.permission_ids.map(String);

        document.querySelectorAll('.role-permission').forEach(checkbox => {
            checkbox.checked = selectedPermissions.includes(checkbox.value);
        });

        document.getElementById('roleModal').classList.add('show');
    }

    function toggleAllPermissions(checked) {
        document.querySelectorAll('.role-permission').forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    document.getElementById('roleModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeRoleModal();
        }
    });
</script>
@endpush