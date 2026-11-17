
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_reviews', function (Blueprint $table) {
            // Preserve an index for the task_id foreign key.
            $table->index('task_id', 'task_reviews_task_id_index');
        });

        Schema::table('task_reviews', function (Blueprint $table) {
            // Remove the old unique constraint.
            $table->dropUnique('task_id');

            // Allow multiple reviewers in the same workflow step.
            $table->unique(
                ['task_id', 'review_order', 'reviewer_id'],
                'task_review_unique_reviewer_per_step'
            );
        });
    }

    public function down(): void
    {
        Schema::table('task_reviews', function (Blueprint $table) {
            $table->dropUnique('task_review_unique_reviewer_per_step');

            $table->unique(
                ['task_id', 'review_order'],
                'task_id'
            );

            $table->dropIndex('task_reviews_task_id_index');
        });
    }
};
