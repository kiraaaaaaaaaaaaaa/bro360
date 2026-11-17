@extends('layouts.app')

@php
$classification = request('classification', 'Incoming');

$pageTitles = [
'Incoming' => 'Incoming Documents',
'Outgoing' => 'Outgoing Documents',
'Digital' => 'Digital Files',
];

$pageTitle = $pageTitles[$classification] ?? 'Documents';
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-description', 'BRO360 Document Management Registry')

@section('content')

<div class="document-page">

    {{-- PAGE HEADER --}}
    <div class="document-header">
        <div>
            <h1>{{ $pageTitle }}</h1>
            <p>
                @if($classification === 'Incoming')
                Register and manage official correspondence received by BRO.
                @elseif($classification === 'Outgoing')
                Register and manage official correspondence released by BRO.
                @else
                Manage scanned digital reference documents.
                @endif
            </p>
        </div>

        <div class="document-filter-actions">
            <button type="submit" class="btn-primary" onclick="openDocumentModal()">
                Upload Document
            </button>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="document-filter-panel">

        <form method="GET" action="{{ route('documents.index') }}" class="document-filter-form">

            <input type="hidden" name="classification" value="{{ $classification }}">

            <div class="document-filter-group">
                <label for="search">Search Documents</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search document number, subject, or filename...">
            </div>

            <div class="document-filter-group">
                <label for="category_id">Document Type</label>
                <select name="category_id" id="category_id">
                    <option value="">All Document Types</option>

                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="document-filter-actions">
                <button type="submit" class="btn-primary">
                    Apply Filters
                </button>

                <a href="{{ route('documents.index', ['classification' => $classification]) }}" class="btn-secondary">
                    Clear
                </a>
            </div>

        </form>

    </div>

    {{-- DOCUMENT TABLE --}}
    <div class="document-table-panel">

        <div class="document-table-header">
            <div>
                <h2>{{ $pageTitle }}</h2>
                <p>{{ $documents->total() }} document(s) found</p>
            </div>
        </div>

        <div class="document-table-wrapper">
            <table class="document-table">

                <thead>
                    <tr>
                        <th>Document Code</th>
                        <th>Document Number</th>
                        <th>Subject / Filename</th>
                        <th>Document Type</th>
                        <th>Document Date</th>
                        <th>Date Received</th>
                        <th>Uploaded By</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($documents as $document)

                    <tr>
                        <td>{{ $document->document_code ?? '—' }}</td>

                        <td>{{ $document->document_number ?? '—' }}</td>

                        <td>
                            <div class="document-name">
                                {{ $document->subject ?: $document->title }}
                            </div>

                            <div class="document-filename">
                                {{ $document->original_name }}
                            </div>
                        </td>

                        <td>
                            {{ $document->category->name ?? 'Uncategorized' }}
                        </td>

                        <td>
                            {{ $document->document_date?->format('M d, Y') ?? '—' }}
                        </td>

                        <td>
                            {{ $document->date_received?->format('M d, Y') ?? '—' }}
                        </td>

                        <td>
                            {{ $document->uploader->name ?? 'Unknown User' }}
                        </td>
                        <td>

                            <button type="submit" class="document-action-buttons" onclick="openPreviewModal('{{ route('documents.preview', $document->id) }}', @js($document->original_name))">
                                Preview
                            </button>

                        </td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="8" class="document-empty">
                            No {{ strtolower($classification) }} documents found.
                            You can upload a document using the button above.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="document-pagination">
            {{ $documents->appends(request()->query())->links() }}
        </div>

    </div>

</div>


{{-- UPLOAD DOCUMENT MODAL --}}
<div class="document-modal" id="documentModal">

    <div class="document-modal-content">

        <div class="document-modal-header">
            <div>
                <h2>Upload Document</h2>
                <p>Register and upload a scanned document.</p>
            </div>

            <button type="button" class="document-modal-close" onclick="closeDocumentModal()">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">

            @csrf

            <div class="document-form-grid">

                <div class="document-form-group">
                    <label>Document Classification</label>
                    <select name="classification" required>
                        <option value="Incoming" {{ $classification === 'Incoming' ? 'selected' : '' }}>Incoming</option>
                        <option value="Outgoing" {{ $classification === 'Outgoing' ? 'selected' : '' }}>Outgoing</option>
                        <option value="Digital" {{ $classification === 'Digital' ? 'selected' : '' }}>Digital</option>
                    </select>
                </div>

                <div class="document-form-group">
                    <label>Document Number</label>
                    <input type="text" name="document_number" required>
                </div>

                <div class="document-form-group">
                    <label>Document Type</label>
                    <select name="category_id" required>
                        <option value="">Select Document Type</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="document-form-group">
                    <label>Document Date Released</label>
                    <input type="date" name="document_date">
                </div>

                <div class="document-form-group">
                    <label>Date Received</label>
                    <input type="date" name="date_received">
                </div>

                <div class="document-form-group document-full-width">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>

                <div class="document-form-group document-full-width">
                    <label>Scanned Copy</label>
                    <input type="file" name="scanned_copy" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small>Accepted formats: PDF, JPG, JPEG, PNG.</small>
                </div>

            </div>

            <div class="document-form-actions">
                <button type="button" class="btn-secondary" onclick="closeDocumentModal()">
                    Cancel
                </button>

                <button type="submit" class="btn-primary">
                    Upload Document
                </button>
            </div>

        </form>

    </div>
</div>

{{-- DOCUMENT PREVIEW MODAL --}}
<div class="document-modal" id="previewModal">

    <div class="document-modal-content preview-modal-content">

        <div class="document-modal-header">
            <div>
                <h2 id="previewTitle">Document Preview</h2>
                <p>Viewing scanned document inside BRO360.</p>
            </div>

            <button type="button" class="document-modal-close" onclick="closePreviewModal()">
                &times;
            </button>
        </div>

        <div class="document-preview-container">
            <iframe id="documentPreviewFrame" src="" title="Document Preview">
            </iframe>
        </div>

    </div>
</div>

<script>
    function openDocumentModal() {
        document.getElementById('documentModal').classList.add('show');
    }

    function closeDocumentModal() {
        document.getElementById('documentModal').classList.remove('show');
    }

    function openPreviewModal(url, filename) {
        document.getElementById('previewTitle').textContent = filename;

        document.getElementById('documentPreviewFrame').src = url;

        document.getElementById('previewModal').classList.add('show');
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.remove('show');

        document.getElementById('documentPreviewFrame').src = '';
    }

</script>


@endsection
