<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_steps') &&
            !Schema::hasTable('workflow_template_steps')) {

            Schema::rename(
                'workflow_steps',
                'workflow_template_steps'
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_template_steps') &&
            !Schema::hasTable('workflow_steps')) {

            Schema::rename(
                'workflow_template_steps',
                'workflow_steps'
            );
        }
    }
};