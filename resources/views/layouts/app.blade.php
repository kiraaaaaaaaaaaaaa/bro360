<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') | BRO360</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/mfbm.png') }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
       @stack('styles')
</head>

<body>

    <div class="app-shell">

        {{-- SIDEBAR --}}
        @include('components.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <main class="main-area">

            {{-- TOP NAVIGATION --}}
            <header class="topbar">

                <div class="topbar-left">

                    <button class="icon-button mobile-menu" onclick="toggleSidebar()" aria-label="Toggle navigation">
                        ☰
                    </button>

                    <div>
                        <div class="page-heading">
                            @yield('page-title', 'Dashboard')
                        </div>

                        <div class="page-subheading">
                            @yield('page-description', 'Welcome to BRO360')
                        </div>
                    </div>

                </div>

                <div class="topbar-right">

                    {{-- NOTIFICATION DROPDOWN --}}
                    <div class="notification-wrapper">

                        @php
                        $currentUser = auth()->user();

                        $unreadNotificationCount = $currentUser
                        ->unreadNotifications()
                        ->count();

                        $latestNotifications = $currentUser
                        ->notifications()
                        ->latest()
                        ->take(5)
                        ->get();
                        @endphp

                        <button type="button" class="icon-button notification-toggle" id="notificationToggle" title="Notifications" onclick="toggleNotifications()">

                            <i class="fa-regular fa-bell"></i>

                            @if($unreadNotificationCount > 0)
                            <span class="notification-badge">
                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                            </span>
                            @endif

                        </button>

                        <div class="notification-dropdown" id="notificationDropdown">

                            <div class="notification-dropdown-header">
    <strong>Notifications</strong>

    @if($unreadNotificationCount > 0)
        <form method="POST"
              action="{{ route('notifications.markAllAsRead') }}">
            @csrf
            @method('PATCH')

            <button type="submit" class="notification-mark-all">
                Mark all as read
            </button>
        </form>
    @else
        <span>All caught up</span>
    @endif
</div>

                            <div class="notification-dropdown-list">

                                @forelse($latestNotifications as $notification)

                                @php
                                $data = $notification->data ?? [];
                                @endphp

                                <a href="{{ route('notifications.open', $notification->id) }}" class="notification-item {{ $notification->read_at ? '' : 'unread' }}">

                                    <div class="notification-item-title">
                                        {{ $data['task_title'] ?? 'System Notification' }}
                                    </div>

                                    <div class="notification-item-message">
                                        {{ $data['message'] ?? 'You have a new notification.' }}
                                    </div>

                                    <div class="notification-item-date">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>

                                </a>

                                @empty

                                <div class="notification-empty">
                                    No notifications yet.
                                </div>

                                @endforelse

                            </div>

                            <a href="{{ route('notifications.index') }}" class="notification-view-all">
                                View All Notifications
                            </a>

                        </div>

                    </div>

                    <div class="user-profile">

                        <div class="avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>

                        <div class="user-details">
                            <div class="user-name">
                                {{ auth()->user()->name ?? 'User' }}
                            </div>

                            <div class="user-role">
                                BRO360 User
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="icon-button" title="Logout">
                                ↪
                            </button>
                        </form>

                    </div>
                </div>
            </header>

            {{-- PAGE CONTENT --}}
            <section class="content-area">
                @yield('content')
            </section>

        </main>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');

            dropdown.classList.toggle('show');
        }

        // Close notification dropdown when clicking outside.
        document.addEventListener('click', function(event) {
            const wrapper = document.querySelector('.notification-wrapper');
            const dropdown = document.getElementById('notificationDropdown');

            if (wrapper && !wrapper.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

    </script>



    @stack('scripts')

</body>
</html>
