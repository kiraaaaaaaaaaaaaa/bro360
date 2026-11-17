<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_template_assignees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_template_step_id')
                ->constrained('workflow_template_steps')
                ->cascadeOnDelete();

            $table->enum('assignment_type', [
                'user',
                'role',
                'position',
                'division',
                'organizational_unit',
            ]);

            // References the ID of the selected user, role,
            // position, division, or organizational unit.
            $table->unsignedBigInteger('assignment_id');

            $table->timestamps();

            $table->unique([
                'workflow_template_step_id',
                'assignment_type',
                'assignment_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_template_assignees');
    }
};