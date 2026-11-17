
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->string('google_drive_folder_id')
                ->nullable()
                ->after('year');
        });

        Schema::table('paps', function (Blueprint $table) {
            $table->string('google_drive_folder_id')
                ->nullable()
                ->after('fiscal_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('paps', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder_id');
        });

        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder_id');
        });
    }
};
