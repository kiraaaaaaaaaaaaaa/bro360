<?php

namespace App\Http\Controllers;


use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\FiscalYear;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $documents = Document::visibleTo($user)
            ->with([
                'category',
                'uploader',
                'versions',
            ])
            ->when(
                in_array($request->query('classification'), [
                    'Incoming',
                    'Outgoing',
                    'Digital',
                ]),
                function ($query) use ($request) {
                    $query->where(
                        'classification',
                        $request->query('classification')
                    );
                }
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('document_code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('visibility'), function ($query) use ($request) {
                $query->where('visibility', $request->visibility);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = DocumentCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('documents.index', compact(
            'documents',
            'categories'
        ));
    }

    public function create(Request $request)
    {
        $categories = DocumentCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $classification = $request->query('classification', 'Incoming');

        if (!in_array($classification, ['Incoming', 'Outgoing', 'Digital'])) {
            $classification = 'Incoming';
        }

        return view('documents.create', compact(
            'categories',
            'classification'
        ));
    }

    public function store(
        Request $request,
        GoogleDriveService $googleDriveService
    ) {
       
        $validated = $request->validate([
            'classification' => [
                'required',
                Rule::in(['Incoming', 'Outgoing', 'Digital']),
            ],

            'document_number' => [
                'required',
                'string',
                'max:150',
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('document_categories', 'id')
                    ->where('is_active', 1),
            ],

            'document_date' => [
                'nullable',
                'date',
            ],

            'date_received' => [
                'nullable',
                'date',
            ],

            'subject' => [
                'required',
                'string',
                'max:500',
            ],

            'scanned_copy' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:20480',
            ],
        ]);

        // Determine the applicable fiscal year.
        $referenceDate = Carbon::parse(
            $validated['document_date'] ?? now()
        );

        $fiscalYear = FiscalYear::whereDate(
            'start_date',
            '<=',
            $referenceDate
        )
            ->whereDate(
                'end_date',
                '>=',
                $referenceDate
            )
            ->first();

        // Fallback to the fiscal year matching the calendar year.
        if (!$fiscalYear) {
            $fiscalYear = FiscalYear::where(
                'year',
                $referenceDate->year
            )->first();
        }

        if (!$fiscalYear) {
            throw ValidationException::withMessages([
                'document_date' => [
                    'No fiscal year is configured for the document date.',
                ],
            ]);
        }

        

        // Generate the document code.
        $prefix = match ($validated['classification']) {
            'Incoming' => 'I',
            'Outgoing' => 'O',
            'Digital' => 'D',
        };

        $lastCode = Document::where(
            'classification',
            $validated['classification']
        )
            ->whereNotNull('document_code')
            ->orderByDesc('id')
            ->value('document_code');

        $lastNumber = 0;

        if ($lastCode && preg_match('/^[IOD](\d+)$/', $lastCode, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $documentCode = $prefix . str_pad(
            $lastNumber + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

        // Upload the scanned copy to Google Drive.
        $uploadedFile = $googleDriveService->uploadGeneralDocument(
            $request->file('scanned_copy'),
            $fiscalYear,
            $validated['classification'],
            $documentCode,
            $validated['document_number']
        );

 
        // Save the document record and its initial version.
        DB::transaction(function () use (
            $validated,
            $uploadedFile,
            $documentCode
        ) {
            $document = Document::create([
                'category_id' => $validated['category_id'],
                'classification' => $validated['classification'],
                'document_code' => $documentCode,
                'document_number' => $validated['document_number'],
                'subject' => $validated['subject'],
                'document_date' => $validated['document_date'] ?? null,
                'date_received' => $validated['date_received'] ?? null,

                'title' => $validated['subject'],
                'description' => null,

                // Store the Google Drive file ID.
                'file_path' => $uploadedFile['id'],
                'original_name' => $uploadedFile['original_name'],
                'mime_type' => $uploadedFile['mime_type'],
                'file_size' => $uploadedFile['size'],

                'uploaded_by' => Auth::id(),
                'visibility' => 'System',
            ]);

            DocumentVersion::create([
                'document_id' => $document->id,
                'version_no' => 1,
                'file_path' => $uploadedFile['id'],
                'original_name' => $uploadedFile['original_name'],
                'file_size' => $uploadedFile['size'],
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('documents.index', [
                'classification' => $validated['classification'],
            ])
            ->with(
                'success',
                "Document {$documentCode} was uploaded successfully."
            );
    }

    public function preview(
    Document $document,
    GoogleDriveService $googleDriveService
) {
    // Verify that the user can access this document.
    abort_unless(
        Document::visibleTo(Auth::user())
            ->whereKey($document->id)
            ->exists(),
        403
    );

    $allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    abort_unless(
        in_array($document->mime_type, $allowedTypes),
        415,
        'This file type cannot be previewed.'
    );

    $fileContents = $googleDriveService->downloadFile(
        $document->file_path
    );

    return Response::make($fileContents, 200, [
        'Content-Type' => $document->mime_type,
        'Content-Disposition' => 'inline; filename="' .
            addslashes($document->original_name) . '"',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'private, no-store',
    ]);
}
}


