@extends('layouts.app')

@section('content')

<div class="notification-page">

    <div class="notification-header">
        <div>
            <div class="notification-breadcrumb">
                Dashboard / Notifications
            </div>

            <h1>Notification Center</h1>
            <p>View and manage your task and workflow notifications.</p>
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit" class="notification-primary-btn">
                    Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <div class="notification-summary">
        <div class="notification-summary-card">
            <span>Total Notifications</span>
            <strong>{{ $notifications->total() }}</strong>
        </div>

        <div class="notification-summary-card">
            <span>Unread Notifications</span>
            <strong>{{ $unreadCount }}</strong>
        </div>
    </div>

    <div class="notification-list">

        <div class="notification-list-header">
            <h2>Recent Notifications</h2>
        </div>

        @forelse($notifications as $notification)

            @php
                $data = $notification->data ?? [];

                $notificationTitle = match($data['type'] ?? '') {
    'task_assigned' => 'New Task Assigned',
    'task_reassigned' => 'Task Reassigned to You',
    'task_returned' => 'Task Returned for Revision',
    'task_submitted_for_review' => 'Task Submitted for Review',
    default => 'System Notification',
};

                $notificationMessage = $data['message'] ?? 'You have a new notification.';
            @endphp

            <div class="notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}">

                <div class="notification-item-content">

                    <div class="notification-item-top">
                        <h3>{{ $notificationTitle }}</h3>

                        @if(!$notification->read_at)
                            <span class="notification-unread-badge">
                                Unread
                            </span>
                        @endif
                    </div>

                    <p>{{ $notificationMessage }}</p>

                    @if(!empty($data['task_title']))
                        <p>
                            <strong>Task:</strong>
                            {{ $data['task_title'] }}
                        </p>
                    @endif

                    @if(!empty($data['remarks']))
                        <p>
                            <strong>Remarks:</strong>
                            {{ $data['remarks'] }}
                        </p>
                    @endif

                    <div class="notification-item-meta">
                        <span>{{ $data['type'] ?? 'General' }}</span>
                        <span>{{ $notification->created_at?->format('M d, Y h:i A') }}</span>
                    </div>

                    @if(!empty($data['url']))
                        <a href="{{ $data['url'] }}" class="notification-read-btn">
                            View Task
                        </a>
                    @endif

                </div>

                @if(!$notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="notification-read-btn">
                            Mark as Read
                        </button>
                    </form>
                @endif

            </div>

        @empty

            <div class="notification-empty">
                <h3>No Notifications Yet</h3>
                <p>Your notifications will appear here when there are updates.</p>
            </div>

        @endforelse

        <div class="notification-pagination">
            {{ $notifications->links() }}
        </div>

    </div>

</div>

@endsection