<?php

namespace Database\Seeders;

use App\Models\CourseGamificationRule;
use Illuminate\Database\Seeder;

class CourseGamificationRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CourseGamificationRule::updateOrCreate(
            ['course_id' => null],
            [
                'name' => '🌟 Global Platform Default Matrix',
                'rules' => CourseGamificationRule::getDefaultRepeaterRows(),
                'is_active' => true,
            ]
        );
    }
}
