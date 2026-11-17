
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {

            // Document classification
            $table->enum('classification', [
                'Incoming',
                'Outgoing',
                'Digital'
            ])->nullable()->after('category_id');

            // System-generated code: I001, O001, D001
            $table->string('document_code', 20)
                ->nullable()
                ->unique()
                ->after('classification');

            // Reference number printed on the document
            $table->string('document_number', 150)
                ->nullable()
                ->after('document_code');

            // Document subject
            $table->string('subject', 500)
                ->nullable()
                ->after('document_number');

            // Dates
            $table->date('document_date')->nullable();
            $table->date('date_received')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['document_code']);

            $table->dropColumn([
                'classification',
                'document_code',
                'document_number',
                'subject',
                'document_date',
                'date_received',
            ]);
        });
    }
};
