<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_drive_connections', function (Blueprint $table) {
            $table->string('root_folder_id')->nullable()->after('google_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('google_drive_connections', function (Blueprint $table) {
            $table->dropColumn('root_folder_id');
        });
    }
};