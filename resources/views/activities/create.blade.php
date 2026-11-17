
@extends('layouts.app')

@section('content')

<div class="page-container activity-form-page">

    {{-- HEADER --}}
    <div class="activity-form-header">
        <div>
            <div class="activity-form-eyebrow">PROGRAM IMPLEMENTATION</div>
            <h1>Create Activity</h1>
            <p>Register a new activity under an existing Program, Activity, and Project.</p>
        </div>

        <a href="{{ url()->previous() }}" class="activity-form-back">
            Back
        </a>
    </div>

    {{-- VALIDATION SUMMARY --}}
    @if($errors->any())
        <div class="activity-form-error-summary">
            <strong>Please review the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('activities.store') }}" method="POST">
        @csrf

        {{-- ACTIVITY INFORMATION --}}
        <section class="activity-form-panel">

            <div class="activity-form-section-header">
                <div class="activity-form-section-number">01</div>
                <div>
                    <h2>Activity Information</h2>
                    <p>Provide the basic details of the activity.</p>
                </div>
            </div>

            <div class="activity-form-grid">

                <div class="activity-form-field">
                    <label for="pap_id">Parent PAP <span>*</span></label>

                    <select name="pap_id" id="pap_id" required>
                        <option value="">Select Parent PAP</option>

                        @foreach($paps as $pap)
                            <option value="{{ $pap->id }}"
                                {{ old('pap_id', request('pap_id')) == $pap->id ? 'selected' : '' }}>
                                {{ $pap->pap_code }} - {{ $pap->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('pap_id')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field">
                    <label for="title">Activity Title <span>*</span></label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title') }}"
                        placeholder="Enter activity title"
                        required
                    >

                    @error('title')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field activity-form-field-full">
                    <label for="description">Description</label>

                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        placeholder="Provide a brief description of the activity..."
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field activity-form-field-full">
                    <label for="location">Implementation Location</label>

                    <input
                        type="text"
                        name="location"
                        id="location"
                        value="{{ old('location') }}"
                        placeholder="Enter venue, city, or location"
                    >

                    @error('location')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- ACTIVITY SCHEDULE --}}
        <section class="activity-form-panel">

            <div class="activity-form-section-header">
                <div class="activity-form-section-number">02</div>
                <div>
                    <h2>Activity Schedule</h2>
                    <p>Set the planned implementation period.</p>
                </div>
            </div>

            <div class="activity-form-grid">

                <div class="activity-form-field">
                    <label for="start_date">Start Date</label>

                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ old('start_date') }}"
                    >

                    @error('start_date')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field">
                    <label for="end_date">End Date</label>

                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        value="{{ old('end_date') }}"
                    >

                    @error('end_date')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field">
                    <label for="quarter">Implementation Quarter</label>

                    <select name="quarter" id="quarter">
                        <option value="">Select Quarter</option>
                        <option value="1" {{ old('quarter') == '1' ? 'selected' : '' }}>Q1 - First Quarter</option>
                        <option value="2" {{ old('quarter') == '2' ? 'selected' : '' }}>Q2 - Second Quarter</option>
                        <option value="3" {{ old('quarter') == '3' ? 'selected' : '' }}>Q3 - Third Quarter</option>
                        <option value="4" {{ old('quarter') == '4' ? 'selected' : '' }}>Q4 - Fourth Quarter</option>
                    </select>

                    @error('quarter')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- ACTIVITY ASSIGNMENT --}}
        <section class="activity-form-panel">

            <div class="activity-form-section-header">
                <div class="activity-form-section-number">03</div>
                <div>
                    <h2>Activity Assignment</h2>
                    <p>Assign responsibility and set the initial implementation status.</p>
                </div>
            </div>

            <div class="activity-form-grid">

                <div class="activity-form-field">
                    <label for="focal_user_id">Focal Person</label>

                    <select name="focal_user_id" id="focal_user_id">
                        <option value="">Select Focal Person</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('focal_user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('focal_user_id')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="activity-form-field">
                    <label for="status">Activity Status <span>*</span></label>

                    <select name="status" id="status" required>
                        <option value="Planned" {{ old('status', 'Planned') == 'Planned' ? 'selected' : '' }}>Planned</option>
                        <option value="Ongoing" {{ old('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="Deferred" {{ old('status') == 'Deferred' ? 'selected' : '' }}>Deferred</option>
                    </select>

                    @error('status')
                        <small class="activity-form-field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- FORM ACTIONS --}}
        <div class="activity-form-footer">
            <a href="{{ route('activities.index') }}" class="activity-form-cancel">
                Cancel
            </a>

            <button type="submit" class="activity-form-submit">
                Create Activity
            </button>
        </div>

    </form>

</div>

@endsection
