<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('process_type', 50);
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('version')->default(1);

            $table->boolean('is_active')->default(false);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['process_type', 'is_active']);
            $table->unique(['name', 'process_type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_templates');
    }
};