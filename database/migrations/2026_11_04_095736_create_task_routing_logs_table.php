<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_routing_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_routing_id')
                ->constrained('task_routings')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 100);

            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();

            $table->text('remarks')->nullable();

            $table->json('details')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['task_routing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_routing_logs');
    }
};