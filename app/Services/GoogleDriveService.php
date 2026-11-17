<?php

namespace App\Services;


use App\Models\GoogleDriveSetting;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;
use App\Models\FiscalYear;
use App\Models\Pap;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use App\Models\PapActivity;

class GoogleDriveService
{
    /**
     * Retrieve the central Google Drive settings.
     */
    private function getCentralSetting(): GoogleDriveSetting
    {
        $setting = GoogleDriveSetting::current();

        if (!$setting) {
            throw new RuntimeException(
                'No central Google Drive account is connected. Please connect an account first.'
            );
        }

        return $setting;
    }

    /**
     * Get an authenticated Google Drive client.
     */
    public function getClient(
        ?GoogleDriveSetting $setting = null
    ): Client {

        $setting = $setting ?? $this->getCentralSetting();

        $client = new Client();

        $client->setClientId(config('services.google_drive.client_id'));
        $client->setClientSecret(config('services.google_drive.client_secret'));
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));

        $client->setScopes([
            Drive::DRIVE_FILE,
            'https://www.googleapis.com/auth/userinfo.email',
            'openid',
        ]);

        // The model automatically decrypts this value.
        $accessToken = json_decode(
            $setting->access_token,
            true
        );

        if (!is_array($accessToken)) {
            throw new RuntimeException(
                'Invalid stored Google access token. Please reconnect your Google account.'
            );
        }

        // Preserve the separately stored refresh token.
        if ($setting->refresh_token) {
            $accessToken['refresh_token'] = $setting->refresh_token;
        }

        $client->setAccessToken($accessToken);

        // Refresh expired access token.
        if ($client->isAccessTokenExpired()) {

            if (!$setting->refresh_token) {
                throw new RuntimeException(
                    'Google refresh token is missing. Please reconnect your Google account.'
                );
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken(
                $setting->refresh_token
            );

            if (isset($newToken['error'])) {
                throw new RuntimeException(
                    'Unable to refresh Google access token: '
                        . ($newToken['error_description'] ?? $newToken['error'])
                );
            }

            // Preserve existing token details if Google does not return them.
            $updatedToken = array_merge($accessToken, $newToken);

            $updatedToken['refresh_token'] = $setting->refresh_token;

            $client->setAccessToken($updatedToken);

            // Saving triggers automatic encryption through the model cast.
            $setting->access_token = json_encode($updatedToken);

            $setting->expires_at = now()->addSeconds(
                (int) ($newToken['expires_in'] ?? 3600)
            );

            $setting->save();
        }

        return $client;
    }

    /**
     * Create or retrieve the central BRO360 root folder.
     */
    public function ensureRootFolder(
        ?GoogleDriveSetting $setting = null
    ): string {

        $setting = $setting ?? $this->getCentralSetting();

        $client = $this->getClient($setting);

        $drive = new Drive($client);

        // Check the registered root folder first.
        if ($setting->root_folder_id) {

            try {
                $folder = $drive->files->get(
                    $setting->root_folder_id,
                    ['fields' => 'id,name,mimeType,trashed']
                );

                if (
                    $folder->getMimeType() === 'application/vnd.google-apps.folder'
                    && !$folder->getTrashed()
                ) {
                    return $folder->getId();
                }
            } catch (\Google\Service\Exception $e) {
                // Registered folder is unavailable.
                // Continue to search or create a replacement.
            }
        }

        // Search for an existing BRO360 folder.
        $results = $drive->files->listFiles([
            'q' => "name = 'BRO360' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
            'spaces' => 'drive',
            'pageSize' => 100,
            'fields' => 'files(id,name)',
        ]);

        $files = $results->getFiles();

        if (count($files) > 0) {

            $folderId = $files[0]->getId();
        } else {

            // Create a new BRO360 root folder.
            $folder = new DriveFile([
                'name' => 'BRO360',
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            $createdFolder = $drive->files->create($folder, [
                'fields' => 'id,name',
            ]);

            $folderId = $createdFolder->getId();
        }

        // Save the central root folder ID.
        $setting->root_folder_id = $folderId;
        $setting->save();

        return $folderId;
    }


    /**
     * Find an existing folder or create a new one.
     */
    public function findOrCreateFolder(
        string $name,
        string $parentFolderId
    ): string {

        $setting = $this->getCentralSetting();

        $drive = new Drive($this->getClient($setting));

        // Escape characters used in Google Drive search queries.
        $escapedName = str_replace(
            ['\\', "'"],
            ['\\\\', "\\'"],
            $name
        );

        $escapedParent = str_replace(
            ['\\', "'"],
            ['\\\\', "\\'"],
            $parentFolderId
        );

        $query = "name = '{$escapedName}'"
            . " and '{$escapedParent}' in parents"
            . " and mimeType = 'application/vnd.google-apps.folder'"
            . " and trashed = false";

        $results = $drive->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'pageSize' => 100,
            'fields' => 'files(id,name)',
        ]);

        $files = $results->getFiles();

        if (count($files) > 0) {
            return $files[0]->getId();
        }

        // Create folder inside its specified parent.
        $folder = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId],
        ]);

        $createdFolder = $drive->files->create($folder, [
            'fields' => 'id,name',
        ]);

        return $createdFolder->getId();
    }

    /**
     * Create or retrieve a fiscal year folder.
     */
    public function ensureFiscalYearFolder(
        FiscalYear $fiscalYear
    ): string {

        // Reuse an existing registered folder.
        if ($fiscalYear->google_drive_folder_id) {
            return $fiscalYear->google_drive_folder_id;
        }

        $rootFolderId = $this->ensureRootFolder();

        $folderName = 'FY ' . $fiscalYear->year;

        $folderId = $this->findOrCreateFolder(
            $folderName,
            $rootFolderId
        );

        $fiscalYear->google_drive_folder_id = $folderId;
        $fiscalYear->save();

        return $folderId;
    }

    /**
     * Create or retrieve a PAP folder.
     */
    public function ensurePapFolder(Pap $pap): string
    {
        $pap->loadMissing('fiscalYear');

        if (!$pap->fiscalYear) {
            throw new RuntimeException(
                'The PAP does not have an assigned fiscal year.'
            );
        }

        // Reuse an existing registered folder.
        if ($pap->google_drive_folder_id) {
            return $pap->google_drive_folder_id;
        }

        $fiscalYearFolderId = $this->ensureFiscalYearFolder(
            $pap->fiscalYear
        );

        $folderName = $pap->pap_code
            ? $pap->pap_code . ' - ' . $pap->name
            : $pap->name;

        $folderId = $this->findOrCreateFolder(
            $folderName,
            $fiscalYearFolderId
        );

        $pap->google_drive_folder_id = $folderId;
        $pap->save();

        return $folderId;
    }

    
