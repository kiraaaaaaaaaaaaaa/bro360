<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_routings', function (Blueprint $table) {
            $table->id();

            // Task and workflow reference
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('workflow_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            // External destination
            $table->enum('destination_type', ['division', 'office']);
            $table->unsignedBigInteger('destination_id');

            // Routing sequence
            $table->unsignedSmallInteger('review_order');

            // Routing status
            $table->enum('status', [
                'Pending',
                'Dispatched',
                'Received',
                'Completed',
                'Returned',
                'For Revision',
                'On Hold',
                'Cancelled',
            ])->default('Pending');

            // QR identification
            $table->string('qr_token', 64)->unique();

            // Dispatch information
            $table->foreignId('dispatched_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('dispatched_at')->nullable();

            // Receiving party information
            $table->string('receiving_name', 255)->nullable();
            $table->string('receiving_position', 255)->nullable();
            $table->string('receiving_office', 255)->nullable();

            $table->timestamp('received_at')->nullable();

            // Google Drive photo reference
            $table->string('receiving_photo_drive_id')->nullable();
            $table->string('receiving_photo_name')->nullable();

            // Receiving party remarks
            $table->text('receiving_remarks')->nullable();

            // Final outcome
            $table->text('remarks')->nullable();

            $table->foreignId('action_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('action_at')->nullable();

            $table->timestamps();

            $table->index(['task_id', 'review_order']);
            $table->index(['destination_type', 'destination_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_routings');
    }
};