@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Google Drive Management</h2>

    <p>
        Manage your Google Drive connection and BRO360 storage folder.
    </p>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($connection)

        <div class="card">
            <div class="card-body">

                <h4>Google Account</h4>

                <p>
                    <strong>Email:</strong>
                    {{ $connection->google_email }}
                </p>

                <p>
                    <strong>Connected:</strong>
                    {{ $connection->connected_at?->format('F d, Y h:i A') ?? 'Not recorded' }}
                </p>

                <hr>

                <h4>BRO360 Storage Folder</h4>

                @if ($connection->root_folder_id)

                    <p>Root folder has been created.</p>

                    <p>
                        <strong>Folder ID:</strong>
                        {{ $connection->root_folder_id }}
                    </p>

                    <a
                        href="https://drive.google.com/drive/folders/{{ $connection->root_folder_id }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open BRO360 Folder
                    </a>

                @else

                    <p>The BRO360 root folder has not yet been created.</p>

                    <form
                        method="POST"
                        action="{{ route('google.drive.folders.root.create') }}"
                    >
                        @csrf

                        <button type="submit">
                            Create BRO360 Google Drive Folder
                        </button>
                    </form>

                @endif

            </div>
        </div>

    @else

        <div class="alert alert-warning">
            Google Drive is not connected to your account.

            <a href="{{ route('google.drive.connect') }}">
                Connect Google Drive
            </a>
        </div>

    @endif

</div>

@endsection