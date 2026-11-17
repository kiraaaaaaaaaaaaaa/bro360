<?php

namespace App\Http\Controllers;


use App\Models\GoogleDriveSetting;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Throwable;

class GoogleDriveFolderController extends Controller
{
    protected GoogleDriveService $googleDrive;

    public function __construct(GoogleDriveService $googleDrive)
    {
        $this->googleDrive = $googleDrive;
    }

    /**
     * Display central Google Drive connection and folder information.
     */
    public function index(Request $request)
    {
        $connection = GoogleDriveSetting::current();

        return view('google-drive.index', compact('connection'));
    }

    /**
     * Create or retrieve the central BRO360 root folder.
     */
    public function createRootFolder(Request $request)
    {
        $connection = GoogleDriveSetting::current();

        if (!$connection) {
            return back()->with(
                'error',
                'No central Google Drive account is connected. Please connect an account first.'
            );
        }

        try {
            $folderId = $this->googleDrive->ensureRootFolder($connection);

            return back()->with(
                'success',
                'BRO360 central Google Drive folder is ready. Folder ID: ' . $folderId
            );

        } catch (Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to create or access the central BRO360 folder. Please check the Google Drive connection and permissions.'
            );
        }
    }
}
