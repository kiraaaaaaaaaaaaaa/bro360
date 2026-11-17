
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_drive_settings', function (Blueprint $table) {
            $table->id();

            // The BRO360 administrator who connected the account
            $table->foreignId('connected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('google_user_id')->nullable();
            $table->string('google_email')->nullable();

            // Encrypted token values copied from the existing connection
            $table->longText('access_token')->nullable();
            $table->longText('refresh_token')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->text('scopes')->nullable();

            $table->string('root_folder_id')->nullable();
            $table->timestamp('connected_at')->nullable();

            $table->timestamps();
        });

        // Preserve the existing test connection.
        // Copy the most recently connected account into the central settings.
        if (Schema::hasTable('google_drive_connections')) {
            $connection = DB::table('google_drive_connections')
                ->orderByDesc('connected_at')
                ->first();

            if ($connection) {
                DB::table('google_drive_settings')->insert([
                    'id' => 1,
                    'connected_by' => $connection->user_id,
                    'google_user_id' => $connection->google_user_id,
                    'google_email' => $connection->google_email,
                    'access_token' => $connection->access_token,
                    'refresh_token' => $connection->refresh_token,
                    'expires_at' => $connection->expires_at,
                    'scopes' => $connection->scopes,
                    'root_folder_id' => $connection->root_folder_id,
                    'connected_at' => $connection->connected_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('google_drive_settings');
    }
};
