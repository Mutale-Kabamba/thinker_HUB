<?php

use App\Models\Badge;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Resets and purges unapproved course completion badges (Graduate, Mastermind)
     * and course_completed XP transactions that were awarded prior to the instructor
     * sign-off gate. Only students with active, instructor-signed-off enrollments
     * (completed_at != null) will retain course completion badges and XP.
     */
    public function up(): void
    {
        $graduateBadge = Badge::query()->where('key', 'course_completed')->first();
        $mastermindBadge = Badge::query()->where('key', 'mastermind')->first();

        // 1. Delete course_completed XP transactions for courses without instructor sign-off
        XpTransaction::query()
            ->where('source', 'course_completed')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('enrollments')
                    ->whereColumn('enrollments.user_id', 'xp_transactions.user_id')
                    ->whereColumn('enrollments.course_id', 'xp_transactions.source_id')
                    ->whereNotNull('enrollments.completed_at');
            })
            ->delete();

        // 2. Reset Graduate badge (course_completed) for students with 0 signed-off course completions
        if ($graduateBadge) {
            $unqualifiedUserIds = DB::table('user_badge')
                ->where('badge_id', $graduateBadge->id)
                ->whereNotExists(function ($query) {
                    $query->selectRaw(1)
                        ->from('enrollments')
                        ->whereColumn('enrollments.user_id', 'user_badge.user_id')
                        ->whereNotNull('enrollments.completed_at');
                })
                ->pluck('user_id')
                ->all();

            if ($unqualifiedUserIds !== []) {
                DB::table('user_badge')
                    ->where('badge_id', $graduateBadge->id)
                    ->whereIn('user_id', $unqualifiedUserIds)
                    ->delete();

                XpTransaction::query()
                    ->where('source', 'badge')
                    ->where('source_id', $graduateBadge->id)
                    ->whereIn('user_id', $unqualifiedUserIds)
                    ->delete();
            }
        }

        // 3. Reset Mastermind badge for students with fewer than 3 signed-off course completions
        if ($mastermindBadge) {
            $mastermindUserIds = DB::table('user_badge')
                ->where('badge_id', $mastermindBadge->id)
                ->pluck('user_id')
                ->all();

            foreach ($mastermindUserIds as $userId) {
                $count = Enrollment::query()
                    ->where('user_id', $userId)
                    ->whereNotNull('completed_at')
                    ->count();

                if ($count < 3) {
                    DB::table('user_badge')
                        ->where('badge_id', $mastermindBadge->id)
                        ->where('user_id', $userId)
                        ->delete();

                    XpTransaction::query()
                        ->where('source', 'badge')
                        ->where('source_id', $mastermindBadge->id)
                        ->where('user_id', $userId)
                        ->delete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed.
    }
};
