<?php

use App\Models\Certificate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purges pre-existing certificates that were created before the instructor
     * sign-off gate was introduced (where enrollment completed_at is null).
     * These will be cleanly re-issued once the instructor clicks "Mark Complete".
     */
    public function up(): void
    {
        Certificate::query()
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('enrollments')
                    ->whereColumn('enrollments.user_id', 'certificates.user_id')
                    ->whereColumn('enrollments.course_id', 'certificates.course_id')
                    ->whereNotNull('enrollments.completed_at');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed.
    }
};
