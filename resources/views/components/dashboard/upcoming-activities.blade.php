<div class="panel upcoming-activities-panel">

    <div class="panel-header">
        <h3>Upcoming Activities</h3>
        <a href="{{ route('calendar.index') }}" class="panel-link">
            View Calendar
        </a>
    </div>

    <div class="panel-body upcoming-activities-body">

        @forelse($activities as $activity)

        @php
        $isOngoing = $activity->start_date
        && $activity->end_date
        && today()->betweenIncluded(
        $activity->start_date,
        $activity->end_date
        );
        @endphp

        <a href="{{ route('activities.show', $activity->id) }}" class="upcoming-activity-item">

            <div class="activity-date-block
    {{ $activity->start_date?->isToday() ? 'activity-today' : '' }}">
                <span class="activity-month">
                    {{ $activity->start_date?->format('M') }}
                </span>

                <strong class="activity-day">
                    {{ $activity->start_date?->format('d') }}
                </strong>

            </div>


            <div class="activity-information">



                <strong class="activity-title">
                    {{ $activity->title }}
                </strong>
                
                @if($activity->location)
                <span class="activity-location">
                    <icon name="map-pin" />
                    {{ $activity->location }}
                </span>
                @endif

                <span class="activity-duration">
                    {{ $activity->start_date?->format('M d, Y') }}
                    –
                    {{ $activity->end_date?->format('M d, Y') }}
                </span>

            </div>


            <span class="activity-status
    {{ $isOngoing ? 'status-ongoing' : 'status-upcoming' }}">

                <span class="activity-status-dot"></span>

                {{ $isOngoing ? 'Ongoing' : 'Upcoming' }}

            </span>

        </a>

        @empty

        <p class="empty-state">
            No upcoming activities assigned to you.
        </p>

        @endforelse

    </div>

</div>
