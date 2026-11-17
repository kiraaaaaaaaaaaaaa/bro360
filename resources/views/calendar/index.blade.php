
@extends('layouts.app')

@section('content')

<div class="page-container calendar-page">

    {{-- PAGE HEADER --}}
    <div class="calendar-page-header">
        <div>
            <div class="calendar-eyebrow">PROGRAM IMPLEMENTATION</div>
            <h1>Calendar</h1>
            <p>View scheduled PAP activities and tasks across the office.</p>
        </div>
    </div>

    
{{-- CALENDAR LEGEND AND FILTERS --}}
<div class="calendar-toolbar-options">

    <div class="calendar-legend">

        <div class="calendar-legend-item">
            <span class="calendar-legend-color activity-color"></span>
            <span>PAP Activities</span>
        </div>

        <div class="calendar-legend-item">
            <span class="calendar-legend-color task-color"></span>
            <span>Tasks</span>
        </div>

    </div>

    <label class="calendar-task-toggle">
        <input type="checkbox" id="toggle-tasks" checked>
        <span class="calendar-toggle-slider"></span>
        <span class="calendar-toggle-label">Show Tasks</span>
    </label>

</div>


    {{-- CALENDAR --}}
    <section class="calendar-content-card">
        <div id="bro360-calendar"></div>
    </section>

</div>

@endsection

@push('styles')
<link
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css"
    rel="stylesheet"
>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarElement = document.getElementById('bro360-calendar');

    const calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        height: 'auto',

        events: {
            url: '{{ route("calendar.events") }}',
            method: 'GET',
            failure: function () {
                alert('Unable to load calendar events.');
            }
        },

        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },

        eventDisplay: 'block',
        dayMaxEvents: true,
        navLinks: true,
        nowIndicator: true
    });

    calendar.render();

});


document.addEventListener('DOMContentLoaded', function () {

    const calendarElement = document.getElementById('bro360-calendar');
    const taskToggle = document.getElementById('toggle-tasks');

    let showTasks = true;

    const calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        height: 'auto',

        events: {
            url: '{{ route("calendar.events") }}',
            method: 'GET',

            failure: function () {
                alert('Unable to load calendar events.');
            }
        },

        eventSourceSuccess: function (events) {
            return events.filter(function (event) {
                return showTasks || !event.id.startsWith('task-');
            });
        },

        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },

        eventDisplay: 'block',
        dayMaxEvents: true,
        navLinks: true,
        nowIndicator: true
    });

    calendar.render();

    taskToggle.addEventListener('change', function () {
        showTasks = this.checked;
        calendar.refetchEvents();
    });

});

</script>
@endpush
