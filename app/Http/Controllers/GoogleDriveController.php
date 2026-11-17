<?php

namespace App\Http\Controllers;


use App\Models\GoogleDriveSetting;
use Google\Client;
use Google\Service\Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class GoogleDriveController extends Controller
{
    /**
     * Create the Google API client.
     */
    private function googleClient(): Client
    {
        $client = new Client();

        $client->setClientId(config('services.google_drive.client_id'));
        $client->setClientSecret(config('services.google_drive.client_secret'));
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));

        $client->setAccessType('offline');

        // Let the administrator choose which Google account to authorize.
        $client->setPrompt('select_account consent');

        $client->setIncludeGrantedScopes(true);

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/userinfo.email',
            'openid',
        ]);

        return $client;
    }

    /**
     * Redirect the administrator to Google authorization.
     */
    public function connect(Request $request)
    {
        if (
            !config('services.google_drive.client_id') ||
            !config('services.google_drive.client_secret') ||
            !config('services.google_drive.redirect_uri')
        ) {
            return redirect()->route('dashboard')
                ->with('error', 'Google Drive credentials are not configured.');
        }

        $state = Str::random(64);

        $request->session()->put('google_drive_oauth_state', $state);

        $client = $this->googleClient();

        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }

    /**
     * Handle Google's OAuth callback.
     */
    public function callback(Request $request)
    {
        // Check if Google returned an authorization error.
        if ($request->filled('error')) {
            $request->session()->forget('google_drive_oauth_state');

            return redirect()->route('dashboard')
                ->with('error', 'Google Drive authorization was cancelled or denied.');
        }

        // Validate OAuth state.
        $expectedState = $request->session()->pull('google_drive_oauth_state');
        $receivedState = $request->query('state');

        if (
            !$expectedState ||
            !$receivedState ||
            !hash_equals($expectedState, $receivedState)
        ) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid Google authorization state. Please try again.');
        }

        if (!$request->filled('code')) {
            return redirect()->route('dashboard')
                ->with('error', 'Google did not return an authorization code.');
        }

        try {
            $client = $this->googleClient();

            // Exchange authorization code for tokens.
            $token = $client->fetchAccessTokenWithAuthCode(
                $request->query('code')
            );

            if (isset($token['error'])) {
                throw new \RuntimeException(
                    $token['error_description'] ?? 'Unable to obtain Google access credentials.'
                );
            }

            $client->setAccessToken($token);

            // Retrieve authorized Google account information.
            $googleUser = new Oauth2($client);
            $userInfo = $googleUser->userinfo->get();

            if (!$userInfo->getEmail()) {
                throw new \RuntimeException(
                    'Unable to retrieve your Google account email.'
                );
            }

            // Retrieve the existing central connection.
            $setting = GoogleDriveSetting::firstOrNew(['id' => 1]);

            $sameAccount = $setting->exists
                && $setting->google_user_id === $userInfo->getId();

            // Only reuse the old refresh token if this is the same Google account.
            $refreshToken = $token['refresh_token']
                ?? ($sameAccount ? $setting->refresh_token : null);

            // A different account must have its own refresh token.
            if (!$refreshToken) {
                throw new \RuntimeException(
                    'Google did not provide a refresh token. Please revoke BRO360 access from your Google Account permissions and try connecting again.'
                );
            }

            // Save the new central connection.
            DB::transaction(function () use (
                $setting,
                $request,
                $token,
                $userInfo,
                $refreshToken,
                $sameAccount
            ) {
                $setting->id = 1;
                $setting->connected_by = $request->user()->id;

                $setting->google_user_id = $userInfo->getId();
                $setting->google_email = $userInfo->getEmail();

                // Store the complete token response as encrypted JSON.
                $setting->access_token = json_encode($token);
                $setting->refresh_token = $refreshToken;

                $setting->expires_at = now()->addSeconds(
                    (int) ($token['expires_in'] ?? 3600)
                );

                $setting->scopes = $token['scope'] ?? null;
                $setting->connected_at = now();

                // A different Google account needs a new root folder.
                if (!$sameAccount) {
                    $setting->root_folder_id = null;
                }

                $setting->save();
            });

            return redirect()->route('dashboard')
                ->with(
                    'success',
                    'Central Google Drive account connected successfully: '
                    . $userInfo->getEmail()
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }
    }
}
