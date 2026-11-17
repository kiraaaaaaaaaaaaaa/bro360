@extends('layouts.app')

@section('title', 'User Management')

@section('content')

<div class="page-header">
    <div>
        <h1>User Management</h1>
        <p>Manage BRO360 user accounts, organizational assignments, and access roles.</p>
    </div>
    <br>

    @can('user-management.create')
        <button class="btn btn-primary" type="button" id="openAddUser">
            + Add User
        </button>
        
    @endcan
    
</div>
<br>

@if(session('success'))
    <div class="user-alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="user-alert error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="user-alert error">
        Please check the form. Some information needs correction.
    </div>
@endif

{{-- SUMMARY CARDS --}}
<div class="user-summary-grid">

    <div class="user-summary-card">
        <span>Total Users</span>
        <strong>{{ number_format($totalUsers) }}</strong>
        <small>Registered accounts</small>
    </div>

    <div class="user-summary-card">
        <span>Active Users</span>
        <strong>{{ number_format($activeUsers) }}</strong>
        <small>Enabled accounts</small>
    </div>

    <div class="user-summary-card">
        <span>Inactive Users</span>
        <strong>{{ number_format($inactiveUsers) }}</strong>
        <small>Disabled accounts</small>
    </div>

</div>

{{-- FILTERS --}}
<div class="user-filter-card">
    <form action="{{ route('users.index') }}" method="GET" class="user-filter-form">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search name, email, or employee number...">

        <select name="division_id">
            <option value="">All Divisions</option>
            @foreach($divisions as $division)
                <option value="{{ $division->id }}"
                    @selected(request('division_id') == $division->id)>
                    {{ $division->name }}
                </option>
            @endforeach
        </select>

        <select name="role_id">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}"
                    @selected(request('role_id') == $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        <select name="status">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status') === 'active')>
                Active
            </option>
            <option value="inactive" @selected(request('status') === 'inactive')>
                Inactive
            </option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>

        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            Reset
        </a>

    </form>
</div>

{{-- USER TABLE --}}
<div class="user-table-card">

    <div class="user-table-heading">
        <div>
            <h3>System Users</h3>
            <p>Personnel registered in BRO360.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="user-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Employee No.</th>
                    <th>Division</th>
                    <th>Position</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-identity">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>

                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <small>{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>

                        <td>{{ $user->employee_no ?: '—' }}</td>

                        <td>{{ $user->division?->name ?? '—' }}</td>

                        <td>{{ $user->position?->title ?? '—' }}</td>

                        <td>
                            <div class="user-role-list">
                                @forelse($user->roles as $role)
                                    <span class="user-role-tag">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-muted">No role</span>
                                @endforelse
                            </div>
                        </td>

                        <td>
                            <span class="user-status {{ $user->is_active ? 'active' : 'inactive' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            <div class="user-actions">

                                @can('user-management.update')
                                    <button type="button"
                                            class="user-action-btn edit-user-btn"
                                            data-user="{{ json_encode([
                                                'id' => $user->id,
                                                'name' => $user->name,
                                                'email' => $user->email,
                                                'employee_no' => $user->employee_no,
                                                'office_id' => $user->office_id,
                                                'division_id' => $user->division_id,
                                                'organizational_unit_id' => $user->organizational_unit_id,
                                                'position_id' => $user->position_id,
                                                'phone' => $user->phone,
                                                'role_ids' => $user->roles->pluck('id')->values(),
                                            ]) }}">
                                        Edit
                                    </button>
                                @endcan

                                @can('user-management.status')
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.toggle-status', $user) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to change this account status?')">
                                            @csrf
                                            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                                          
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="user-action-btn {{ $user->is_active ? 'deactivate' : 'activate' }}">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="user-empty">
                            No user accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="user-pagination">
        {{ $users->links() }}
    </div>

</div>


