<link rel="stylesheet" href="{{ asset('css/errors.css') }}">

<aside class="sidebar">

    <div class="brand-area">
        <img src="{{ asset('images\mfbm.png') }}" alt="BRO360 Logo">

        <div>
            <div class="brand-title">BRO360</div>
            <div class="brand-caption">BANGSAMORO REVENUE OFFICE</div>
        </div>
    </div>

    <div class="sidebar-scroll">

        {{-- OVERVIEW --}}
        <div class="nav-section">
            <div class="nav-heading">Overview</div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">▦</span>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- OPERATIONS --}}
        <div class="nav-section">
            <div class="nav-heading">Operations</div>

            <a href="{{ route('paps.index') }}" class="nav-link {{ request()->routeIs('paps.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M5.85,19.59q-0.6,0-1.02-0.42T4.41,18.15V5.85q0-0.6 0.42-1.02T5.85,4.41h4.8q0.09-0.5 0.46-0.84T12,3.22q0.53,0 0.89,0.34t0.46,0.84h4.8q0.6,0 1.02,0.42t0.42,1.02v12.3q0,0.6-0.42,1.02t-1.02,0.42H5.85Zm0-0.64h12.3q0.3,0 0.55-0.25t0.25-0.55V5.85q0-0.3-0.25-0.55T18.15,5.05H15.71v1.9H8.29V5.05H5.85q-0.3,0-0.55,0.25T5.05,5.85v12.3q0,0.3 0.25,0.55t0.55,0.25ZM12.52,5.09q0.21-0.21 0.21-0.51T12.52,4.07T12,3.86T11.49,4.07t-0.2,0.51t0.2,0.51T12,5.3t0.52-0.21Z" /></svg></span>
                <span>PAP Management</span>
            </a>

            <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M5.84,20.58q-0.61,0-1.02-0.41T4.41,19.14V6.83q0-0.61 0.41-1.02T5.85,5.4H7.91V3.1H8.65V5.4h6.8V3.1h0.64V5.4h2.06q0.61,0 1.02,0.41t0.41,1.02V19.14q0,0.61-0.41,1.02q-0.41,0.41-1.02,0.41H5.84Zm0.01-0.64h12.3q0.3,0 0.55-0.25t0.25-0.55V10.83H5.05v8.31q0,0.3 0.25,0.55t0.55,0.25Zm-0.8-9.75h13.9V6.84q0-0.3-0.25-0.55T18.15,6.04H5.85q-0.3,0-0.55,0.25T5.05,6.84v3.35Zm0,0V6.84q0-0.3 0-0.55t0-0.25q0,0 0,0.25t0,0.55v3.35Zm2.66,3.13V12.68h8.58v0.64H7.71Zm0,3.96V16.64h5.6v0.64H7.71Z" /></svg></span>
                <span>Activities</span>
            </a>

            <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M11.95,10.01h4.5V9.38h-4.5v0.64Zm0,4.61h4.5V13.99h-4.5v0.64ZM9.71,10.38Q10,10.1 10,9.7T9.71,9.01T9.02,8.73T8.34,9.01T8.06,9.7t0.28,0.68t0.68,0.28t0.69-0.28Zm0,4.6Q10,14.7 10,14.3T9.71,13.61T9.02,13.33T8.34,13.61T8.06,14.3t0.28,0.68t0.68,0.28t0.69-0.28ZM5.84,19.59q-0.61,0-1.02-0.41T4.41,18.16V5.84q0-0.61 0.41-1.02T5.84,4.41H18.16q0.61,0 1.02,0.41t0.41,1.02V18.16q0,0.61-0.41,1.02t-1.02,0.41H5.84Zm0.01-0.64h12.3q0.3,0 0.55-0.25t0.25-0.55V5.85q0-0.3-0.25-0.55T18.15,5.05H5.85q-0.3,0-0.55,0.25T5.05,5.85v12.3q0,0.3 0.25,0.55t0.55,0.25ZM5.05,5.05q0,0 0,0.22t0,0.57v12.3q0,0.35 0,0.58t0,0.22q0,0 0-0.22t0-0.58V5.85q0-0.35 0-0.57t0-0.22Z" /></svg></span>
                <span>Tasks</span>
            </a>

            <a href="{{ route('tasks.my') }}" class="nav-link {{ request()->routeIs('tasks.my') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M10.95,16.45l4.67-4.68l-0.45-0.45l-4.21,4.21L8.81,13.39L8.35,13.85l2.6,2.6Zm-4.1,4.13q-0.61,0-1.02-0.41T5.41,19.14V4.86q0-0.61 0.41-1.02T6.85,3.42h7.8l3.94,3.93V19.14q0,0.61-0.41,1.02q-0.41,0.41-1.02,0.41H6.85Zm7.49-12.9V4.06H6.85q-0.3,0-0.55,0.25T6.05,4.86V19.14q0,0.3 0.25,0.55t0.55,0.25h10.3q0.3,0 0.55-0.25t0.25-0.55V7.68H14.34ZM6.05,4.06V7.68V4.06V7.68V19.14q0,0.3 0,0.55t0,0.25q0,0 0-0.25t0-0.55V4.86q0-0.3 0-0.55t0-0.25Z" /></svg></span>
                <span>My Tasks</span>
            </a>

            <a href="{{ route('tasks.my-reviews') }}" class=" nav-link {{ request()->routeIs('tasks.my-reviews') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M8.62,19.98Q7.04,19.3 5.87,18.13T4.02,15.38T3.34,12T4.02,8.63T5.87,5.87T8.62,4.01Q10.2,3.32 12,3.32t3.38,0.68t2.76,1.86t1.86,2.76Q20.68,10.2 20.68,12t-0.68,3.38t-1.86,2.76t-2.75,1.85T12,20.66T8.62,19.98Zm9.07-2.3q2.34-2.34 2.34-5.69T17.7,6.31T12,3.96T6.31,6.3Q3.98,8.65 3.98,12t2.34,5.69T12,20.02t5.69-2.34ZM8.7,15.3Q7.34,13.94 7.34,12T8.7,8.69T12,7.32t3.31,1.36t1.37,3.31T15.31,15.3t-3.31,1.36T8.7,15.3Zm6.16-0.46q1.18-1.18 1.18-2.85T14.86,9.14T12,7.96T9.15,9.14T7.98,12t1.18,2.85T12,16.02t2.85-1.18Zm-3.32-2.39q-0.2-0.2-0.2-0.46t0.2-0.47T12,11.33t0.47,0.2T12.68,12t-0.2,0.46T12,12.66t-0.46-0.2Z" /></svg></span>
                <span>Action Center</span>
            </a>


        </div>

        {{-- MONITORING --}}
        <div class="nav-section">
            <div class="nav-heading">Monitoring</div>

            <!--<a href="#" class="nav-link" hidden>
                <span class="nav-icon">◫</span>
                <span>Monitoring</span>
            </a> -->

            <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                    <path d="M5.84,20.58q-0.61,0-1.02-0.41T4.41,19.14V6.83q0-0.61 0.41-1.02T5.85,5.4H7.91V3.1H8.65V5.4h6.8V3.1h0.64V5.4h2.06q0.61,0 1.02,0.41t0.41,1.02V19.14q0,0.61-0.41,1.02q-0.41,0.41-1.02,0.41H5.84Zm0.01-0.64h12.3q0.3,0 0.55-0.25t0.25-0.55V10.83H5.05v8.31q0,0.3 0.25,0.55t0.55,0.25Zm-0.8-9.75h13.9V6.84q0-0.3-0.25-0.55T18.15,6.04H5.85q-0.3,0-0.55,0.25T5.05,6.84v3.35Zm0,0V6.84q0-0.3 0-0.55t0-0.25q0,0 0,0.25t0,0.55v3.35Zm6.95,4q-0.28,0-0.48-0.2T11.31,13.5q0-0.28 0.2-0.48T12,12.81q0.28,0 0.48,0.2t0.21,0.48q0,0.28-0.2,0.48T12,14.19Zm-4.48-0.2q-0.21-0.2-0.21-0.48q0-0.28 0.2-0.48T8,12.81t0.48,0.2T8.69,13.5q0,0.28-0.2,0.48T8,14.19q-0.28,0-0.48-0.2ZM16,14.19q-0.28,0-0.48-0.2T15.31,13.5q0-0.28 0.2-0.48T16,12.81t0.48,0.2t0.21,0.48q0,0.28-0.2,0.48T16,14.19Zm-4,3.8q-0.28,0-0.48-0.2T11.31,17.3q0-0.28 0.2-0.48T12,16.61q0.28,0 0.48,0.2t0.21,0.48q0,0.28-0.2,0.48T12,17.99Zm-4.48-0.2q-0.21-0.2-0.21-0.48q0-0.28 0.2-0.48T8,16.61t0.48,0.2T8.69,17.3q0,0.28-0.2,0.48T8,17.99q-0.28,0-0.48-0.2ZM16,17.99q-0.28,0-0.48-0.2T15.31,17.3q0-0.28 0.2-0.48T16,16.61t0.48,0.2t0.21,0.48q0,0.28-0.2,0.48T16,17.99Z" /></svg>
                <span>Calendar</span>

            </a>

            <!--<a href="#" class="nav-link">
                <span class="nav-icon">◷</span>
                <span>Analytics</span>
            </a> -->

            <!--<a href="#" class="nav-link">
                <span class="nav-icon">▤</span>
                <span>Reports</span>
            </a> -->
        </div>

        {{-- RECORDS --}}

        {{-- RECORDS --}}
        <div class="nav-section">
            <div class="nav-heading">Records</div>


            @php
            $documentMenuActive = request()->routeIs('documents.*');
            @endphp

            <div class="document-menu">

                <div class="nav-parent {{ $documentMenuActive ? 'expanded' : '' }}" onclick="this.classList.toggle('expanded');
                  this.nextElementSibling.classList.toggle('expanded');">

                    <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                            <path d="M4.86,18.58q-0.61,0-1.02-0.41T3.43,17.14V6.86q0-0.61 0.41-1.02Q4.25,5.43 4.86,5.43H9.54l1.94,1.94h7.67q0.61,0 1.02,0.41T20.58,8.8v8.34q0,0.61-0.41,1.02q-0.41,0.41-1.02,0.41H4.86Zm0.01-0.64H19.14q0.35,0 0.58-0.23t0.22-0.57V8.8q0-0.35-0.22-0.57T19.14,8H11.23L9.29,6.06H4.86q-0.35,0-0.58,0.22T4.06,6.86V17.14q0,0.35 0.23,0.57t0.58,0.23Zm-0.8,0q0,0 0-0.23t0-0.57V6.86q0-0.35 0-0.58t0-0.22V8q0,0 0,0.22t0,0.57v8.34q0,0.35 0,0.57t0,0.23Z" /></svg></span>
                    <span>Document</span>
                    <span class="nav-arrow">›</span>
                </div>

                <div class="nav-submenu {{ $documentMenuActive ? 'expanded' : '' }}">

                    <a href="{{ route('documents.index', ['classification' => 'Incoming']) }}" class="nav-link {{ request('classification') === 'Incoming' ? 'active' : '' }}">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                                <path d="M6.44,17.58V8.94H7.08v7.54L18.13,5.43l0.45,0.45L7.53,16.94h7.55v0.64H6.44Z" /></svg></span>
                        <span class="nav-text">Incoming</span>
                    </a>

                    <a href="{{ route('documents.index', ['classification' => 'Outgoing']) }}" class="nav-link {{ request('classification') === 'Outgoing' ? 'active' : '' }}">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                                <path d="M5.89,18.58L5.43,18.11L16.48,7.06H8.94V6.43h8.64v8.64H16.94V7.51L5.89,18.58Z" /></svg></span>
                        <span class="nav-text">Outgoing</span>
                    </a>

                    <a href="{{ route('documents.index', ['classification' => 'Digital']) }}" class="nav-link {{ request('classification') === 'Digital' ? 'active' : '' }}">
                        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                                <path d="M2.13,18.85V18.21H21.88v0.64H2.13Zm2.73-1.63q-0.61,0-1.02-0.41T3.43,15.79V6.47q0-0.61 0.41-1.02T4.86,5.04H19.14q0.61,0 1.02,0.41t0.41,1.02v9.32q0,0.61-0.41,1.02t-1.02,0.41H4.86Zm0.01-0.64H19.14q0.3,0 0.55-0.25t0.25-0.55V6.47q0-0.3-0.25-0.55T19.14,5.68H4.86q-0.3,0-0.55,0.25T4.06,6.47v9.31q0,0.3 0.25,0.55t0.55,0.25Zm-0.8,0q0,0 0-0.25t0-0.55V6.47q0-0.3 0-0.55t0-0.25q0,0 0,0.25t0,0.55v9.31q0,0.3 0,0.55t0,0.25Z" /></svg></span>
                        <span class="nav-text">Digital</span>
                    </a>

                </div>

            </div>

        </div>


        {{-- ADMINISTRATION --}}
        <div class="nav-section">
            <div class="nav-heading">Administration</div>

            <a href="{{ route('fiscal-years.index') }}" class="nav-link {{ request()->routeIs('fiscal-years.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M5.84,20.58q-0.61,0-1.02-0.41T4.41,19.14V6.83q0-0.61 0.41-1.02T5.85,5.4H7.91V3.1H8.65V5.4h6.8V3.1h0.64V5.4h2.06q0.61,0 1.02,0.41t0.41,1.02v4.98H18.95V10.83H5.05v8.31q0,0.3 0.25,0.55t0.55,0.25H11.8v0.64H5.84ZM5.05,10.19h13.9V6.84q0-0.3-0.25-0.55T18.15,6.04H5.85q-0.3,0-0.55,0.25T5.05,6.84v3.35Zm0,0V6.84q0-0.35 0-0.57t0-0.23q0,0 0,0.23t0,0.57v3.35Zm9.19,10.39V18.66l5.26-5.24q0.11-0.11 0.24-0.15t0.24-0.04t0.25,0.05t0.23,0.14l0.93,0.95q0.08,0.12 0.13,0.24q0.05,0.12 0.05,0.24t-0.05,0.24t-0.14,0.23l-5.24,5.25H14.24Zm6.68-5.73L19.99,13.9l0.93,0.95Zm-6,5.05h0.94l3.63-3.63L19.02,15.8l-0.45-0.5l-3.66,3.65V19.9Zm4.1-4.1l-0.45-0.5l0.93,0.97L19.01,15.8Z" /></svg></span>
                <span>Fiscal Year Management</span>
            </a>

            <a href="{{ route('organization.index') }}" class="nav-link {{ request()->routeIs('organization.*') ? 'active' : '' }}">

                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M2.93,15.94V3.85H9.78V6.72h7.29v9.22H2.93Zm0.6-0.6H9.17V13.06H3.53v2.27Zm0-2.87H9.17V10.19H3.53v2.27Zm0-2.87H9.17V7.32H3.53V9.59Zm0-2.87H9.17V4.45H3.53V6.72Zm6.24,8.62h6.69V7.32H9.78v8.01Zm1.76-5.14V9.59h2.86v0.6H11.53Zm0,2.87v-0.6h2.86v0.6H11.53Z" /></svg></span>
                <span>Organizational Management</span>

            </a>

            <!--<a href="#" class="nav-link">
                <span class="nav-icon">₱</span>
                <span>Budget Setup</span>

            </a>



            <a href="#" class="nav-link">
                <span class="nav-icon">▦</span>
                <span>Catalogs</span>
            </a> -->
        </div>

        {{-- SYSTEM --}}
        <div class="nav-section">
            <div class="nav-heading">System</div>

            <a href="{{ route('workflow-templates.index') }}" class="nav-link {{ request()->routeIs('workflow-templates.*') ? 'active' : '' }}">

                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M7.4,16.5q-0.86,0.71-1.77,0.63T4.06,16.51T3.18,15.07t0.33-1.82l1.95-3.22Q4.87,9.59 4.51,8.9T4.15,7.43q0-1.37 0.95-2.32T7.42,4.15T9.74,5.1t0.95,2.32T9.74,9.74T7.42,10.69q-0.3,0-0.59-0.05T6.28,10.48L4.31,13.74q-0.36,0.59-0.22,1.15t0.57,0.92t1.01,0.4T6.8,15.8l9.82-8.31q0.82-0.71 1.72-0.64t1.57,0.62t0.91,1.44T20.5,10.75l-1.96,3.24q0.59,0.45 0.95,1.12t0.36,1.48q0,1.37-0.96,2.31t-2.32,0.95t-2.31-0.96t-0.95-2.32q0-1.35 0.95-2.31t2.3-0.96q0.3,0 0.6,0.06t0.57,0.16l1.96-3.26q0.35-0.59 0.22-1.15T19.34,8.19T18.33,7.78t-1.12,0.4L7.4,16.5ZM9.09,9.09Q9.77,8.4 9.77,7.42T9.09,5.76T7.42,5.08T5.76,5.76T5.08,7.42T5.76,9.09T7.42,9.77T9.09,9.09Zm9.14,9.15q0.7-0.68 0.7-1.66t-0.7-1.67t-1.67-0.68T14.9,14.91t-0.67,1.66t0.67,1.66t1.65,0.68t1.67-0.68ZM7.42,7.42Zm9.15,9.15Z" /></svg></span>
                <span>Workflow Templates</span>
            </a>

            <!--<a href="#" class="nav-link">
                <span class="nav-icon">☷</span>
                <span>Logbook Configuration</span>
            </a> -->

            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M7.94,10.55Q7.09,9.7 7.09,8.48T7.94,6.41t2.07-0.85t2.07,0.85t0.85,2.07t-0.85,2.07T10.02,11.4T7.94,10.55ZM3.15,18.42V16.86q0-0.68 0.34-1.16t0.96-0.78Q5.73,14.31 7,13.95t3.01-0.36q0.13,0 0.22,0t0.22,0.01q-0.09,0.25-0.15,0.47t-0.12,0.44H10.02q-1.57,0-2.79,0.31T4.9,15.73q-0.47,0.25-0.64,0.51T4.08,16.86v0.63h6.24q0.06,0.23 0.16,0.47t0.21,0.45H3.15Zm13.03,0.47l-0.13-1.12q-0.41-0.09-0.79-0.3t-0.67-0.53l-1.07,0.42L13.14,16.7l0.9-0.66q-0.17-0.43-0.17-0.9t0.17-0.9l-0.88-0.72l0.38-0.64l1.05,0.45q0.28-0.33 0.66-0.53t0.8-0.28l0.13-1.12h0.77l0.11,1.12q0.43,0.09 0.82,0.28t0.66,0.53l1.03-0.46l0.38,0.65l-0.87,0.72q0.17,0.43 0.17,0.89t-0.17,0.89l0.89,0.66l-0.38,0.64l-1.05-0.42q-0.29,0.32-0.67,0.53t-0.81,0.3l-0.11,1.12H16.18Zm1.63-2.48q0.52-0.52 0.52-1.27t-0.52-1.27t-1.26-0.52t-1.27,0.52t-0.52,1.27t0.52,1.27t1.27,0.52t1.26-0.52ZM11.43,9.89q0.59-0.59 0.59-1.41T11.43,7.06T10.02,6.48T8.6,7.06T8.02,8.48T8.6,9.89t1.41,0.59t1.41-0.59ZM10.02,8.48Zm0.3,9.02Z" /></svg></span>
                <span>User Management</span>
            </a>

            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M14.63,20.48h5.71V19.79q-0.54-0.62-1.28-0.98t-1.58-0.35T15.9,18.81t-1.27,0.98v0.69Zm3.74-3.21q0.36-0.36 0.36-0.88t-0.37-0.89t-0.89-0.37t-0.88,0.37t-0.36,0.89t0.36,0.89t0.88,0.36t0.89-0.36ZM12,12ZM11.94,9.58q-1.02,0-1.72,0.71t-0.7,1.72q0,0.79 0.42,1.39t1.11,0.86q0.01-0.23 0.06-0.47t0.16-0.43q-0.42-0.17-0.62-0.55T10.44,12q0-0.63 0.44-1.06t1.06-0.44q0.45,0 0.81,0.25t0.55,0.64q0.16-0.08 0.33-0.12t0.34-0.04h0.22q-0.23-0.72-0.85-1.18t-1.4-0.46ZM10.22,20.85L9.86,18q-0.32-0.08-0.89-0.39T7.82,16.87l-2.61,1.1L3.42,14.85L5.7,13.14q-0.05-0.27-0.08-0.54T5.6,12.02q0-0.24 0.03-0.52T5.7,10.83L3.42,9.13L5.21,6.05l2.62,1.1Q8.26,6.79 8.79,6.49T9.84,6l0.38-2.86h3.56L14.15,6q0.58,0.2 1.03,0.47t0.91,0.68l2.71-1.1l1.79,3.07l-2.43,1.81q0.01,0.06 0.02,0.13t0.03,0.09H17.24q-0.02-0.15-0.05-0.3q-0.02-0.15-0.07-0.3l2.24-1.7L18.4,7.18L15.82,8.27Q15.4,7.8 14.66,7.36T13.3,6.79L13,4.06H11.01l-0.3,2.7Q9.93,6.94 9.33,7.27T8.09,8.24L5.61,7.18L4.62,8.87l2.17,1.61q-0.13,0.35-0.18,0.74T6.56,12.01q0,0.38 0.04,0.74t0.15,0.75L4.62,15.12L5.61,16.8l2.46-1.05q0.6,0.59 1.35,0.98q0.75,0.39 1.59,0.58v3.54H10.22Zm4.33,0.94q-0.52,0-0.88-0.36t-0.36-0.88V14.71q0-0.52 0.36-0.88t0.88-0.36H20.4q0.52,0 0.88,0.37t0.36,0.89v5.83q0,0.52-0.37,0.88t-0.89,0.36H14.56Z" /></svg></span>
                <span>Role Management</span>
            </a>

            <a href="{{ route('fund-sources.index') }}" class="nav-link {{ request()->routeIs('fund-sources.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M11.85,15.9h0.53V15.12q0.7-0.1 1.19-0.55t0.49-1.18q0-0.62-0.5-1.04T12.4,11.66V9.63q0.29,0.06 0.5,0.24t0.3,0.42l0.71-0.3q-0.19-0.45-0.6-0.74T12.4,8.88V8.1H11.85V8.87q-0.7,0.07-1.19,0.48t-0.49,1.1q0,0.64 0.51,1.08t1.16,0.71v2.11q-0.48-0.09-0.78-0.38t-0.42-0.69l-0.7,0.3q0.2,0.63 0.7,1.04t1.21,0.51V15.9Zm0.56-1.56v-1.9q0.35,0.16 0.62,0.39t0.27,0.6q0,0.43-0.25,0.63T12.4,14.35Zm-0.56-2.92q-0.33-0.16-0.62-0.39t-0.29-0.59q0-0.4 0.28-0.59t0.63-0.24v1.8ZM8,17.96q-2.49,0-4.22-1.73T2.04,12T3.78,7.77T8,6.03h8q2.49,0 4.23,1.74T21.97,12t-1.74,4.23T16,17.96H8Zm0-0.92h8q2.09,0 3.57-1.47T21.04,12T19.57,8.43T16,6.96H8q-2.09,0-3.56,1.47T2.97,12t1.47,3.56T8,17.03ZM12,12Z" /></svg></span>
                <span>Fund Source Management</span>
            </a>


            <a href="{{ route('programs.index') }}" class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3">
                        <path d="M12,12Zm-1.77,8.85L9.86,18q-0.32-0.08-0.89-0.39T7.82,16.87l-2.61,1.1L3.42,14.85L5.7,13.14q-0.05-0.27-0.08-0.54T5.6,12.02q0-0.24 0.03-0.52T5.7,10.83L3.42,9.13L5.21,6.05l2.62,1.1Q8.26,6.79 8.79,6.49T9.84,6l0.38-2.86h3.56L14.15,6q0.58,0.2 1.03,0.47t0.91,0.68l2.71-1.1l1.79,3.07L18.7,10.54q-0.28-0.1-0.59-0.17t-0.6-0.12l1.85-1.39L18.4,7.18L15.82,8.27Q15.4,7.8 14.66,7.36T13.3,6.79L13,4.06H11.01l-0.3,2.7Q9.93,6.94 9.33,7.27T8.09,8.24L5.61,7.18L4.62,8.87l2.17,1.61q-0.13,0.35-0.18,0.74t-0.05,0.8q0,0.38 0.04,0.74t0.15,0.75L4.62,15.12L5.61,16.8l2.46-1.05q0.51,0.51 1.04,0.84t1.14,0.53q0.07,1.07 0.46,2.02t1.05,1.71H10.22Zm0.54-6.72q0.09-0.23 0.2-0.44t0.23-0.4q-0.34-0.19-0.55-0.53T10.44,12q0-0.63 0.44-1.06t1.06-0.44q0.4,0 0.74,0.2t0.55,0.53q0.19-0.13 0.4-0.23t0.42-0.2q-0.31-0.56-0.88-0.89T11.94,9.58q-1.02,0-1.72,0.7T9.52,12q0,0.7 0.34,1.26t0.91,0.86Zm5.93,5.06l2.51-2.51l-2.51-2.5l-0.6,0.61l1.49,1.47h-3.4v0.85h3.38l-1.48,1.47l0.61,0.61Zm-2.94,0.44q-1.21-1.22-1.21-2.94t1.21-2.94t2.94-1.22t2.95,1.22t1.22,2.94t-1.22,2.94t-2.95,1.22t-2.94-1.22Z" /></svg></span>
                <span>Program Management</span>
            </a>

            <a href="{{ route('task-types.index') }}" class="nav-link {{ request()->routeIs('task-types.*') ? 'active' : '' }}">
                <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px" fill="#e3e3e3"><path d="M14.54,20.85q-0.65,0-1.09-0.45T13,19.31V15.54q0-0.65 0.45-1.09T14.54,14h5.77q0.65,0 1.09,0.45t0.45,1.09v3.77q0,0.65-0.45,1.09t-1.09,0.45H14.54Zm0-0.92h5.77q0.27,0 0.44-0.17t0.17-0.44V15.54q0-0.27-0.17-0.44t-0.44-0.17H14.54q-0.27,0-0.44,0.17t-0.17,0.44v3.77q0,0.27 0.17,0.44t0.44,0.17Zm-3.43-5.65q-0.7-0.26-1.15-0.87T9.52,12q0-1.02 0.7-1.72t1.72-0.7q0.97,0 1.66,0.67t0.76,1.62H14.11q-0.19,0.02-0.34,0.04t-0.33,0.07q0-0.01 0-0.01t0-0.01q-0.02-0.61-0.45-1.03T11.93,10.5t-1.05,0.44t-0.44,1.06q0,0.51 0.29,0.9t0.73,0.54q-0.13,0.2-0.21,0.41t-0.13,0.44ZM11.94,12Zm-1.72,8.85L9.86,18q-0.49-0.15-1.07-0.46T7.82,16.87l-2.61,1.1L3.42,14.85L5.7,13.14q-0.05-0.27-0.08-0.54T5.6,12.03q0-0.25 0.03-0.53T5.7,10.83L3.42,9.13L5.21,6.05l2.62,1.1Q8.26,6.79 8.79,6.49T9.84,6l0.38-2.86h3.56L14.15,6q0.58,0.2 1.03,0.47t0.91,0.68l2.71-1.1l1.79,3.07l-2.4,1.79q0.07,0.31 0.09,0.56T18.29,12H17.33q-0-0.42-0.05-0.74t-0.15-0.69l2.24-1.7L18.4,7.18L15.82,8.27Q15.4,7.8 14.66,7.36T13.3,6.79L13,4.06H11.01l-0.3,2.7Q9.93,6.94 9.33,7.27T8.09,8.24L5.61,7.18L4.62,8.87l2.17,1.61q-0.13,0.35-0.18,0.74T6.56,12q0,0.39 0.04,0.75t0.15,0.74L4.62,15.12L5.61,16.8l2.46-1.05q0.6,0.59 1.35,0.98q0.75,0.39 1.59,0.58v3.54H10.22ZM11.94,12Zm5.48,5.42Z"/></svg></span>
                <span>Task Types</span>
            </a>



            <!--<a href="#" class="nav-link">
                <span class="nav-icon">◷</span>
                <span>Audit Logs</span>
            </a> -->
        </div>

    </div>

    <div class="sidebar-footer">
        <strong>BRO360</strong> Internal Management System
        <br>
        <a href="/privacy-policy">Privacy Policy</a>
    </div>

</aside>
