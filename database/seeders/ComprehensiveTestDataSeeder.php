<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSection;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\HubPost;
use App\Models\Lesson;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\ResourceVideo;
use App\Models\Review;
use App\Models\User;
use App\Models\XpTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // =========================================================================
        // 1. USERS & PROFILES
        // =========================================================================

        // Admin
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'track' => 'Advanced',
                'is_active' => true,
                'email_verified_at' => $now,
                'bio' => 'Lead think.er HUB platform administrator and director of technology education.',
                'company' => 'think.er HUB Academy',
                'specialty' => 'Academic Operations & Cloud Systems',
            ]
        );

        // Instructors
        $instructor1 = User::query()->updateOrCreate(
            ['email' => 'mwansa@example.com'],
            [
                'name' => 'Dr. Mwansa Chileshe',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'track' => 'Advanced',
                'is_active' => true,
                'email_verified_at' => $now,
                'specialty' => 'Full-Stack Architecture & Cloud Systems',
                'company' => 'think.er HUB Tech Lead',
                'bio' => 'Senior Software Architect and educator with 12+ years building cloud-scale web platforms, distributed microservices, and leading developer bootcamps across Sub-Saharan Africa.',
                'instructor_rating' => 4.95,
                'instructor_review_count' => 14,
                'portfolio_url' => 'https://github.com',
                'github_url' => 'https://github.com',
                'linkedin_url' => 'https://linkedin.com',
            ]
        );

        $instructor2 = User::query()->updateOrCreate(
            ['email' => 'kondwani@example.com'],
            [
                'name' => 'Eng. Kondwani Banda',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'track' => 'Advanced',
                'is_active' => true,
                'email_verified_at' => $now,
                'specialty' => 'Machine Learning & Big Data Engineering',
                'company' => 'AfriAI Research Labs',
                'bio' => 'Data Science Researcher specializing in applied machine learning, computer vision pipelines, predictive analytics, and scalable data warehousing with Python and SQL.',
                'instructor_rating' => 4.90,
                'instructor_review_count' => 11,
                'github_url' => 'https://github.com',
                'linkedin_url' => 'https://linkedin.com',
            ]
        );

        $instructor3 = User::query()->updateOrCreate(
            ['email' => 'natasha@example.com'],
            [
                'name' => 'Natasha Zulu',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'track' => 'Intermediate',
                'is_active' => true,
                'email_verified_at' => $now,
                'specialty' => 'Defensive Cybersecurity & Ethical Hacking',
                'company' => 'SecureNet Africa',
                'bio' => 'Certified Information Systems Security Professional (CISSP) & CEH instructor with experience securing financial institutions, cloud workloads, and defensive SOC environments.',
                'instructor_rating' => 4.88,
                'instructor_review_count' => 9,
                'linkedin_url' => 'https://linkedin.com',
            ]
        );

        // Parent
        $parent = User::query()->updateOrCreate(
            ['email' => 'parent.mulenga@example.com'],
            [
                'name' => 'Mr. Joseph Mulenga',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'track' => 'Beginner',
                'is_active' => true,
                'email_verified_at' => $now,
                'whatsapp' => '+260977112233',
                'occupation' => 'Financial Analyst & Parent',
            ]
        );

        // Junior Kids (Supervised)
        $juniorKid1 = User::query()->updateOrCreate(
            ['email' => 'junior.mulenga@example.com'],
            [
                'name' => 'Junior Mulenga',
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_kid' => true,
                'unique_code' => 'THK-KID-4821',
                'age' => 11,
                'school' => 'Baobab College Lusaka',
                'grade' => 'Grade 6',
                'track' => 'Beginner',
                'is_active' => true,
                'email_verified_at' => $now,
                'lifetime_xp' => 420,
                'spendable_coins' => 150,
                'current_streak' => 3,
            ]
        );

        $juniorKid2 = User::query()->updateOrCreate(
            ['email' => 'chileshe.mulenga@example.com'],
            [
                'name' => 'Chileshe Mulenga',
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_kid' => true,
                'unique_code' => 'THK-KID-4822',
                'age' => 14,
                'school' => 'Baobab College Lusaka',
                'grade' => 'Grade 9',
                'track' => 'Intermediate',
                'is_active' => true,
                'email_verified_at' => $now,
                'lifetime_xp' => 760,
                'spendable_coins' => 280,
                'current_streak' => 6,
            ]
        );

        // Link parent & kids
        DB::table('parent_kids')->updateOrInsert(
            ['parent_id' => $parent->id, 'kid_id' => $juniorKid1->id],
            ['relationship' => 'Father', 'age' => 11, 'school' => 'Baobab College Lusaka', 'grade' => 'Grade 6', 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('parent_kids')->updateOrInsert(
            ['parent_id' => $parent->id, 'kid_id' => $juniorKid2->id],
            ['relationship' => 'Father', 'age' => 14, 'school' => 'Baobab College Lusaka', 'grade' => 'Grade 9', 'created_at' => $now, 'updated_at' => $now]
        );

        // Adult Students
        $students = [
            'alex' => User::query()->updateOrCreate(
                ['email' => 'student@example.com'],
                [
                    'name' => 'Alex Mwape',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Intermediate',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 1250,
                    'spendable_coins' => 450,
                    'current_streak' => 5,
                    'bio' => 'Aspiring backend engineer passionate about Laravel and cloud infrastructure.',
                ]
            ),
            'thandiwe' => User::query()->updateOrCreate(
                ['email' => 'thandiwe@example.com'],
                [
                    'name' => 'Thandiwe Mwale',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Beginner',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 580,
                    'spendable_coins' => 200,
                    'current_streak' => 4,
                    'bio' => 'Transitioning from accounting into software development.',
                ]
            ),
            'sipho' => User::query()->updateOrCreate(
                ['email' => 'sipho@example.com'],
                [
                    'name' => 'Sipho Daka',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Advanced',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 2450,
                    'spendable_coins' => 920,
                    'current_streak' => 9,
                    'bio' => 'Data analyst upskilling in deep learning and Python computer vision.',
                ]
            ),
            'bwalya' => User::query()->updateOrCreate(
                ['email' => 'bwalya@example.com'],
                [
                    'name' => 'Bwalya Tembo',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Intermediate',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 980,
                    'spendable_coins' => 310,
                    'current_streak' => 3,
                ]
            ),
            'chisomo' => User::query()->updateOrCreate(
                ['email' => 'chisomo@example.com'],
                [
                    'name' => 'Chisomo Phiri',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Beginner',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 640,
                    'spendable_coins' => 190,
                    'current_streak' => 2,
                ]
            ),
            'lubinda' => User::query()->updateOrCreate(
                ['email' => 'lubinda@example.com'],
                [
                    'name' => 'Lubinda Lubinda',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'track' => 'Advanced',
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'lifetime_xp' => 3200,
                    'spendable_coins' => 1100,
                    'current_streak' => 14,
                    'bio' => 'Computer Science senior at UNZA and STEM scholarship recipient.',
                ]
            ),
        ];

        // =========================================================================
        // 2. COURSES & STRUCTURED REQUIREMENTS
        // =========================================================================

        $coursesData = [
            [
                'code' => 'FSW-201',
                'title' => 'Full-Stack Web Development Bootcamp',
                'slug' => 'full-stack-web-development-bootcamp',
                'fees' => 2500,
                'course_by' => 'Dr. Mwansa Chileshe',
                'offering_mode' => 'once_off',
                'level_progression' => 'Intermediate',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => false,
                'average_rating' => 4.92,
                'review_count' => 18,
                'overview' => 'Master modern web development from relational databases to frontend UI with Laravel, Tailwind, and Vue.',
                'timeline' => '12 Weeks (Saturdays 09:00 - 13:00 + Midweek Lab)',
                'key_outcome' => 'Deploy 3 full-stack portfolio applications with authentication, payment gateways, and cloud CI/CD.',
                'description' => 'Master modern web application architecture from front-to-back. This intensive, project-driven bootcamp takes learners through deep principles of responsive frontend development with Tailwind CSS and Vue/Blade, paired with enterprise-grade backend APIs in Laravel and PostgreSQL. You will build end-to-end applications including real-time authentication, multi-tenant databases, transactional billing webhooks, and scalable cloud deployments on Linux VPS. Designed for developers eager to bridge the gap between hobbyist scripting and production-ready engineering, the curriculum emphasizes automated testing, git team branching, clean architecture, and defensive security practices. By the end of this course, you will have completed three production-grade portfolio applications and earned an industry-recognized certificate.',
                'requirements' => "## Hardware\n- Laptop or desktop computer with at least 8GB RAM (16GB recommended)\n- Minimum 40GB free SSD storage for local containers and development tools\n- Dual-core 64-bit processor (Intel Core i5, AMD Ryzen 5, or Apple Silicon M-series)\n- Reliable internet connectivity for live cohort workshops and package downloads\n\n## Knowledge\n- Solid grasp of foundational HTML tags, CSS styling properties, and responsive layout concepts\n- Basic programming understanding (variables, arrays, functions, and control flow)\n- Familiarity with the terminal or command prompt command navigation\n\n## Software\n- Visual Studio Code or PhpStorm (community or professional)\n- Git version control installed and linked to an active GitHub profile\n- PHP 8.2+ runtime environment with Composer dependency manager\n- Node.js LTS (v20+) and npm\n- Docker Desktop or local PostgreSQL / MySQL database server",
            ],
            [
                'code' => 'DS-301',
                'title' => 'Python for Data Science & Machine Learning',
                'slug' => 'python-for-data-science-machine-learning',
                'fees' => 3000,
                'course_by' => 'Eng. Kondwani Banda',
                'offering_mode' => 'once_off',
                'level_progression' => 'Advanced',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => false,
                'average_rating' => 4.88,
                'review_count' => 12,
                'overview' => 'Transform raw enterprise data into predictive insights using Python, NumPy, Pandas, Scikit-Learn, and PyTorch.',
                'timeline' => '10 Weeks (Sundays 14:00 - 18:00)',
                'key_outcome' => 'Design predictive ML classification models, time-series forecasts, and interactive Streamlit analytics dashboards.',
                'description' => 'Dive deep into modern data science, exploratory data analysis, and predictive machine learning. Starting from statistical foundations and vector mathematics, you will manipulate multi-gigabyte datasets with Pandas and NumPy, build automated data ingestion pipelines, and engineer high-signal features. The course then transitions into core machine learning algorithms including gradient boosted trees, clustering, and neural network classification. Students build end-to-end predictive applications, validate models against data drift, and deploy interactive visualization dashboards that communicate actionable insights to executive stakeholders.',
                'requirements' => "## Hardware\n- Modern laptop or PC with minimum 16GB RAM for efficient in-memory dataset manipulation\n- At least 60GB free disk space for Jupyter environments, datasets, and model checkpoints\n- Multi-core Intel Core i7 / AMD Ryzen 7 or Apple Silicon M-series (dedicated GPU is a bonus)\n- Stable high-speed internet connection for cloud notebooks (Google Colab / Kaggle)\n\n## Knowledge\n- Prior experience writing Python scripts, functions, and working with basic data structures\n- Understanding of fundamental high-school algebra and statistics (mean, variance, probability)\n- Experience working with structured tabular data or spreadsheet formulas\n\n## Software\n- Anaconda distribution or standalone Python 3.11+\n- JupyterLab / Jupyter Notebook and VS Code with Python extensions\n- Git command-line client\n- SQLite or PostgreSQL client (e.g. DBeaver or pgAdmin)",
            ],
            [
                'code' => 'SEC-101',
                'title' => 'Practical Cybersecurity & Ethical Hacking',
                'slug' => 'practical-cybersecurity-ethical-hacking',
                'fees' => 2800,
                'course_by' => 'Natasha Zulu',
                'offering_mode' => 'once_off',
                'level_progression' => 'Intermediate',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => false,
                'average_rating' => 4.95,
                'review_count' => 15,
                'overview' => 'Hands-on offensive penetration testing, network defense, threat analysis, and vulnerability mitigation.',
                'timeline' => '8 Weeks (Tuesday & Thursday Evenings)',
                'key_outcome' => 'Conduct comprehensive vulnerability assessments and secure infrastructure against modern OWASP top 10 vectors.',
                'description' => 'Develop vital defensive and offensive cybersecurity proficiencies through intensive virtual lab scenarios. Learners explore network topology mapping, packet inspection with Wireshark, port scanning with Nmap, and systematic vulnerability assessment. You will analyze and exploit web application weaknesses following OWASP standards, uncover misconfigured identity systems, and practice defensive incident response, log auditing, and firewall rule orchestration to neutralize cyber attacks in real time.',
                'requirements' => "## Hardware\n- 64-bit computer supporting hardware virtualization (VT-x / AMD-V enabled in BIOS)\n- Minimum 16GB RAM (to comfortably run 2 simultaneous virtual machines)\n- Minimum 80GB free SSD storage space for Kali Linux and Metasploitable virtual disks\n- External USB Wi-Fi adapter supporting monitor mode (optional for wireless labs)\n\n## Knowledge\n- Solid understanding of TCP/IP networking, subnets, DNS, and HTTP/HTTPS protocol headers\n- Comfort with Linux terminal commands (bash shell, permissions, piping, process management)\n- High ethical commitment and understanding of responsible disclosure ethics\n\n## Software\n- VirtualBox (v7+) or VMware Workstation Player\n- Kali Linux Virtual Machine image (pre-configured)\n- Wireshark, Nmap, and Burp Suite Community Edition\n- OpenVPN client for secure access to remote practice ranges",
            ],
            [
                'code' => 'KID-101',
                'title' => 'Junior Coders: Scratch & Creative Game Lab',
                'slug' => 'junior-coders-scratch-creative-game-lab',
                'fees' => 1500,
                'course_by' => 'Dr. Mwansa Chileshe',
                'offering_mode' => 'once_off',
                'level_progression' => 'Beginner',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => true,
                'average_rating' => 5.00,
                'review_count' => 10,
                'overview' => 'An engaging visual programming adventure empowering young innovators (ages 9-15) to build animated stories and games.',
                'timeline' => '6 Weeks (Saturday Mornings 10:00 - 11:30)',
                'key_outcome' => 'Build 4 playable computer games, share interactive animations, and master logical computational thinking.',
                'description' => 'Ignite your child’s creative confidence and problem-solving skills through block-based visual coding! In this fun, highly interactive course, young learners are introduced to the core concepts of computer science using MIT Scratch and beginner-friendly game mechanics. Under the patient guidance of certified youth technology tutors, students create maze games, space dodgers, animated greeting cards, and musical synthesizers while learning loops, conditions, variables, and coordinate math.',
                'requirements' => "## Hardware\n- Any laptop, desktop, Chromebook, or iPad/tablet with a keyboard\n- Working webcam and microphone for live interactive weekend sessions\n- Stable home Wi-Fi connection\n\n## Knowledge\n- Curiosity to create and experiment!\n- Basic reading ability and comfort using a computer mouse or trackpad\n- No prior programming experience required\n\n## Software\n- Google Chrome, Microsoft Edge, or Safari browser\n- Free MIT Scratch 3.0 online account (guided setup during Session 1)\n- Zoom Desktop Client installed",
            ],
            [
                'code' => 'UIX-101',
                'title' => 'Foundations of UI/UX Design & Product Prototyping',
                'slug' => 'foundations-ui-ux-design-product-prototyping',
                'fees' => 1800,
                'course_by' => 'Dr. Mwansa Chileshe',
                'offering_mode' => 'once_off',
                'level_progression' => 'Beginner',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => false,
                'average_rating' => 4.85,
                'review_count' => 8,
                'overview' => 'Learn user research, wireframing, design systems, and clickable micro-interactions in Figma.',
                'timeline' => '6 Weeks (Fridays 17:30 - 20:30)',
                'key_outcome' => 'Produce high-fidelity clickable mobile & web prototypes with complete responsive component libraries.',
                'description' => 'Transform digital concepts into beautiful, intuitive user experiences. This practical design course guides students through the complete UX lifecycle: defining user personas, customer journey mapping, structural wireframing, accessibility contrast standards, and high-fidelity UI aesthetics. You will build comprehensive design systems using Figma Auto-Layout, dynamic components, and interactive variants, culminating in a polished portfolio case study ready for client presentation.',
                'requirements' => "## Hardware\n- Laptop or desktop computer with minimum 8GB RAM\n- External mouse or precision trackpad\n- Standard high-definition display\n\n## Knowledge\n- Keen eye for visual aesthetics and empathy for end-user frustrations\n- Basic comfort navigating cloud software applications\n\n## Software\n- Figma desktop app or Google Chrome browser\n- Free Figma Education / Starter account",
            ],
            [
                'code' => 'CLD-401',
                'title' => 'Cloud Engineering & DevOps with Docker & AWS',
                'slug' => 'cloud-engineering-devops-docker-aws',
                'fees' => 3500,
                'course_by' => 'Dr. Mwansa Chileshe',
                'offering_mode' => 'once_off',
                'level_progression' => 'Advanced',
                'is_active' => true,
                'is_open_enrollment' => true,
                'is_for_kids' => false,
                'average_rating' => 4.90,
                'review_count' => 7,
                'overview' => 'Automate infrastructure, containerize applications, and build automated CI/CD pipelines.',
                'timeline' => '8 Weeks (Wednesday Evenings & Saturday Afternoons)',
                'key_outcome' => 'Architect automated zero-downtime deployment pipelines using Docker, GitHub Actions, and AWS ECS/Terraform.',
                'description' => 'Bridge software engineering and production cloud reliability. In this advanced DevOps lab, students master Linux system administration, containerization with Docker and Docker Compose, infrastructure provisioning with Terraform, and automated testing/deployment pipelines with GitHub Actions. You will configure SSL, reverse proxies with NGINX, centralized logging, and production monitoring for high-availability cloud applications.',
                'requirements' => "## Hardware\n- 64-bit computer with at least 16GB RAM and 50GB available disk space\n- Unrestricted internet access for downloading base container images\n\n## Knowledge\n- Strong command line proficiency in Linux/Unix\n- Prior experience building backend web applications or microservices\n\n## Software\n- Docker Desktop & Docker Compose v2\n- AWS CLI configured with free-tier account\n- Terraform CLI and Git",
            ],
        ];

        $createdCourses = [];

        foreach ($coursesData as $c) {
            $course = Course::query()->updateOrCreate(
                ['code' => $c['code']],
                $c
            );
            $createdCourses[$c['code']] = $course;
        }

        // =========================================================================
        // 3. INTAKES & COHORTS
        // =========================================================================

        $createdIntakes = [];

        // FSW-201 Intakes
        $createdIntakes['fsw_active'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['FSW-201']->id, 'name' => 'Cohort Alpha 2026 (Weekend Intensive)'],
            [
                'start_date' => $now->copy()->subWeeks(4)->toDateString(),
                'end_date' => $now->copy()->addWeeks(8)->toDateString(),
                'registration_deadline' => $now->copy()->subWeeks(4)->subDays(3)->toDateString(),
                'status' => CourseIntake::STATUS_ACTIVE,
                'is_active' => true,
                'max_capacity' => 25,
                'notes' => 'Primary weekend cohort. In-person & live stream.',
            ]
        );

        $createdIntakes['fsw_upcoming'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['FSW-201']->id, 'name' => 'Cohort Beta 2026 (Evening Track)'],
            [
                'start_date' => $now->copy()->addWeeks(3)->toDateString(),
                'end_date' => $now->copy()->addWeeks(15)->toDateString(),
                'registration_deadline' => $now->copy()->addWeeks(2)->toDateString(),
                'status' => CourseIntake::STATUS_UPCOMING,
                'is_active' => false,
                'max_capacity' => 30,
                'notes' => 'Open enrollment cohort starting next month.',
            ]
        );

        // DS-301 Intakes
        $createdIntakes['ds_active'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['DS-301']->id, 'name' => 'Data Science Cohort 1 - 2026'],
            [
                'start_date' => $now->copy()->subWeeks(2)->toDateString(),
                'end_date' => $now->copy()->addWeeks(8)->toDateString(),
                'registration_deadline' => $now->copy()->subWeeks(2)->subDays(2)->toDateString(),
                'status' => CourseIntake::STATUS_ACTIVE,
                'is_active' => true,
                'max_capacity' => 20,
                'notes' => 'Sunday cohort with dedicated Google Colab cloud GPU credits.',
            ]
        );

        $createdIntakes['ds_upcoming'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['DS-301']->id, 'name' => 'Data Science Cohort 2 - Mid Year'],
            [
                'start_date' => $now->copy()->addWeeks(6)->toDateString(),
                'end_date' => $now->copy()->addWeeks(16)->toDateString(),
                'registration_deadline' => $now->copy()->addWeeks(5)->toDateString(),
                'status' => CourseIntake::STATUS_UPCOMING,
                'is_active' => false,
                'max_capacity' => 25,
            ]
        );

        // SEC-101 Intakes
        $createdIntakes['sec_active'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['SEC-101']->id, 'name' => 'Cyber Defense Cohort 1'],
            [
                'start_date' => $now->copy()->subWeeks(3)->toDateString(),
                'end_date' => $now->copy()->addWeeks(5)->toDateString(),
                'registration_deadline' => $now->copy()->subWeeks(3)->subDays(4)->toDateString(),
                'status' => CourseIntake::STATUS_ACTIVE,
                'is_active' => true,
                'max_capacity' => 20,
                'notes' => 'Includes access to private Kali range VPN.',
            ]
        );

        // KID-101 Intakes
        $createdIntakes['kid_active'] = CourseIntake::query()->updateOrCreate(
            ['course_id' => $createdCourses['KID-101']->id, 'name' => 'Junior Scratch Squad - Term 1'],
            [
                'start_date' => $now->copy()->subWeeks(1)->toDateString(),
                'end_date' => $now->copy()->addWeeks(5)->toDateString(),
                'registration_deadline' => $now->copy()->subWeeks(1)->toDateString(),
                'status' => CourseIntake::STATUS_ACTIVE,
                'is_active' => true,
                'max_capacity' => 15,
                'notes' => 'Ages 9-15. Weekend mornings.',
            ]
        );

        // =========================================================================
        // 4. CURRICULUM: SECTIONS, LESSONS & SESSIONS
        // =========================================================================

        $fswCourse = $createdCourses['FSW-201'];

        // Section 1
        $sec1 = CourseSection::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => 'Module 1: Modern Web Foundations & Git Workflows'],
            ['description' => 'Environment configuration, semantic HTML, responsive layout principles, and collaborative team git.', 'order_column' => 1]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => '1.1 Local Dev Setup: PHP 8.4, Composer, and VS Code'],
            [
                'course_section_id' => $sec1->id,
                'slug' => 'fsw-1-1-local-dev-setup',
                'description' => 'Configuring your local workstation with modern PHP tooling, Composer, extensions, and shell shortcuts.',
                'youtube_url' => 'https://www.youtube.com/watch?v=OK_JCtrrv-c',
                'duration_seconds' => 1450,
                'is_published' => true,
                'sort_order' => 1,
                'order_column' => 1,
                'type' => 'video',
                'reading_body' => "## Welcome to Module 1\nIn this lesson, you will configure your local development machine with PHP 8.4, Composer, and VS Code extensions for syntax highlighting and linting.",
            ]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => '1.2 Git Branching, Merge Requests, and Conflict Resolution'],
            [
                'course_section_id' => $sec1->id,
                'slug' => 'fsw-1-2-git-branching',
                'description' => 'Mastering git feature branching, pull request reviews, rebasing, and resolving merge conflicts cleanly.',
                'youtube_url' => 'https://www.youtube.com/watch?v=RGOj5yH7evk',
                'duration_seconds' => 1800,
                'is_published' => true,
                'sort_order' => 2,
                'order_column' => 2,
                'type' => 'video',
            ]
        );

        // Section 2
        $sec2 = CourseSection::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => 'Module 2: Laravel Architecture, Eloquent & Migrations'],
            ['description' => 'MVC architecture, relational database modeling, Eloquent relationships, and database seeders.', 'order_column' => 2]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => '2.1 Routing, Controllers & Blade Templating Engine'],
            [
                'course_section_id' => $sec2->id,
                'slug' => 'fsw-2-1-routing-controllers-blade',
                'description' => 'Deep dive into Laravel RESTful routing, resource controllers, and reusable Blade component hierarchies.',
                'youtube_url' => 'https://www.youtube.com/watch?v=MYyJ4PuL4pY',
                'duration_seconds' => 2200,
                'is_published' => true,
                'sort_order' => 3,
                'order_column' => 3,
                'type' => 'video',
            ]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => '2.2 Advanced Eloquent: Relationships, Scopes & Query Optimization'],
            [
                'course_section_id' => $sec2->id,
                'slug' => 'fsw-2-2-advanced-eloquent',
                'description' => 'Tuning queries, preventing N+1 problems, polymorphic relationships, and database transactions.',
                'youtube_url' => 'https://www.youtube.com/watch?v=376vZ1qNYPA',
                'duration_seconds' => 2600,
                'is_published' => true,
                'sort_order' => 4,
                'order_column' => 4,
                'type' => 'video',
            ]
        );

        // Section 3
        $sec3 = CourseSection::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => 'Module 3: Authentication, Payments & Cloud Deployment'],
            ['description' => 'Securing APIs, processing payments via Lenco webhooks, and zero-downtime deployment on Linux VPS.', 'order_column' => 3]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => '3.1 Integrating Mobile Money & Card Payments with Webhooks'],
            [
                'course_section_id' => $sec3->id,
                'slug' => 'fsw-3-1-payment-gateways-webhooks',
                'description' => 'Building resilient checkout flows, idempotent webhook handlers, and transactional ledger accounting.',
                'youtube_url' => 'https://www.youtube.com/watch?v=1ojA5mLUn3k',
                'duration_seconds' => 3100,
                'is_published' => true,
                'sort_order' => 5,
                'order_column' => 5,
                'type' => 'video',
            ]
        );

        // Course Sessions (Live Interactive Classes)
        CourseSession::query()->updateOrCreate(
            ['course_id' => $fswCourse->id, 'title' => 'Cohort Alpha Live Workshop: Architecture Review & Q&A'],
            [
                'course_intake_id' => $createdIntakes['fsw_active']->id,
                'instructor_id' => $instructor1->id,
                'session_date' => $now->copy()->addDays(2)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'meeting_link' => 'https://meet.google.com/thk-fsw-live',
                'delivery_mode' => 'online',
                'notes' => 'Live code review of Module 2 assignments, database schema normalization, and live debugging session.',
            ]
        );

        // =========================================================================
        // 5. ENROLLMENTS & ACCOUNTING PAYMENTS (THE CORE FINANCIAL LEDGER)
        // =========================================================================

        $enrollmentSpecs = [
            // 1. Direct Checkout: Thandiwe Mwale (FSW-201, Airtel Money, Paid)
            [
                'user' => $students['thandiwe'],
                'course' => $createdCourses['FSW-201'],
                'intake' => $createdIntakes['fsw_active'],
                'channel' => Enrollment::CHANNEL_DIRECT_CHECKOUT,
                'status' => Enrollment::STATUS_PAID,
                'is_free' => false,
                'amount' => 2500.00,
                'method' => 'mobile_money',
                'provider' => 'Airtel Money',
                'ref' => 'LENCO-TX-984712',
                'notes' => 'Self-service online enrollment via Lenco Airtel Money gateway.',
                'paid_at' => $now->copy()->subDays(12),
                'registered_by' => null,
            ],

            // 2. Direct Checkout: Sipho Daka (DS-301, Visa Card, Paid)
            [
                'user' => $students['sipho'],
                'course' => $createdCourses['DS-301'],
                'intake' => $createdIntakes['ds_active'],
                'channel' => Enrollment::CHANNEL_DIRECT_CHECKOUT,
                'status' => Enrollment::STATUS_PAID,
                'is_free' => false,
                'amount' => 3000.00,
                'method' => 'card',
                'provider' => 'Visa Card / Lenco',
                'ref' => 'LENCO-TX-847291',
                'notes' => 'Online checkout via debit card.',
                'paid_at' => $now->copy()->subDays(9),
                'registered_by' => null,
            ],

            // 3. Direct Checkout: Parent Mulenga -> Junior Mulenga (KID-101, MTN MoMo, Paid)
            [
                'user' => $juniorKid1,
                'course' => $createdCourses['KID-101'],
                'intake' => $createdIntakes['kid_active'],
                'channel' => Enrollment::CHANNEL_DIRECT_CHECKOUT,
                'status' => Enrollment::STATUS_PAID,
                'is_free' => false,
                'amount' => 1500.00,
                'method' => 'mobile_money',
                'provider' => 'MTN Mobile Money',
                'ref' => 'LENCO-TX-773412',
                'notes' => 'Parent Joseph Mulenga enrolled Junior Mulenga via parent checkout.',
                'paid_at' => $now->copy()->subDays(5),
                'registered_by' => null,
            ],

            // 4. Admin Manual Registration (Paying): Bwalya Tembo (SEC-101, Bank Transfer, Paid)
            [
                'user' => $students['bwalya'],
                'course' => $createdCourses['SEC-101'],
                'intake' => $createdIntakes['sec_active'],
                'channel' => Enrollment::CHANNEL_ADMIN_MANUAL,
                'status' => Enrollment::STATUS_PAID,
                'is_free' => false,
                'amount' => 2800.00,
                'method' => 'bank_transfer',
                'provider' => 'Stanbic Bank Zambia',
                'ref' => 'TX-STB-2026-8812',
                'notes' => 'Full tuition paid via Stanbic Bank direct deposit. Reconciled against verified bank slip #STB-4412.',
                'paid_at' => $now->copy()->subDays(4),
                'registered_by' => $admin->id,
            ],

            // 5. Admin Manual Registration (Paying): Chisomo Phiri (FSW-201, Cash Receipt, Paid)
            [
                'user' => $students['chisomo'],
                'course' => $createdCourses['FSW-201'],
                'intake' => $createdIntakes['fsw_active'],
                'channel' => Enrollment::CHANNEL_ADMIN_MANUAL,
                'status' => Enrollment::STATUS_PAID,
                'is_free' => false,
                'amount' => 2500.00,
                'method' => 'cash',
                'provider' => 'think.er HUB Campus Finance Desk',
                'ref' => 'RCPT-CASH-1092',
                'notes' => 'Student paid in full at campus reception finance desk. Physical receipt issued.',
                'paid_at' => $now->copy()->subDays(2),
                'registered_by' => $admin->id,
            ],

            // 6. Admin Manual Registration (Free): Lubinda Lubinda (DS-301, 100% STEM Scholarship, Free)
            [
                'user' => $students['lubinda'],
                'course' => $createdCourses['DS-301'],
                'intake' => $createdIntakes['ds_active'],
                'channel' => Enrollment::CHANNEL_ADMIN_MANUAL,
                'status' => Enrollment::STATUS_FREE,
                'is_free' => true,
                'amount' => 0.00,
                'method' => 'scholarship',
                'provider' => 'Academic Board Waiver',
                'ref' => 'SCHOLAR-STEM-2026-09',
                'notes' => 'Awarded 100% STEM Merit Scholarship for outstanding university academic achievement approved by Academic Director.',
                'paid_at' => $now->copy()->subDays(8),
                'registered_by' => $admin->id,
            ],

            // 7. Instructor Manual Registration (Free): Alex Mwape (FSW-201, TA / Peer Mentor Waiver, Free)
            [
                'user' => $students['alex'],
                'course' => $createdCourses['FSW-201'],
                'intake' => $createdIntakes['fsw_active'],
                'channel' => Enrollment::CHANNEL_INSTRUCTOR_MANUAL,
                'status' => Enrollment::STATUS_FREE,
                'is_free' => true,
                'amount' => 0.00,
                'method' => 'complimentary',
                'provider' => 'Instructor Grant',
                'ref' => 'TA-GRANT-2026-MWANSA',
                'notes' => 'Enrolled by Lead Instructor Dr. Mwansa Chileshe as Teaching Assistant and Peer Mentor for Cohort Alpha.',
                'paid_at' => $now->copy()->subDays(15),
                'registered_by' => $instructor1->id,
            ],

            // 8. Admin Manual Registration (Free): Chileshe Mulenga (UIX-101, Youth Talent Sponsorship)
            [
                'user' => $juniorKid2,
                'course' => $createdCourses['UIX-101'],
                'intake' => null,
                'channel' => Enrollment::CHANNEL_ADMIN_MANUAL,
                'status' => Enrollment::STATUS_FREE,
                'is_free' => true,
                'amount' => 0.00,
                'method' => 'sponsorship',
                'provider' => 'Youth Tech Initiative',
                'ref' => 'YOUTH-SPONSOR-2026-03',
                'notes' => 'Sponsored under the Youth Innovation Fellowship 2026.',
                'paid_at' => $now->copy()->subDays(6),
                'registered_by' => $admin->id,
            ],
        ];

        foreach ($enrollmentSpecs as $spec) {
            $enrollment = Enrollment::query()->updateOrCreate(
                [
                    'user_id' => $spec['user']->id,
                    'course_id' => $spec['course']->id,
                ],
                [
                    'course_intake_id' => $spec['intake']?->id,
                    'registration_type' => $spec['channel'],
                    'payment_status' => $spec['status'],
                    'is_free' => $spec['is_free'],
                    'fee_amount' => $spec['amount'],
                    'payment_method' => $spec['method'],
                    'transaction_reference' => $spec['ref'],
                    'payment_notes' => $spec['notes'],
                    'registered_by_user_id' => $spec['registered_by'],
                    'paid_at' => $spec['paid_at'],
                    'created_at' => $spec['paid_at'],
                    'updated_at' => $now,
                ]
            );

            // Create corresponding ledger entry in payments table
            Payment::query()->updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'user_id' => $spec['user']->id,
                    'course_id' => $spec['course']->id,
                    'amount' => $spec['amount'],
                    'original_amount' => $spec['course']->fees ?? $spec['amount'],
                    'discount_amount' => $spec['is_free'] ? ($spec['course']->fees ?? 0) : 0,
                    'currency' => 'ZMW',
                    'payment_method' => $spec['method'],
                    'provider' => $spec['provider'],
                    'reference' => $spec['ref'],
                    'status' => 'paid',
                    'is_free' => $spec['is_free'],
                    'registration_type' => $spec['channel'],
                    'registered_by_user_id' => $spec['registered_by'],
                    'payment_notes' => $spec['notes'],
                    'paid_at' => $spec['paid_at'],
                    'created_at' => $spec['paid_at'],
                    'updated_at' => $now,
                ]
            );
        }

        // =========================================================================
        // 6. RESOURCE VIDEOS (LIBRARY & STREAM ARCHIVE)
        // =========================================================================

        $resourceVideosData = [
            [
                'title' => 'Masterclass: Modern Full-Stack Architecture in 2026',
                'channel_name' => 'think.er HUB / Dr. Mwansa Chileshe',
                'category' => 'Tutorials',
                'youtube_url' => 'https://www.youtube.com/watch?v=OK_JCtrrv-c',
                'course_id' => $createdCourses['FSW-201']->id,
                'target_level' => 'Intermediate',
                'is_recorded_lesson' => false,
                'is_published' => true,
                'sort_order' => 1,
                'description' => 'A comprehensive deep dive into microservice design, reactive frontend state, and high-performance Laravel APIs.',
            ],
            [
                'title' => 'Cohort Alpha - Week 1: Environment Setup, Docker & Git Workflow',
                'channel_name' => 'think.er HUB Live Archive',
                'category' => 'Recorded Lessons',
                'youtube_url' => 'https://www.youtube.com/watch?v=RGOj5yH7evk',
                'course_id' => $createdCourses['FSW-201']->id,
                'course_intake_id' => $createdIntakes['fsw_active']->id,
                'target_level' => 'Intermediate',
                'is_recorded_lesson' => true,
                'is_published' => true,
                'sort_order' => 2,
                'description' => 'Official recorded session of Cohort Alpha Week 1 covering container setup, Composer autoloader, and team git standards.',
            ],
            [
                'title' => 'Cohort Alpha - Week 2: Eloquent ORM & Complex Relational Schemas',
                'channel_name' => 'think.er HUB Live Archive',
                'category' => 'Recorded Lessons',
                'youtube_url' => 'https://www.youtube.com/watch?v=376vZ1qNYPA',
                'course_id' => $createdCourses['FSW-201']->id,
                'course_intake_id' => $createdIntakes['fsw_active']->id,
                'target_level' => 'Intermediate',
                'is_recorded_lesson' => true,
                'is_published' => true,
                'sort_order' => 3,
                'description' => 'Official recorded session exploring index design, polymorphic models, and database constraints.',
            ],
            [
                'title' => 'Python Pandas & NumPy Crash Course for Data Science',
                'channel_name' => 'think.er HUB / Eng. Kondwani Banda',
                'category' => 'Tutorials',
                'youtube_url' => 'https://www.youtube.com/watch?v=vmEHCJofslg',
                'course_id' => $createdCourses['DS-301']->id,
                'target_level' => 'Advanced',
                'is_recorded_lesson' => false,
                'is_published' => true,
                'sort_order' => 4,
                'description' => 'Step-by-step tutorial on cleaning messy real-world CSV and Parquet files using vectorization and query expressions.',
            ],
            [
                'title' => 'Ethical Hacking: Setting Up Your VirtualBox Attack Lab',
                'channel_name' => 'think.er HUB / Natasha Zulu',
                'category' => 'Tutorials',
                'youtube_url' => 'https://www.youtube.com/watch?v=3Kq1MIfTWCE',
                'course_id' => $createdCourses['SEC-101']->id,
                'target_level' => 'Intermediate',
                'is_recorded_lesson' => false,
                'is_published' => true,
                'sort_order' => 5,
                'description' => 'Safely isolate vulnerable machines inside a host-only virtual network with Kali Linux and Wireshark.',
            ],
            [
                'title' => 'Scratch Game Lab: Building a Space Dodger in 30 Minutes',
                'channel_name' => 'Junior Scratch Lab',
                'category' => 'Tutorials',
                'youtube_url' => 'https://www.youtube.com/watch?v=VIpmkeqJhmQ',
                'course_id' => $createdCourses['KID-101']->id,
                'target_level' => 'Beginner',
                'is_recorded_lesson' => false,
                'is_published' => true,
                'sort_order' => 6,
                'description' => 'Fun interactive coding tutorial showing kids how to spawn asteroids, track player lives, and keep score!',
            ],
            [
                'title' => 'Tech Career Playbook: From Junior Dev to Tech Lead in Africa',
                'channel_name' => 'think.er HUB Industry Talks',
                'category' => 'General',
                'youtube_url' => 'https://www.youtube.com/watch?v=1ojA5mLUn3k',
                'course_id' => null,
                'target_level' => 'Beginner',
                'is_recorded_lesson' => false,
                'is_published' => true,
                'sort_order' => 7,
                'description' => 'Fireside discussion on networking, remote freelancing, open source contributions, and technical interview preparation.',
            ],
        ];

        foreach ($resourceVideosData as $v) {
            ResourceVideo::query()->updateOrCreate(
                ['title' => $v['title']],
                $v
            );
        }

        // =========================================================================
        // 7. KNOWLEDGE HUB POSTS & OPPORTUNITIES
        // =========================================================================

        $hubPostsData = [
            [
                'title' => 'Demystifying Database Indexing: Why Your B-Tree Makes Queries 100x Faster',
                'slug' => 'demystifying-database-indexing-b-tree',
                'type' => 'blog',
                'category' => 'Database Engineering',
                'author_id' => $instructor1->id,
                'is_published' => true,
                'excerpt' => 'Understand how composite indexes work under the hood and how to eliminate slow full table scans in PostgreSQL and MySQL.',
                'content' => "Database indexes are often treated like magic bullets. You notice a query taking 1,200ms, run `CREATE INDEX`, and suddenly response times plummet to 8ms.\n\n### The B-Tree Anatomy\nA B-Tree (Balanced Tree) maintains sorted keys with logarithmic search complexity O(log n). Each page in memory contains pointers to child nodes, allowing PostgreSQL to pinpoint matching rows in 3 to 4 disk reads instead of scanning 1,000,000 blocks.\n\n### Beware the Index Tax\nEvery index added slows down `INSERT`, `UPDATE`, and `DELETE` operations because the database engine must synchronously rebalance tree nodes. Always profile with `EXPLAIN ANALYZE` before creating indexes in production.",
                'code_snippet' => "EXPLAIN ANALYZE\nSELECT id, name, created_at \nFROM users \nWHERE role = 'student' AND created_at >= '2026-01-01';",
                'pro_tip' => 'Always order multi-column composite indexes starting with equality filters first, followed by range filters.',
            ],
            [
                'title' => 'Top 5 Security Vulnerabilities in Web APIs and How to Prevent Them',
                'slug' => 'top-5-security-vulnerabilities-in-web-apis',
                'type' => 'blog',
                'category' => 'Cybersecurity',
                'author_id' => $instructor3->id,
                'is_published' => true,
                'excerpt' => 'Broken Object Level Authorization (BOLA), mass assignment, and leaky error payloads remain the most exploited API flaws.',
                'content' => "Modern applications rely heavily on REST and GraphQL APIs. However, moving business logic to client-side single page apps has exposed critical authorization vulnerabilities.\n\n### 1. Broken Object Level Authorization (BOLA)\nNever rely on predictable auto-increment IDs in endpoints without checking tenant ownership:\n`GET /api/v1/invoices/1042`\nEnsure policies rigorously verify `auth()->user()->can('view', \$invoice)`.\n\n### 2. Strict Request Validation\nAvoid unvetted `Model::create(\$request->all())` patterns. Use Form Requests with explicit \$fillable guards.",
                'pro_tip' => 'Implement rate limiting using Redis token buckets to deter credential stuffing attacks.',
            ],
            [
                'title' => 'Getting Started with Machine Learning on Low-Resource Hardware',
                'slug' => 'getting-started-with-ml-low-resource-hardware',
                'type' => 'blog',
                'category' => 'Artificial Intelligence',
                'author_id' => $instructor2->id,
                'is_published' => true,
                'excerpt' => 'You do not need an \$8,000 GPU cluster to start learning machine learning. Here is how to train models efficiently on standard laptops.',
                'content' => "A common myth among aspiring data scientists is that AI requires expensive dedicated GPU rigs. While fine-tuning 70-billion parameter LLMs does, 90% of commercial data science is tabular machine learning where CPU-optimized libraries shine.\n\n### Efficient Tooling\n1. Use Polars or DuckDB instead of memory-heavy Pandas for instant zero-copy aggregations.\n2. Leverage Scikit-Learn with joblib multi-threading.\n3. Run experimental notebook training on free Google Colab or Kaggle cloud kernels with T4 GPUs.",
                'pro_tip' => 'Quantize your models with ONNX Runtime to deploy fast inference on commodity servers.',
            ],
        ];

        foreach ($hubPostsData as $hp) {
            HubPost::query()->updateOrCreate(
                ['slug' => $hp['slug']],
                $hp
            );
        }

        // Opportunities
        $oppsData = [
            [
                'title' => 'Junior Full-Stack Software Developer (Remote / Lusaka)',
                'type' => 'Job',
                'provider' => 'Zambian Fintech Group',
                'is_published' => true,
                'link_url' => 'https://example.com/careers/fintech-junior',
                'description' => 'Exciting opportunity for recent bootcamp graduates and junior developers skilled in PHP/Laravel and Tailwind CSS. Competitive salary and mentorship provided.',
                'expires_at' => $now->copy()->addDays(28),
            ],
            [
                'title' => 'AfriTech Cloud & AI Fellowship 2026',
                'type' => 'Scholarship',
                'provider' => 'AfriAI Foundation',
                'is_published' => true,
                'link_url' => 'https://example.com/fellowships/2026',
                'description' => 'Fully funded 6-month hands-on fellowship covering AWS cloud certification exam vouchers and machine learning project stipends.',
                'expires_at' => $now->copy()->addDays(45),
            ],
            [
                'title' => 'Lusaka DevHacks 2026 Hackathon',
                'type' => 'Hackathon',
                'provider' => 'think.er HUB & Innovation Hub',
                'is_published' => true,
                'link_url' => 'https://example.com/hackathons/lusakadev2026',
                'description' => '48-hour competitive hackathon building climate-smart agricultural technology and open payment systems. Prize pool of ZMW 50,000!',
                'expires_at' => $now->copy()->addDays(18),
            ],
        ];

        foreach ($oppsData as $opp) {
            Opportunity::query()->updateOrCreate(
                ['title' => $opp['title']],
                $opp
            );
        }

        // =========================================================================
        // 8. ASSIGNMENTS, QUIZZES & REVIEWS
        // =========================================================================

        // Assignment for FSW-201
        $fswAssignment = Assignment::query()->updateOrCreate(
            ['name' => 'Project 1: Multi-Tenant Portfolio CMS with Laravel'],
            [
                'course_id' => $fswCourse->id,
                'course_intake_id' => $createdIntakes['fsw_active']->id,
                'scope' => 'all',
                'target_track' => 'Intermediate',
                'description' => 'Architect a robust CMS supporting multiple student portfolios, image uploads, policy-based access control, and responsive Tailwind styling.',
                'due_date' => $now->copy()->addDays(14)->toDateString(),
                'date_given' => $now->copy()->subDays(3)->toDateString(),
            ]
        );

        // Sample submission from Alex
        AssignmentSubmission::query()->updateOrCreate(
            [
                'assignment_id' => $fswAssignment->id,
                'user_id' => $students['alex']->id,
            ],
            [
                'content' => 'Implemented complete tenant separation with scoped query filters, responsive UI, and feature test coverage.',
                'submitted_at' => $now->copy()->subDay(),
                'status' => 'graded',
                'grade' => 95.0,
                'feedback' => 'Superb clean architecture and well-documented migrations. Excellent work Alex!',
            ]
        );

        // Quiz for FSW-201
        $quiz = Quiz::query()->updateOrCreate(
            ['title' => 'Web Dev Fundamentals: PHP 8 & MVC Architecture'],
            [
                'course_id' => $fswCourse->id,
                'course_intake_id' => $createdIntakes['fsw_active']->id,
                'description' => 'Test your core understanding of PHP 8.4 features, dependency injection, and RESTful routing patterns.',
                'time_limit_minutes' => 20,
                'pass_percentage' => 70,
                'is_active' => true,
            ]
        );

        $q1 = QuizQuestion::query()->updateOrCreate(
            ['quiz_id' => $quiz->id, 'question' => 'Which PHP 8 feature allows declaring properties directly inside class constructors?'],
            ['type' => 'multiple_choice', 'points' => 10]
        );

        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q1->id, 'option_text' => 'Constructor Property Promotion'],
            ['is_correct' => true]
        );
        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q1->id, 'option_text' => 'Dynamic Type Hinting'],
            ['is_correct' => false]
        );
        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q1->id, 'option_text' => 'Anonymous Trait Casting'],
            ['is_correct' => false]
        );

        $q2 = QuizQuestion::query()->updateOrCreate(
            ['quiz_id' => $quiz->id, 'question' => 'In Laravel Eloquent, how do you prevent the N+1 database query problem?'],
            ['type' => 'multiple_choice', 'points' => 10]
        );

        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q2->id, 'option_text' => 'By using Eager Loading with the with() method'],
            ['is_correct' => true]
        );
        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q2->id, 'option_text' => 'By running raw SQL SELECT * statements'],
            ['is_correct' => false]
        );
        QuizOption::query()->updateOrCreate(
            ['quiz_question_id' => $q2->id, 'option_text' => 'By disabling foreign key constraints'],
            ['is_correct' => false]
        );

        // Reviews for Courses
        $reviewsData = [
            [
                'user_id' => $students['alex']->id,
                'reviewable_type' => Course::class,
                'reviewable_id' => $createdCourses['FSW-201']->id,
                'rating' => 5,
                'title' => 'Life changing course for anyone serious about engineering!',
                'comment' => 'Dr. Mwansa breaks down complex architectural patterns with infectious enthusiasm. The practical projects directly helped me ace my technical interview!',
                'is_approved' => true,
                'is_verified' => true,
            ],
            [
                'user_id' => $students['thandiwe']->id,
                'reviewable_type' => Course::class,
                'reviewable_id' => $createdCourses['FSW-201']->id,
                'rating' => 5,
                'title' => 'Incredible support for beginners transitioning careers',
                'comment' => 'Coming from a finance background, I was initially intimidated. The cohort environment and supportive tutors made every milestone rewarding.',
                'is_approved' => true,
                'is_verified' => true,
            ],
            [
                'user_id' => $students['sipho']->id,
                'reviewable_type' => Course::class,
                'reviewable_id' => $createdCourses['DS-301']->id,
                'rating' => 5,
                'title' => 'Mastery of real-world datasets and model tuning',
                'comment' => 'Eng. Kondwani does not just teach algorithms; he teaches how to think critically about data anomalies and business impact.',
                'is_approved' => true,
                'is_verified' => true,
            ],
            [
                'user_id' => $parent->id,
                'reviewable_type' => Course::class,
                'reviewable_id' => $createdCourses['KID-101']->id,
                'rating' => 5,
                'title' => 'My son Junior looks forward to Saturday mornings all week!',
                'comment' => 'Outstanding teaching pedagogy for kids. Junior created his first playable space game in 3 weeks and cannot stop showing it to the family.',
                'is_approved' => true,
                'is_verified' => true,
            ],
        ];

        foreach ($reviewsData as $rev) {
            Review::query()->updateOrCreate(
                [
                    'user_id' => $rev['user_id'],
                    'reviewable_type' => $rev['reviewable_type'],
                    'reviewable_id' => $rev['reviewable_id'],
                ],
                $rev
            );
        }

        // =========================================================================
        // 9. XP TRANSACTIONS (GAMIFICATION REWARDS)
        // =========================================================================

        XpTransaction::query()->create([
            'user_id' => $students['alex']->id,
            'course_id' => $fswCourse->id,
            'points' => 250,
            'amount_xp' => 250,
            'amount_coins' => 50,
            'source' => 'assignment',
            'source_id' => $fswAssignment->id,
            'activity_type' => 'assignment_completed',
            'description' => 'Completed Project 1 with score 95/100',
            'created_at' => $now->copy()->subDay(),
        ]);

        XpTransaction::query()->create([
            'user_id' => $students['alex']->id,
            'course_id' => $fswCourse->id,
            'points' => 50,
            'amount_xp' => 50,
            'amount_coins' => 10,
            'source' => 'streak',
            'source_id' => 1,
            'activity_type' => 'daily_streak',
            'description' => 'Maintained 5-day study streak',
            'created_at' => $now,
        ]);
    }
}