{{-- ADD / EDIT USER MODAL --}}
<div class="user-modal-overlay" id="userModal">

    <div class="user-modal">

        <div class="user-modal-header">
            <div>
                <h2 id="userModalTitle">Add New User</h2>
                <p>Enter the user's account and organizational information.</p>
            </div>
            

            <button type="button" class="user-modal-close" id="closeUserModal">
                &times;
            </button>
        </div>

        <form id="userForm" method="POST" action="{{ route('users.store') }}">
            @csrf

            <div id="userMethodField"></div>

            <div class="user-modal-body">

                <div class="user-form-section">
                    <h4>Personal and Account Information</h4>

                    <div class="user-form-grid">

                        <div class="form-group">
                            <label>Full Name <span>*</span></label>
                            <input type="text" name="name" id="user_name"
                                   value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Employee Number</label>
                            <input type="text" name="employee_no" id="user_employee_no"
                                   value="{{ old('employee_no') }}">
                        </div>

                        <div class="form-group">
                            <label>Email Address <span>*</span></label>
                            <input type="email" name="email" id="user_email"
                                   value="{{ old('email') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" id="user_phone"
                                   value="{{ old('phone') }}">
                        </div>

                        <div class="form-group">
                            <label>Password <span id="passwordRequired">*</span></label>
                            <input type="password" name="password" id="user_password"
                                   minlength="8" autocomplete="new-password">
                            <small id="passwordHelp">Minimum 8 characters.</small>
                        </div>

                        <div class="form-group">
                            <label>Confirm Password <span id="confirmRequired">*</span></label>
                            <input type="password" name="password_confirmation"
                                   id="user_password_confirmation"
                                   minlength="8" autocomplete="new-password">
                        </div>

                    </div>
                </div>

                <div class="user-form-section">
                    <h4>Organizational Assignment</h4>

                    <div class="user-form-grid">

                        <div class="form-group">
                            <label>Office</label>
                            <select name="office_id" id="user_office_id">
                                <option value="">Select Office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Division</label>
                            <select name="division_id" id="user_division_id">
                                <option value="">Select Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Organizational Unit</label>
                            <select name="organizational_unit_id" id="user_organizational_unit_id">
                                <option value="">Select Organizational Unit</option>
                                @foreach($organizationalUnits as $unit)
                                    <option value="{{ $unit->id }}">
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Position</label>
                            <select name="position_id" id="user_position_id">
                                <option value="">Select Position</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">
                                        {{ $position->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="user-form-section">
                    <h4>System Roles</h4>
                    <p class="user-form-note">
                        Select one or more roles. Permissions are inherited from assigned roles.
                    </p>

                    <div class="user-role-checkboxes">
                        @foreach($roles as $role)
                            <label class="user-role-option">
                                <input type="checkbox"
                                       name="role_ids[]"
                                       value="{{ $role->id }}"
                                       class="user-role-checkbox">

                                <span>
                                    <strong>{{ $role->name }}</strong>
                                    <small>{{ $role->description }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($errors->any())
                    <div class="user-validation-errors">
                        <strong>Please correct the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            <div class="user-modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelUserModal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-primary" id="saveUserButton">
                    Save User
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    const openAdd = document.getElementById('openAddUser');
    const closeButton = document.getElementById('closeUserModal');
    const cancelButton = document.getElementById('cancelUserModal');

    const title = document.getElementById('userModalTitle');
    const methodField = document.getElementById('userMethodField');

    const storeUrl = @json(route('users.store'));
    const updateUrlTemplate = @json(route('users.update', ['user' => '__USER__']));

    function openModal() {
        modal.classList.add('active');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
    }

    function resetForm() {
        form.reset();
        form.action = storeUrl;
        methodField.innerHTML = '';

        document.querySelectorAll('.user-role-checkbox').forEach(input => {
            input.checked = false;
        });

        title.textContent = 'Add New User';
        document.getElementById('saveUserButton').textContent = 'Save User';

        document.getElementById('user_password').required = true;
        document.getElementById('user_password_confirmation').required = true;

        document.getElementById('passwordRequired').style.display = 'inline';
        document.getElementById('confirmRequired').style.display = 'inline';

        document.getElementById('passwordHelp').textContent =
            'Minimum 8 characters.';
    }

    function fillSelect(id, value) {
        document.getElementById(id).value = value ?? '';
    }

    function editUser(user) {
        resetForm();

        title.textContent = 'Edit User Information';
        document.getElementById('saveUserButton').textContent = 'Update User';

        form.action = updateUrlTemplate.replace('__USER__', user.id);

        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('user_name').value = user.name ?? '';
        document.getElementById('user_email').value = user.email ?? '';
        document.getElementById('user_employee_no').value = user.employee_no ?? '';
        document.getElementById('user_phone').value = user.phone ?? '';

        fillSelect('user_office_id', user.office_id);
        fillSelect('user_division_id', user.division_id);
        fillSelect('user_organizational_unit_id', user.organizational_unit_id);
        fillSelect('user_position_id', user.position_id);

        document.getElementById('user_password').required = false;
        document.getElementById('user_password_confirmation').required = false;

        document.getElementById('passwordRequired').style.display = 'none';
        document.getElementById('confirmRequired').style.display = 'none';

        document.getElementById('passwordHelp').textContent =
            'Leave blank to keep the existing password.';

        document.querySelectorAll('.user-role-checkbox').forEach(input => {
            input.checked = (user.role_ids ?? []).map(String).includes(input.value);
        });

        openModal();
    }

    openAdd?.addEventListener('click', function () {
        resetForm();
        openModal();
    });

    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function () {
            try {
                const user = JSON.parse(this.dataset.user);
                editUser(user);
            } catch (error) {
                alert('Unable to load user information.');
                console.error(error);
            }
        });
    });

    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    @if($errors->any())
        resetForm();

        @if(old('_method') === 'PUT')
            title.textContent = 'Edit User Information';
            document.getElementById('saveUserButton').textContent = 'Update User';
            form.action = updateUrlTemplate.replace(
                '__USER__',
                @json(old('user_id'))
            );
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('user_password').required = false;
            document.getElementById('user_password_confirmation').required = false;
        @endif

        openModal();
    @endif

});
</script>
@endpush