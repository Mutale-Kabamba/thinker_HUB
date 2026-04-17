<?php
try {
    $user = App\Models\User::where('role','student')->first();
    if(!$user) { echo "No student user found"; exit; }
    echo "Student: " . $user->name . PHP_EOL;
    $assessments = App\Models\Assessment::where('user_id', $user->id)->with('course')->get();
    echo "Assessments count: " . $assessments->count() . PHP_EOL;
    $submissions = App\Models\AssessmentSubmission::where('user_id', $user->id)->get();
    echo "Submissions count: " . $submissions->count() . PHP_EOL;
    echo "OK";
} catch(\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
