<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('paps', function (Blueprint $table) {
            $table->string('google_drive_proof_folder_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('paps', function (Blueprint $table) {
            $table->dropColumn('google_drive_proof_folder_id');
        });
    }
};
