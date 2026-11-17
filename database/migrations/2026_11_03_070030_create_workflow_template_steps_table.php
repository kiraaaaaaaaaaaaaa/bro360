<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_template_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_template_id')
                ->constrained('workflow_templates')
                ->cascadeOnDelete();

            $table->unsignedInteger('step_order');
            $table->string('name', 150);

            $table->enum('action_type', [
                'review',
                'approval',
            ])->default('review');

            // How many assigned reviewers must complete this stage?
            $table->enum('completion_rule', [
                'any',
                'all',
            ])->default('all');

            $table->unsignedSmallInteger('due_days')->nullable();

            $table->boolean('allow_return')->default(true);
            $table->boolean('allow_reject')->default(false);
            $table->boolean('allow_not_applicable')->default(false);

            $table->timestamps();

            $table->unique([
                'workflow_template_id',
                'step_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_template_steps');
    }
};