/**
 * Create or retrieve the General Documents folders
 * under the existing fiscal year folder.
 */
public function ensureGeneralDocumentFolders(
    FiscalYear $fiscalYear
): array {

    // Reuse the existing BRO360 fiscal year folder.
    $fiscalYearFolderId = $this->ensureFiscalYearFolder(
        $fiscalYear
    );

    // Create or retrieve General Documents.
    $generalFolderId = $this->findOrCreateFolder(
        'General Documents',
        $fiscalYearFolderId
    );

    // Create or retrieve classification folders.
    $incomingFolderId = $this->findOrCreateFolder(
        'Incoming',
        $generalFolderId
    );

    $outgoingFolderId = $this->findOrCreateFolder(
        'Outgoing',
        $generalFolderId
    );

    $digitalFolderId = $this->findOrCreateFolder(
        'Digital Files',
        $generalFolderId
    );

    return [
        'general_documents' => $generalFolderId,
        'incoming' => $incomingFolderId,
        'outgoing' => $outgoingFolderId,
        'digital' => $digitalFolderId,
    ];
}


    public function ensurePapSubfolders(Pap $pap): array
    {
        $papFolderId = $this->ensurePapFolder($pap);

        if (!$pap->google_drive_activities_folder_id) {
            $pap->google_drive_activities_folder_id =
                $this->findOrCreateFolder('Activities', $papFolderId);
        }

        if (!$pap->google_drive_general_folder_id) {
            $pap->google_drive_general_folder_id =
                $this->findOrCreateFolder('General Documents', $papFolderId);
        }

        if (!$pap->google_drive_proof_folder_id) {
            $pap->google_drive_proof_folder_id =
                $this->findOrCreateFolder('Proof of Liaison', $papFolderId);
        }

        $pap->save();

        return [
            'activities' => $pap->google_drive_activities_folder_id,
            'general_documents' => $pap->google_drive_general_folder_id,
            'proof_of_liaison' => $pap->google_drive_proof_folder_id,
        ];
    }

    /**
     * Upload a task attachment to the PAP's General Documents folder.
     */
    public function uploadTaskAttachment(
        UploadedFile $file,
        Pap $pap
    ): array {

        // Ensure the PAP folders exist.
        $folders = $this->ensurePapSubfolders($pap);

        $folderId = $folders['general_documents'];

        // Get authenticated central Drive client.
        $drive = new Drive($this->getClient());

        // Prepare file metadata.
        $fileMetadata = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        // Upload the file to Google Drive.
        $uploadedFile = $drive->files->create(
            $fileMetadata,
            [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType() ?: 'application/octet-stream',
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,size,webViewLink',
            ]
        );

        return [
            'id' => $uploadedFile->getId(),
            'name' => $uploadedFile->getName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'web_view_link' => $uploadedFile->getWebViewLink(),
        ];
    }

    
