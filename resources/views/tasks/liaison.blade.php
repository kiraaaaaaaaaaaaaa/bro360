<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRO360 - Liaison Officer Portal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6f4;
            font-family: Inter, Arial, sans-serif;
            color: #24352b;
        }

        .liaison-container {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 18px;
        }

        .liaison-header {
            background: linear-gradient(135deg, #102c20, #1c5038);
            color: #fff;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .liaison-brand {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #d8b65c;
            margin-bottom: 12px;
        }

        .liaison-header h1 {
            margin: 0 0 7px;
            font-size: 23px;
            font-weight: 700;
        }

        .liaison-header p {
            margin: 0;
            color: #dce8df;
            font-size: 13px;
        }

        .liaison-card {
            background: #fff;
            border: 1px solid #e5ebe6;
            border-radius: 13px;
            padding: 22px;
            margin-bottom: 18px;
            box-shadow: 0 3px 12px rgba(16, 44, 32, .04);
        }

        .liaison-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 15px;
            margin-bottom: 17px;
            border-bottom: 1px solid #edf1ed;
        }

        .liaison-card-header h2 {
            margin: 0;
            font-size: 16px;
            color: #153b2a;
        }

        .liaison-count {
            background: #edf4ef;
            color: #286747;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .liaison-task-title {
            font-size: 19px;
            line-height: 1.4;
            color: #153b2a;
            margin: 0 0 8px;
        }

        .liaison-task-pap {
            color: #829087;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .liaison-destination {
            padding: 18px 0;
            border-bottom: 1px solid #edf1ed;
        }

        .liaison-destination:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .destination-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .destination-heading h3 {
            margin: 0;
            font-size: 15px;
            color: #153b2a;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #f1f4f2;
            color: #52645a;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #46594d;
            margin-bottom: 7px;
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 12px;
            border: 1px solid #dce4dd;
            border-radius: 8px;
            background: #fff;
            color: #24352b;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #286747;
            box-shadow: 0 0 0 3px rgba(40, 103, 71, .09);
        }

        .form-group textarea {
            min-height: 95px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            min-height: auto;
            padding: 10px;
            font-size: 12px;
        }

        .liaison-submit {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 8px;
            background: #153b2a;
            color: white;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s ease;
        }

        .liaison-submit:hover {
            background: #286747;
        }

        .liaison-alert {
            padding: 14px 16px;
            border-radius: 9px;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.6;
        }

        .liaison-alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #c8e6c9;
        }

        .liaison-alert-error {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ffcdd2;
        }

        .liaison-empty {
            padding: 25px 10px;
            text-align: center;
            color: #829087;
            font-size: 13px;
        }

        .liaison-footer {
            text-align: center;
            padding: 20px 0 10px;
            color: #829087;
            font-size: 11px;
        }

        @media (max-width: 600px) {
            .liaison-container {
                padding: 14px 12px;
            }

            .liaison-header {
                padding: 21px 18px;
            }

            .liaison-header h1 {
                font-size: 20px;
            }

            .liaison-card {
                padding: 17px;
            }

            .liaison-task-title {
                font-size: 17px;
            }

            .destination-heading {
                flex-direction: column;
                gap: 8px;
            }
        }

        .liaison-destination {
            padding: 18px;
            border-bottom: 1px solid #edf1ed;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .liaison-destination.current-route {
            background: #f1f7f2;
            border: 2px solid #286747;
            border-left: 6px solid #d8b65c;
            padding: 16px;
        }

        .liaison-destination.inactive-route {
            background: #fafbfa;
            border: 1px solid #edf1ed;
        }

        .current-route-label {
            display: inline-block;
            margin-bottom: 7px;
            padding: 5px 9px;
            background: #153b2a;
            color: #fff;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .7px;
        }

        .destination-heading>div {
            min-width: 0;
        }

        .camera-file-input {
            position: fixed;
            left: -10000px;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .camera-button {
            display: block;
            width: 100%;
            min-height: 52px;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: #153b2a;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .camera-button:active {
            background: #286747;
        }

        .photo-preview {
            margin-top: 12px;
            padding: 10px;
            background: #f1f7f2;
            border: 1px solid #dce8df;
            border-radius: 9px;
        }

        .photo-preview img {
            display: block;
            width: 100%;
            max-height: 260px;
            object-fit: contain;
            border-radius: 7px;
        }

        .photo-preview span {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

    </style>
</head>

<body>

    <div class="liaison-container">

        {{-- HEADER --}}
        <header class="liaison-header">
            <div class="liaison-brand">BRO360</div>
            <h1>External Document Routing</h1>
            <p>Liaison Officer Portal</p>
        </header>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
        <div class="liaison-alert liaison-alert-success">
            {{ session('success') }}
        </div>
        @endif

        {{-- VALIDATION ERRORS --}}
        @if($errors->any())
        <div class="liaison-alert liaison-alert-error">
            <strong>Please check the following:</strong>
            <ul style="margin-bottom:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- TASK INFORMATION --}}
        <section class="liaison-card">
            <div class="liaison-card-header">
                <h2>Task Information</h2>
            </div>

            <h2 class="liaison-task-title">{{ $task->title }}</h2>
            <p class="liaison-task-pap">
                {{ $task->pap->name ?? 'No PAP assigned' }}
            </p>
        </section>

        {{-- EXTERNAL ROUTING --}}
        <section class="liaison-card">

            <div class="liaison-card-header">
                <h2>Routing Destinations</h2>
                <span class="liaison-count">
                    {{ $task->routings->count() }} Destinations
                </span>
            </div>

            @forelse($task->routings as $routing)

            @php
            $isCurrentRoute = in_array($routing->status, [
            'Pending',
            'Dispatched',
            'On Hold',
            'For Revision'
            ]);
            @endphp

            @php
            $destinationName = $routing->destination_type === 'division'
            ? ($routing->destinationDivision->name ?? 'Unknown Division')
            : ($routing->destinationOffice->name ?? 'Unknown Office');
            @endphp

            <div class="liaison-destination {{ $isCurrentRoute ? 'current-route' : 'inactive-route' }}">

                <div class="destination-heading">
                    @if($isCurrentRoute)
                    <span class="current-route-label">CURRENT ROUTE</span>
                    @endif
                    <h3>{{ $destinationName }}</h3>

                    <span class="status-badge">
                        {{ $routing->status }}
                    </span>
                </div>

                <form action="{{ route('liaison.routing.update', [$task->qr_review_token, $routing->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Update Routing Status</label>

                        <select name="status" required>
                            @foreach([
                            'Pending',
                            'Dispatched',
                            'Received',
                            'Completed',
                            'Returned',
                            'For Revision',
                            'On Hold',
                            'Cancelled'
                            ] as $status)
                            <option value="{{ $status }}" @selected($routing->status === $status)>
                                {{ $status }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="receiving_name">Receiving Name</label>
                        <input type="text" name="receiving_name" id="receiving_name" value="{{ old('receiving_name', $routing->receiving_name) }}" placeholder="Enter the full name of the person who received the document" required>
                    </div>


                    <div class="form-group">
                        <label>Photo Proof</label>

                        <input type="file" name="photo" id="photoProof" accept="image/*" capture="environment" class="camera-file-input">

                        <button type="button" class="camera-button" onclick="document.getElementById('photoProof').click()">
                            Take Photo
                        </button>

                        <div class="photo-preview" id="photoPreview" style="display:none;">
                            <img id="previewImage" src="" alt="Photo proof preview">
                            <span id="photoName"></span>
                        </div>
                    </div>




                    <div class="form-group">
                        <label>Remarks</label>

                        <textarea name="remarks" rows="3" maxlength="2000" placeholder="Enter routing remarks..."></textarea>
                    </div>

                    <button type="submit" class="liaison-submit">
                        Submit Routing Update
                    </button>

                </form>

            </div>

            @empty
            <div class="liaison-empty">
                No external routing destinations found.
            </div>
            @endforelse

        </section>

        <footer class="liaison-footer">
            2026 © BRO360 | Bangsamoro Revenue Office
        </footer>

    </div>


    <script>
        document.getElementById('photoProof').addEventListener('change', function() {
            const file = this.files[0];

            if (!file) return;

            const preview = document.getElementById('photoPreview');
            const image = document.getElementById('previewImage');
            const name = document.getElementById('photoName');

            image.src = URL.createObjectURL(file);
            name.textContent = file.name;
            preview.style.display = 'block';
        });

    </script>


</body>
</html>
