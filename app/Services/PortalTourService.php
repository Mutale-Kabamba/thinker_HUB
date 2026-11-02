<?php

namespace App\Services;

class PortalTourService
{
    /**
     * Get tour steps tailored for the specified portal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStepsForPortal(string $portal): array
    {
        return match ($portal) {
            'student' => $this->getStudentSteps(),
            'instructor' => $this->getInstructorSteps(),
            'admin' => $this->getAdminSteps(),
            'parent' => $this->getParentSteps(),
            'contributor' => $this->getContributorSteps(),
            default => $this->getStudentSteps(),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getStudentSteps(): array
    {
        return [
            [
                'element' => '.fi-page-header-main-ctn, .fi-page, .fi-main',
                'popover' => [
                    'title' => 'Welcome to Your Student Portal',
                    'description' => 'Welcome to think.er HUB! This is your all-in-one learning environment where you can track progress, attend scheduled classes, access study materials, collaborate with peers, and earn certifications.',
                    'side' => 'bottom',
                    'align' => 'center',
                ],
            ],
            [
                'element' => '#tour-student-hero, .grid:has(.carousel-indicator-dot), .fi-main',
                'popover' => [
                    'title' => 'Dynamic Announcements & Milestones',
                    'description' => 'Stay updated with personalized carousel banners highlighting your next live class session, overdue tasks, course milestones, and recommended learning actions.',
                    'side' => 'bottom',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '#tour-student-ranking, .edtech-card:has(a[href*="leaderboard"]), .fi-main',
                'popover' => [
                    'title' => 'My Ranking & Leaderboard',
                    'description' => 'Keep tabs on your peer ranking, earned learning points, and benchmark your progress against classmates on the community leaderboard.',
                    'side' => 'left',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '#tour-student-status, .grid:has(a[href*="/learn/materials"]), .fi-main',
                'popover' => [
                    'title' => 'Learning Status & Completion Rings',
                    'description' => 'Monitor your overall completion health across three key learning pillars: Learning Materials read, Assignments submitted, and Assessments completed.',
                    'side' => 'bottom',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '#tour-student-courses-card, a[href*="/learn/courses"]',
                'popover' => [
                    'title' => 'Enrolled Courses & Active Progress',
                    'description' => 'View all your registered courses with real-time session progress bars. Switch between Active and Completed tabs to inspect your curriculums.',
                    'side' => 'top',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '#tour-student-calendar, a[href*="/learn/schedule"]',
                'popover' => [
                    'title' => 'Interactive Schedule Calendar',
                    'description' => 'Click any date on your interactive calendar to preview scheduled class sessions (purple dots), assignment due dates (rose dots), and quiz tests (emerald dots).',
                    'side' => 'left',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '#tour-student-agenda, .edtech-card:has(a[href*="/learn/schedule"])',
                'popover' => [
                    'title' => 'Upcoming Agenda & Class Details',
                    'description' => 'See upcoming sessions and deadlines at a glance. Click "View Class Details" on any session to review delivery mode, venue, and meeting links.',
                    'side' => 'left',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/courses"], .fi-sidebar-item:has(a[href*="courses"])',
                'popover' => [
                    'title' => 'Courses & Curriculums',
                    'description' => 'Access your active course curriculums, lesson modules, video guides, and track your overall completion progress unit by unit.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/materials"], .fi-sidebar-item:has(a[href*="materials"])',
                'popover' => [
                    'title' => 'Learning Materials & Cheatsheets',
                    'description' => 'Download official PDF lecture slides, study notes, and code cheat sheets curated by your instructors for offline reading.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/learning-resources"], .fi-sidebar-item:has(a[href*="learning-resources"])',
                'popover' => [
                    'title' => 'Curated Resource Library',
                    'description' => 'Explore supplementary technical documentation, recommended developer software, external tutorials, cheatsheets, and video repositories.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/study-buddy"], .fi-sidebar-item:has(a[href*="study-buddy"])',
                'popover' => [
                    'title' => '24/7 AI Study Buddy',
                    'description' => 'Your personal AI tutor contextually grounded in your enrolled course content. Ask for concept explanations, code walkthroughs, and practice problems anytime.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/certificates"], .fi-sidebar-item:has(a[href*="certificates"])',
                'popover' => [
                    'title' => 'My Certificates & Credentials',
                    'description' => 'View, download, and verify your official graduation certificates and achievement credentials as you complete each course milestone.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/assignments"], .fi-sidebar-item:has(a[href*="assignments"])',
                'popover' => [
                    'title' => 'Assignments & Submissions',
                    'description' => 'Submit your practical homework and project repositories, inspect instructor feedback and grades, and take advantage of revision retakes.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/assessments"], .fi-sidebar-item:has(a[href*="assessments"])',
                'popover' => [
                    'title' => 'Course Assessments',
                    'description' => 'Access graded midterm evaluations, final exams, scoring rubrics, and detailed instructor evaluations across all your classes.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/quizzes"], .fi-sidebar-item:has(a[href*="quizzes"])',
                'popover' => [
                    'title' => 'Interactive Quizzes & Tests',
                    'description' => 'Take interactive multiple-choice tests with automated instant grading, timer controls, answer explanations, and performance metrics.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/schedule"], .fi-sidebar-item:has(a[href*="schedule"])',
                'popover' => [
                    'title' => 'Schedule & Timetable',
                    'description' => 'Switch between day, week, and month calendar views to coordinate live virtual sessions, physical classroom attendances, and deadlines.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/community"], .fi-sidebar-item:has(a[href*="community"])',
                'popover' => [
                    'title' => 'Student Community & Discussions',
                    'description' => 'Join track discussion channels, ask peers questions, share study milestones, participate in study groups, and celebrate achievements together.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/opportunities"], .fi-sidebar-item:has(a[href*="opportunities"])',
                'popover' => [
                    'title' => 'Opportunities & Career Desk',
                    'description' => 'Explore exclusive tech internship opportunities, junior developer job listings, hackathons, and industry career programs.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/claim-hub"], .fi-sidebar-item:has(a[href*="claim-hub"])',
                'popover' => [
                    'title' => 'Claim Hub & Learning Rewards',
                    'description' => 'Earn coins and badges by completing lessons, submitting assignments on time, and climbing the ranks. Redeem your rewards and perks here!',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/reviews"], .fi-sidebar-item:has(a[href*="reviews"])',
                'popover' => [
                    'title' => 'Course & Instructor Reviews',
                    'description' => 'Rate your courses, leave constructive feedback on instructors, and help shape continuous curriculum improvements.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/learn/settings"], .fi-sidebar-item:has(a[href*="settings"])',
                'popover' => [
                    'title' => 'Profile & Account Settings',
                    'description' => 'Manage your profile details, biometric passkeys, notification preferences, password security, and account credentials.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '.fi-topbar a[href*="study-buddy"], .fi-topbar form, .fi-topbar',
                'popover' => [
                    'title' => 'Quick Search & Study Buddy Shortcut',
                    'description' => 'Search the entire portal instantly and launch your AI Study Buddy with a single click from the top navigation bar at any time.',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
            [
                'element' => '.portal-tour-trigger-btn, .fi-topbar',
                'popover' => [
                    'title' => 'Replay Tour Anytime',
                    'description' => 'Need a refresher? Click this Tour button in the top navigation bar at any time to replay this comprehensive walkthrough. Happy learning!',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getInstructorSteps(): array
    {
        return [
            [
                'element' => '.fi-page-header-main-ctn, .fi-page, .fi-main',
                'popover' => [
                    'title' => 'Instructor Operations Hub',
                    'description' => 'Welcome to your teaching headquarters. Oversee assigned cohorts, manage attendance registers, evaluate student work, and run daily class operations.',
                    'side' => 'bottom',
                    'align' => 'center',
                ],
            ],
            [
                'element' => 'a[href*="/teach/ai-assistant"], .fi-sidebar-item:has(a[href*="ai-assistant"])',
                'popover' => [
                    'title' => 'AI Teaching Co-Pilot',
                    'description' => 'Automate teaching workflows conversationally! Tell the co-pilot to take attendance, update schedules, draft session agendas, or summarize cohort stats.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/course-resource/courses"], a[href*="/teach/courses"], .fi-sidebar-item:has(a[href*="course"])',
                'popover' => [
                    'title' => 'Assigned Courses & Syllabus',
                    'description' => 'Review courses assigned to you, inspect syllabus modules, manage curriculum units, and track overall cohort progress.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/course-intakes"], .fi-sidebar-item:has(a[href*="course-intakes"])',
                'popover' => [
                    'title' => 'Course Intakes & Cohorts',
                    'description' => 'Monitor active cohort groups, intake timelines, student enrollment capacities, and roster distributions.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/course-session-resource/course-sessions"], a[href*="/teach/course-sessions"], .fi-sidebar-item:has(a[href*="session"])',
                'popover' => [
                    'title' => 'Class Sessions & Delivery',
                    'description' => 'Schedule upcoming lectures, configure delivery modes (live virtual, physical classroom, or recorded video), and manage meeting links.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/attendance-register"], .fi-sidebar-item:has(a[href*="attendance"])',
                'popover' => [
                    'title' => 'Attendance Registers & Roster Sync',
                    'description' => 'Mark live student attendance, record excused absences or apologies, sync roster changes, and generate attendance roll calls.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/schedule"], .fi-sidebar-item:has(a[href*="schedule"])',
                'popover' => [
                    'title' => 'Teaching Schedule & Calendar',
                    'description' => 'Visualize all your teaching sessions, cohort workshops, and student consultation slots in an interactive timetable.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/learning-material-resource/learning-materials"], a[href*="/teach/learning-materials"], .fi-sidebar-item:has(a[href*="material"])',
                'popover' => [
                    'title' => 'Learning Materials & Handouts',
                    'description' => 'Upload and distribute official PDF slides, code cheatsheets, reading notes, and reference files to your students.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/resource-video-resource/resource-videos"], a[href*="/teach/resource-videos"], .fi-sidebar-item:has(a[href*="video"])',
                'popover' => [
                    'title' => 'Video Guides & Recordings',
                    'description' => 'Publish lesson recordings, tutorial screen guides, and video walk-throughs for asynchronous student review.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/assignment-resource/assignments"], a[href*="/teach/assignments"], .fi-sidebar-item:has(a[href*="assignment-resource"])',
                'popover' => [
                    'title' => 'Assignments & Tasks',
                    'description' => 'Create practical coursework, set deadlines, upload starter templates, and define task requirements for cohorts.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/assignment-submission-resource/assignment-submissions"], a[href*="/teach/assignment-submissions"], .fi-sidebar-item:has(a[href*="assignment-submission"])',
                'popover' => [
                    'title' => 'Assignment Submissions & Grading',
                    'description' => 'Review student submissions, inspect code and project files, assign grades, and provide feedback and retake opportunities.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/assessment-resource/assessments"], a[href*="/teach/assessments"], .fi-sidebar-item:has(a[href*="assessment-resource"])',
                'popover' => [
                    'title' => 'Formal Course Assessments',
                    'description' => 'Design formal midterm and final assessments, establish scoring rubrics, and schedule evaluation deadlines.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/assessment-submission-resource/assessment-submissions"], a[href*="/teach/assessment-submissions"], .fi-sidebar-item:has(a[href*="assessment-submission"])',
                'popover' => [
                    'title' => 'Assessment Grading & Rubrics',
                    'description' => 'Grade formal student assessments, record benchmark evaluations, and track class scoring distributions.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/quiz-resource/quizzes"], a[href*="/teach/quizzes"], .fi-sidebar-item:has(a[href*="quiz"])',
                'popover' => [
                    'title' => 'Quizzes & Question Bank',
                    'description' => 'Author multiple-choice quizzes, configure timers, pass marks, question banks, and scheduled release dates.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/student-results"], .fi-sidebar-item:has(a[href*="student-results"])',
                'popover' => [
                    'title' => 'Student Results & Scorecards',
                    'description' => 'Inspect student quiz attempts, review auto-graded results, analyze cohort health, and export scorecards.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/students"], .fi-sidebar-item:has(a[href*="students"])',
                'popover' => [
                    'title' => 'Student Roster & Profiles',
                    'description' => 'Search all enrolled students in your courses, view individual academic histories, attendance statistics, and linked parent profiles.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/broadcasts"], .fi-sidebar-item:has(a[href*="broadcasts"])',
                'popover' => [
                    'title' => 'Cohort Broadcasts',
                    'description' => 'Send urgent announcements, timetable alerts, and assignment reminders directly to all enrolled students across your cohorts.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/claim-request-resource/claim-requests"], a[href*="/teach/claim-item-resource/claim-items"], .fi-sidebar-item:has(a[href*="claim"])',
                'popover' => [
                    'title' => 'Gamification & Reward Approvals',
                    'description' => 'Review and approve student reward claims, monitor coin expenditures, and award achievement badges for exceptional learning.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/reports"], .fi-sidebar-item:has(a[href*="reports"])',
                'popover' => [
                    'title' => 'Academic & Attendance Reports',
                    'description' => 'Generate and download comprehensive PDF/CSV reports covering cohort attendance rates, grades, and completion metrics.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/analytics"], .fi-sidebar-item:has(a[href*="analytics"])',
                'popover' => [
                    'title' => 'Cohort Performance Analytics',
                    'description' => 'Track visual cohort performance charts, student engagement heatmaps, and retention metrics over time.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/hub-posts"], .fi-sidebar-item:has(a[href*="hub-posts"])',
                'popover' => [
                    'title' => 'Articles & Tutorials',
                    'description' => 'Share your technical expertise by publishing articles, learning guides, and tutorials to the Thinker HUB community.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/teach/settings"], .fi-sidebar-item:has(a[href*="settings"])',
                'popover' => [
                    'title' => 'Instructor Profile & Settings',
                    'description' => 'Update your professional bio, notification preferences, biometric passkeys, and account credentials.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '.fi-topbar a[href*="ai-assistant"], .fi-topbar',
                'popover' => [
                    'title' => 'Quick AI Teaching Co-Pilot',
                    'description' => 'Click this topbar button anytime to summon your AI Teaching Co-Pilot from any page in the portal.',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
            [
                'element' => '.portal-tour-trigger-btn, .fi-topbar',
                'popover' => [
                    'title' => 'Replay Tour Anytime',
                    'description' => 'You can trigger this guide again at any time by clicking the Tour button in your top navigation bar. Have a great teaching session!',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getAdminSteps(): array
    {
        return [
            [
                'element' => '.fi-page-header-main-ctn, .fi-page, .fi-main',
                'popover' => [
                    'title' => 'Platform Administration Hub',
                    'description' => 'Central command center for Thinker HUB. Oversee operations, manage platform security, track enrollments, and configure curriculums.',
                    'side' => 'bottom',
                    'align' => 'center',
                ],
            ],
            [
                'element' => '.fi-wi-stats-overview, .fi-widget, .fi-main',
                'popover' => [
                    'title' => 'Executive Stats & Platform Activity',
                    'description' => 'Monitor real-time platform KPIs including total active learners, revenue trends, pending tasks, and recent system activities.',
                    'side' => 'bottom',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/users"], .fi-sidebar-item:has(a[href*="users"])',
                'popover' => [
                    'title' => 'User Accounts & Role Permissions',
                    'description' => 'Control user accounts across all roles (Students, Instructors, Parents, Admins, Contributors). Manage role assignments, reset passkeys, and audit access.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/students"], .fi-sidebar-item:has(a[href*="students"])',
                'popover' => [
                    'title' => 'Student Registry & Enrollments',
                    'description' => 'Access master student profiles, manage active enrollments, inspect verification statuses, and adjust course allocations.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/instructors"], .fi-sidebar-item:has(a[href*="instructors"])',
                'popover' => [
                    'title' => 'Instructor Directory & Contracts',
                    'description' => 'Manage active faculty instructors, course assignments, teaching contracts, and instructor credentials.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/instructor-applications"], .fi-sidebar-item:has(a[href*="instructor-applications"])',
                'popover' => [
                    'title' => 'Instructor Recruitment & Applications',
                    'description' => 'Screen incoming teaching applicants, review CVs/resumes, schedule interviews, and onboard approved instructors.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/courses"], .fi-sidebar-item:has(a[href*="courses"])',
                'popover' => [
                    'title' => 'Course Catalog & Curriculums',
                    'description' => 'Create new courses, manage syllabus modules, set tuition fees, configure delivery modes, and assign lead instructors.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/course-intakes"], .fi-sidebar-item:has(a[href*="course-intakes"])',
                'popover' => [
                    'title' => 'Intakes & Cohort Lifecycles',
                    'description' => 'Schedule cohort intake windows, configure enrollment caps, manage start/graduation dates, and track intake health.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/course-sessions"], .fi-sidebar-item:has(a[href*="course-sessions"])',
                'popover' => [
                    'title' => 'Course Sessions & Timetable',
                    'description' => 'Oversee all classroom sessions across the platform, including physical venue rooms and virtual workshop meeting links.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/learning-materials"], .fi-sidebar-item:has(a[href*="learning-materials"])',
                'popover' => [
                    'title' => 'Learning Materials & Handouts',
                    'description' => 'Manage the central library of student PDF handouts, code cheat sheets, and curriculum resources.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/resource-videos"], .fi-sidebar-item:has(a[href*="resource-videos"])',
                'popover' => [
                    'title' => 'Video Guide Repositories',
                    'description' => 'Supervise recorded lectures, tutorial series, and video resources across all platform courses.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/assignments"], a[href*="/manage/assignment-submissions"], .fi-sidebar-item:has(a[href*="assignment"])',
                'popover' => [
                    'title' => 'Assignments & Student Submissions',
                    'description' => 'Inspect coursework assignments and track student submission rates, grading turnarounds, and retakes.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/assessments"], a[href*="/manage/assessment-submissions"], .fi-sidebar-item:has(a[href*="assessment"])',
                'popover' => [
                    'title' => 'Course Assessments & Grading Oversight',
                    'description' => 'Oversee formal assessments, scoring rubrics, and grade distribution across all active cohorts.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/quizzes"], .fi-sidebar-item:has(a[href*="quizzes"])',
                'popover' => [
                    'title' => 'Quizzes & Examination Bank',
                    'description' => 'Manage platform quiz repositories, timed test templates, and automated grading evaluations.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/attendance-register"], .fi-sidebar-item:has(a[href*="attendance"])',
                'popover' => [
                    'title' => 'Platform Attendance Oversight',
                    'description' => 'Audit institutional attendance compliance, review session registers, and track student attendance metrics.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/accounting-dashboard"], .fi-sidebar-item:has(a[href*="accounting"])',
                'popover' => [
                    'title' => 'Accounting Dashboard & Tuition Payments',
                    'description' => 'Track tuition collections, monitor payment gateways (Stripe/PayFast/Cash), verify manual receipts, and reconcile revenue.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/promo-codes"], .fi-sidebar-item:has(a[href*="promo"])',
                'popover' => [
                    'title' => 'Promotions & Discount Codes',
                    'description' => 'Create coupon codes, percentage discount campaigns, intake promotional discounts, and track redemption analytics.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/reports"], .fi-sidebar-item:has(a[href*="reports"])',
                'popover' => [
                    'title' => 'Executive Reports & Data Export',
                    'description' => 'Generate and export comprehensive institutional reports on enrollment figures, revenues, student grades, and attendance.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/opportunities"], .fi-sidebar-item:has(a[href*="opportunities"])',
                'popover' => [
                    'title' => 'Career Opportunities & Job Board',
                    'description' => 'Publish and curate tech internships, developer jobs, company partner listings, and hackathon opportunities for students.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/claim-items"], a[href*="/manage/claim-requests"], a[href*="/manage/course-gamification-rules"], .fi-sidebar-item:has(a[href*="claim"])',
                'popover' => [
                    'title' => 'Gamification Economy & Rewards',
                    'description' => 'Configure reward shop merchandise, establish point-earning rules, and manage student prize redemptions.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/hub-posts"], a[href*="/manage/resource-comments"], .fi-sidebar-item:has(a[href*="hub-posts"])',
                'popover' => [
                    'title' => 'Articles & Community Moderation',
                    'description' => 'Moderate community discussion posts, review author article submissions, and ensure safe communication across all channels.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/manage/settings"], .fi-sidebar-item:has(a[href*="settings"])',
                'popover' => [
                    'title' => 'Platform Settings & Security',
                    'description' => 'Configure platform parameters, institutional branding, biometric security, and administrative access controls.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '.fi-topbar form, .fi-topbar input, .fi-topbar',
                'popover' => [
                    'title' => 'Global Search & Quick Actions',
                    'description' => 'Instantly locate any student, course, instructor, or transaction using global search from any page.',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
            [
                'element' => '.portal-tour-trigger-btn, .fi-topbar',
                'popover' => [
                    'title' => 'Replay Tour Anytime',
                    'description' => 'Click this Tour button at any time to review all administrative modules and workflows. Have a great session!',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getParentSteps(): array
    {
        return [
            [
                'element' => '.fi-page-header-main-ctn, .fi-page, .fi-main',
                'popover' => [
                    'title' => 'Parent & Guardian Portal',
                    'description' => 'Stay informed on your children\'s academic progress, attendance records, upcoming schedules, and direct instructor communications.',
                    'side' => 'bottom',
                    'align' => 'center',
                ],
            ],
            [
                'element' => 'a[href*="/parent/overview"], .fi-sidebar-item:has(a[href*="overview"]), .fi-main',
                'popover' => [
                    'title' => 'Parent Overview & Academic Health',
                    'description' => 'View a high-level summary of your linked children\'s overall attendance percentages, upcoming class sessions, and recent milestones.',
                    'side' => 'bottom',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/manage-kids"], .fi-sidebar-item:has(a[href*="kids"])',
                'popover' => [
                    'title' => 'Connected Student Profiles & Enrollments',
                    'description' => 'View your linked children\'s profiles, track course enrollments, inspect educational pathways, and connect new child accounts.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/kids-activities"], .fi-sidebar-item:has(a[href*="activities"])',
                'popover' => [
                    'title' => 'Attendance Records & Daily Activities',
                    'description' => 'Track your child\'s physical and virtual attendance, check lesson completion status, and review homework submissions.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/kids-leaderboard"], .fi-sidebar-item:has(a[href*="leaderboard"])',
                'popover' => [
                    'title' => 'Student Achievements & Milestones',
                    'description' => 'Celebrate your child\'s learning achievements, track quiz badges, earned reward coins, and view leaderboard rankings.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/course-parents"], .fi-sidebar-item:has(a[href*="course-parents"])',
                'popover' => [
                    'title' => 'Course Syllabuses & Class Outlines',
                    'description' => 'Review course outlines, learning objectives, instructor details, and session schedules for all courses your children attend.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/parents-chat"], .fi-sidebar-item:has(a[href*="chat"])',
                'popover' => [
                    'title' => 'Direct Instructor Chat & Support',
                    'description' => 'Communicate directly with course instructors to discuss your child\'s learning needs, request updates, and stay informed.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/parent/parent-settings"], .fi-sidebar-item:has(a[href*="parent-settings"])',
                'popover' => [
                    'title' => 'Parent Notifications & Account Settings',
                    'description' => 'Manage your contact details, SMS/email alert preferences for attendance notifications, and account credentials.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '.portal-tour-trigger-btn, .fi-topbar',
                'popover' => [
                    'title' => 'Replay Tour Anytime',
                    'description' => 'Click this Tour button at any time to replay this orientation guide and discover new features for parents.',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getContributorSteps(): array
    {
        return [
            [
                'element' => '.fi-page-header-main-ctn, .fi-page, .fi-main',
                'popover' => [
                    'title' => 'Contributor Studio',
                    'description' => 'Welcome to the Thinker HUB Contributor Studio! Create, format, and share valuable tech articles, code walk-throughs, and tutorials.',
                    'side' => 'bottom',
                    'align' => 'center',
                ],
            ],
            [
                'element' => 'a[href*="/contribute/contributor-overview"], .fi-sidebar-item:has(a[href*="overview"]), .fi-main',
                'popover' => [
                    'title' => 'Contributor Overview & Article Analytics',
                    'description' => 'Track your publication reach, total article views, reader engagements, and community feedback across all your published pieces.',
                    'side' => 'bottom',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/contribute/hub-posts"], .fi-sidebar-item:has(a[href*="hub-posts"])',
                'popover' => [
                    'title' => 'Articles & Tutorials Studio',
                    'description' => 'Draft and edit community articles, technical tutorials, and educational posts using rich markdown and code formatting.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/contribute/hub-posts/create"], a[href*="/contribute/hub-posts"], .fi-sidebar-item:has(a[href*="hub-posts"])',
                'popover' => [
                    'title' => 'Drafting & Publishing Workflow',
                    'description' => 'Track your articles through the editorial cycle: draft your ideas, submit for review, address editor feedback, and get published.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => 'a[href*="/contribute/settings"], .fi-sidebar-item:has(a[href*="settings"])',
                'popover' => [
                    'title' => 'Author Bio & Profile Settings',
                    'description' => 'Customize your public author bio, avatar, social handles, notification alerts, and login passkeys.',
                    'side' => 'right',
                    'align' => 'start',
                ],
            ],
            [
                'element' => '.fi-topbar form, .fi-topbar input, .fi-topbar',
                'popover' => [
                    'title' => 'Article Search & Quick Navigation',
                    'description' => 'Search existing platform tutorials and resources to find inspiration or cross-reference content.',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
            [
                'element' => '.portal-tour-trigger-btn, .fi-topbar',
                'popover' => [
                    'title' => 'Replay Tour Anytime',
                    'description' => 'Click this Tour button in the top navigation bar at any time to review the content publication workflow. Happy writing!',
                    'side' => 'bottom',
                    'align' => 'end',
                ],
            ],
        ];
    }
}