public function uploadActivityAttachment(
    UploadedFile $file,
    Pap $pap,
    PapActivity $activity
): array {

    // Ensure the PAP folders exist.
    $folders = $this->ensurePapSubfolders($pap);

    // Verify that the activity belongs to this PAP.
    abort_unless(
        (int) $activity->pap_id === (int) $pap->id,
        404
    );

    // Find or create the activity's dedicated folder.
    if (!$activity->google_drive_folder_id) {
        $activityFolderId = $this->findOrCreateFolder(
            $activity->title,
            $folders['activities']
        );

        $activity->google_drive_folder_id = $activityFolderId;
        $activity->save();
    }

    $drive = new Drive($this->getClient());

    $fileMetadata = new DriveFile([
        'name' => $file->getClientOriginalName(),
        'parents' => [$activity->google_drive_folder_id],
    ]);

    $uploadedFile = $drive->files->create(
        $fileMetadata,
        [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $file->getMimeType() ?: 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink',
        ]
    );

    return [
        'id' => $uploadedFile->getId(),
        'name' => $uploadedFile->getName(),
        'mime_type' => $uploadedFile->getMimeType(),
        'size' => $uploadedFile->getSize(),
        'web_view_link' => $uploadedFile->getWebViewLink(),
    ];
}


    public function downloadFile(string $fileId): string
    {
        $drive = new Drive($this->getClient());

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $drive->files->get($fileId, [
            'alt' => 'media',
        ]);

        return $response->getBody()->getContents();
    }



public function uploadLiaisonProof(
    UploadedFile $file,
    Pap $pap,
    ?PapActivity $activity = null
): array {

    // Ensure the PAP folders exist.
    $folders = $this->ensurePapSubfolders($pap);

    // Default destination: General Documents.
    $parentFolderId = $folders['general_documents'];

    // If linked to an activity, use its dedicated folder.
    if ($activity) {

        abort_unless(
            (int) $activity->pap_id === (int) $pap->id,
            404
        );

        // Create the activity folder if it does not exist.
        if (!$activity->google_drive_folder_id) {
            $activity->google_drive_folder_id =
                $this->findOrCreateFolder(
                    $activity->title,
                    $folders['activities']
                );

            $activity->save();
        }

        $parentFolderId = $activity->google_drive_folder_id;
    }

    // Create or find the Liaison Proof subfolder.
    $liaisonFolderId = $this->findOrCreateFolder(
        'Liaison Proof',
        $parentFolderId
    );

    $drive = new Drive($this->getClient());

    $fileMetadata = new DriveFile([
        'name' => $file->getClientOriginalName(),
        'parents' => [$liaisonFolderId],
    ]);

    $uploadedFile = $drive->files->create(
        $fileMetadata,
        [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $file->getMimeType() ?: 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink',
        ]
    );

    return [
        'id' => $uploadedFile->getId(),
        'name' => $uploadedFile->getName(),
        'mime_type' => $uploadedFile->getMimeType(),
        'size' => $uploadedFile->getSize(),
        'web_view_link' => $uploadedFile->getWebViewLink(),
    ];
}


/**
 * Upload an incoming, outgoing, or digital document
 * to its designated General Documents folder.
 */
public function uploadGeneralDocument(
    UploadedFile $file,
    FiscalYear $fiscalYear,
    string $classification,
    string $documentCode,
    string $documentNumber
): array {

    $folders = $this->ensureGeneralDocumentFolders($fiscalYear);

    $folderKey = match ($classification) {
        'Incoming' => 'incoming',
        'Outgoing' => 'outgoing',
        'Digital' => 'digital',
        default => throw new RuntimeException(
            'Invalid document classification.'
        ),
    };

    $folderId = $folders[$folderKey];

    // Preserve the original filename and extension.
    $originalName = $file->getClientOriginalName();

    // Build the standardized Google Drive filename.
    $driveFileName = $documentCode
        . ' - '
        . $documentNumber
        . ' - '
        . $originalName;

    $drive = new Drive($this->getClient());

    $fileMetadata = new DriveFile([
        'name' => $driveFileName,
        'parents' => [$folderId],
    ]);

    $uploadedFile = $drive->files->create(
        $fileMetadata,
        [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $file->getMimeType()
                ?: 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink',
        ]
    );

    return [
        'id' => $uploadedFile->getId(),
        'name' => $uploadedFile->getName(),
        'mime_type' => $uploadedFile->getMimeType(),
        'size' => $uploadedFile->getSize(),
        'web_view_link' => $uploadedFile->getWebViewLink(),
        'folder_id' => $folderId,
        'original_name' => $originalName,
    ];
}


}
