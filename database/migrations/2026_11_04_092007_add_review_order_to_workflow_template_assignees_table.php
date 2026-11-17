<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_template_assignees', function (Blueprint $table) {
            $table->unsignedSmallInteger('review_order')
                ->default(1)
                ->after('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_template_assignees', function (Blueprint $table) {
            $table->dropColumn('review_order');
        });
    }
};