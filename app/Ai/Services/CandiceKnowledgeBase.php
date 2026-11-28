<?php

namespace App\Ai\Services;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\HubPost;
use App\Models\Opportunity;
use App\Models\ResourceVideo;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CandiceKnowledgeBase
{
    public const CACHE_KEY = 'candice.knowledge_base.snapshot';

    private const STOP_WORDS = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'can', 'could', 'do', 'does',
        'for', 'from', 'get', 'have', 'hello', 'hey', 'hi', 'how', 'i', 'in', 'info',
        'information', 'is', 'it', 'know', 'like', 'me', 'more', 'my', 'of', 'on', 'or',
        'please', 'show', 'tell', 'that', 'the', 'there', 'this', 'to', 'us', 'want',
        'what', 'when', 'where', 'which', 'who', 'why', 'with', 'would', 'you', 'your',
        'about', 'any', 'currently', 'available', 'exist', 'work', 'thinker', 'hub',
    ];

    /**
     * Invalidate the cached knowledge base snapshot so the next read reflects new uploads.
     */
    public static function invalidate(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
            // Ignore cache errors during early boot/migrations
        }
    }

    /**
     * Force-rebuild and return the live knowledge base snapshot from the database.
     *
     * @return array{
     *     courses: list<array<string, mixed>>,
     *     opportunities: list<array<string, mixed>>,
     *     articles: list<array<string, mixed>>,
     *     videos: list<array<string, mixed>>,
     *     updated_at: string
     * }
     */
    public static function refresh(): array
    {
        self::invalidate();

        return self::snapshot();
    }

    /**
     * Return the cached knowledge base snapshot (automatically built from live DB tables).
     *
     * @return array{
     *     courses: list<array<string, mixed>>,
     *     opportunities: list<array<string, mixed>>,
     *     articles: list<array<string, mixed>>,
     *     videos: list<array<string, mixed>>,
     *     updated_at: string
     * }
     */
    public static function snapshot(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addHours(12), fn (): array => self::buildSnapshot());
        } catch (Throwable) {
            return self::buildSnapshot();
        }
    }

    /**
     * Build a compact preloaded context block for Candice's live AI system prompt
     * so every newly uploaded course, article, and opportunity is immediately known.
     */
    public static function promptContextSummary(): string
    {
        $snap = self::snapshot();
        $sections = [];

        if ($snap['courses'] !== []) {
            $items = array_map(function (array $c): string {
                $intake = $c['next_intake'] ? " (Next intake: {$c['next_intake']})" : '';
                return "- {$c['title']} [{$c['code']}], Fee: {$c['fees']}{$intake}";
            }, array_slice($snap['courses'], 0, 15));
            $sections[] = "Active Courses:\n" . implode("\n", $items);
        }

        if ($snap['opportunities'] !== []) {
            $items = array_map(
                fn (array $o): string => "- {$o['title']} ({$o['type']} by {$o['provider']})",
                array_slice($snap['opportunities'], 0, 10)
            );
            $sections[] = "Active Opportunities:\n" . implode("\n", $items);
        }

        if ($snap['articles'] !== []) {
            $items = array_map(
                fn (array $a): string => "- {$a['title']} [{$a['category']}] ({$a['url']})",
                array_slice($snap['articles'], 0, 10)
            );
            $sections[] = "Published Hub Articles & Guides:\n" . implode("\n", $items);
        }

        if ($snap['videos'] !== []) {
            $items = array_map(
                fn (array $v): string => "- {$v['title']} [{$v['category']}]",
                array_slice($snap['videos'], 0, 8)
            );
            $sections[] = "Published Learning Videos:\n" . implode("\n", $items);
        }

        if ($sections === []) {
            return '';
        }

        return "Live Auto-Synced Platform Catalog Snapshot (automatically updated on every upload):\n" . implode("\n\n", $sections);
    }

    /**
     * Generate a warm, accurate Markdown response from preloaded institutional knowledge
     * and the auto-synced live database snapshot whenever the live AI model is unavailable.
     */
    public static function answer(string $question): string
    {
        $normalized = strtolower(trim($question));

        if ($normalized === '' || self::matchesAny($normalized, ['^hi\b', '^hello\b', '^hey\b', '^good morning', '^good afternoon', '^good evening', 'who are you', 'what is your name', 'about candice'])) {
            return self::greetingResponse();
        }

        // First check if the visitor asked about a specific uploaded course, article, opportunity, or video by title/topic
        $directMatch = self::searchUploadedContent($normalized);
        if ($directMatch !== null) {
            return $directMatch;
        }

        if (self::matchesAny($normalized, ['tuition', 'fee', 'fees', 'price', 'pricing', 'cost', 'pay', 'payment', 'installment', 'mobile money', 'mtn', 'airtel', 'zamtel', 'promo', 'discount'])) {
            return self::tuitionAndPaymentsResponse();
        }

        if (self::matchesAny($normalized, ['enroll', 'enrol', 'register', 'registration', 'apply', 'admission', 'sign up', 'signup', 'how to join', 'how do i start', 'prerequisite', 'requirement', 'laptop'])) {
            return self::admissionsAndOnboardingResponse();
        }

        if (self::matchesAny($normalized, ['delivery', 'online', 'virtual', 'physical', 'in-person', 'in person', 'campus', 'hybrid', 'zoom', 'video guide', 'self-paced', 'self paced'])) {
            return self::deliveryModesResponse();
        }

        if (self::matchesAny($normalized, ['portal', 'student portal', 'parent portal', 'instructor portal', 'contributor', 'admin portal', 'study buddy', 'dashboard'])) {
            return self::portalsResponse();
        }

        if (self::matchesAny($normalized, ['internship', 'job', 'career', 'opportunity', 'opportunities', 'scholarship', 'hackathon', 'placement'])) {
            return self::opportunitiesResponse();
        }

        if (self::matchesAny($normalized, ['article', 'blog', 'tutorial', 'guide', 'hub', 'tips', 'community', 'video', 'videos'])) {
            return self::articlesResponse();
        }

        if (self::matchesAny($normalized, ['certificate', 'certification', 'credential', 'verify', 'verification'])) {
            return self::certificationsResponse();
        }

        if (self::matchesAny($normalized, ['coin', 'coins', 'xp', 'reward', 'rewards', 'swag', 'claim', 'leaderboard', 'gamification', 'streak'])) {
            return self::gamificationResponse();
        }

        if (self::matchesAny($normalized, ['teach', 'become an instructor', 'instructor application', 'lecturer', 'mentor'])) {
            return self::becomeInstructorResponse();
        }

        if (self::matchesAny($normalized, ['contact', 'email', 'location', 'where are you', 'address', 'lusaka', 'zambia', 'support', 'help desk', 'phone'])) {
            return self::contactResponse();
        }

        if (self::matchesAny($normalized, ['course', 'courses', 'track', 'tracks', 'program', 'programs', 'bootcamp', 'intake', 'intakes', 'kids', 'teens', 'coding'])) {
            return self::coursesResponse($normalized);
        }

        return self::generalOverviewResponse();
    }

    /**
     * Build snapshot arrays from active Courses, Opportunities, HubPosts, and ResourceVideos.
     *
     * @return array{
     *     courses: list<array<string, mixed>>,
     *     opportunities: list<array<string, mixed>>,
     *     articles: list<array<string, mixed>>,
     *     videos: list<array<string, mixed>>,
     *     updated_at: string
     * }
     */
    private static function buildSnapshot(): array
    {
        $courses = [];
        $opportunities = [];
        $articles = [];
        $videos = [];

        try {
            $courseRecords = Course::query()
                ->where('is_active', true)
                ->with(['intakes' => fn ($q) => $q->where('status', '!=', CourseIntake::STATUS_ARCHIVED)->orderBy('start_date')])
                ->latest()
                ->limit(50)
                ->get();

            foreach ($courseRecords as $course) {
                $nextIntake = $course->intakes->first();
                $intakeLabel = $nextIntake
                    ? $nextIntake->name . ($nextIntake->start_date ? ' (' . $nextIntake->start_date->format('M d, Y') . ')' : '')
                    : null;

                $courses[] = [
                    'id' => $course->id,
                    'title' => (string) $course->title,
                    'code' => (string) ($course->code ?? ''),
                    'fees' => $course->fees ? (string) $course->fees : 'Flexible / See details',
                    'timeline' => (string) ($course->timeline ?? 'Self-paced / Cohort'),
                    'for_kids' => (bool) $course->is_for_kids,
                    'description' => trim(strip_tags((string) ($course->description ?: $course->overview ?: ''))),
                    'next_intake' => $intakeLabel,
                ];
            }
        } catch (Throwable) {
            // Table may not exist yet during migrations
        }

        try {
            $oppRecords = Opportunity::query()
                ->active()
                ->latest()
                ->limit(30)
                ->get();

            foreach ($oppRecords as $item) {
                $opportunities[] = [
                    'title' => (string) $item->title,
                    'type' => (string) ($item->type ?: 'Opportunity'),
                    'provider' => (string) ($item->provider ?: 'think.er HUB Partner'),
                    'description' => trim(strip_tags((string) ($item->description ?? ''))),
                    'url' => (string) ($item->link_url ?: '/learn/opportunities'),
                    'expires_at' => $item->expires_at ? $item->expires_at->format('M d, Y') : null,
                ];
            }
        } catch (Throwable) {
            // Table may not exist yet
        }

        try {
            $postRecords = HubPost::query()
                ->where('is_published', true)
                ->latest()
                ->limit(40)
                ->get();

            foreach ($postRecords as $post) {
                $url = '/hub/' . ($post->slug ?: $post->id);
                $excerpt = trim(strip_tags((string) ($post->excerpt ?: $post->content ?: '')));

                if ($post->type === 'opportunity') {
                    $opportunities[] = [
                        'title' => (string) $post->title,
                        'type' => 'Opportunity',
                        'provider' => (string) ($post->extra['provider'] ?? 'think.er HUB'),
                        'description' => $excerpt,
                        'url' => (string) ($post->opportunity_link ?: $url),
                        'expires_at' => $post->opportunity_deadline ? $post->opportunity_deadline->format('M d, Y') : null,
                    ];
                } else {
                    $articles[] = [
                        'title' => (string) $post->title,
                        'category' => (string) ($post->category ?: 'Technology'),
                        'type' => (string) ($post->type ?: 'article'),
                        'excerpt' => mb_substr($excerpt, 0, 180),
                        'url' => $url,
                    ];
                }
            }
        } catch (Throwable) {
            // Table may not exist yet
        }

        try {
            $videoRecords = ResourceVideo::query()
                ->where('is_published', true)
                ->where('is_recorded_lesson', false)
                ->latest()
                ->limit(30)
                ->get();

            foreach ($videoRecords as $video) {
                $videos[] = [
                    'title' => (string) $video->title,
                    'category' => (string) ($video->category ?: 'General'),
                    'description' => trim(strip_tags((string) ($video->description ?? ''))),
                    'target_level' => (string) ($video->target_level ?? ''),
                ];
            }
        } catch (Throwable) {
            // Table may not exist yet
        }

        return [
            'courses' => $courses,
            'opportunities' => $opportunities,
            'articles' => $articles,
            'videos' => $videos,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Dynamically search all indexed courses, opportunities, articles, and videos
     * so any newly uploaded content is immediately answerable by title or topic.
     */
    private static function searchUploadedContent(string $normalized): ?string
    {
        $tokens = self::extractSearchTokens($normalized);
        if ($tokens === []) {
            return null;
        }

        $snap = self::snapshot();
        $matchedCourses = self::rankMatches($snap['courses'], $tokens, ['title', 'code', 'description']);
        $matchedOpps = self::rankMatches($snap['opportunities'], $tokens, ['title', 'type', 'provider', 'description']);
        $matchedArticles = self::rankMatches($snap['articles'], $tokens, ['title', 'category', 'excerpt']);
        $matchedVideos = self::rankMatches($snap['videos'], $tokens, ['title', 'category', 'description']);

        if ($matchedCourses === [] && $matchedOpps === [] && $matchedArticles === [] && $matchedVideos === []) {
            return null;
        }

        $sections = [];

        if ($matchedCourses !== []) {
            $lines = array_map(function (array $c): string {
                $intake = $c['next_intake'] ? " · Next intake: **{$c['next_intake']}**" : ' · Open enrollment';
                $desc = $c['description'] !== '' ? ' — ' . mb_substr($c['description'], 0, 120) . (mb_strlen($c['description']) > 120 ? '...' : '') : '';
                return "- **{$c['title']}** (`{$c['code']}`) · Fee: {$c['fees']}{$intake}{$desc}";
            }, array_slice($matchedCourses, 0, 4));
            $sections[] = "### Matching Courses\n" . implode("\n", $lines);
        }

        if ($matchedOpps !== []) {
            $lines = array_map(function (array $o): string {
                $exp = $o['expires_at'] ? " (Closes: {$o['expires_at']})" : '';
                return "- **{$o['title']}** — {$o['provider']} (`{$o['type']}`){$exp}";
            }, array_slice($matchedOpps, 0, 4));
            $sections[] = "### Matching Opportunities\n" . implode("\n", $lines);
        }

        if ($matchedArticles !== []) {
            $lines = array_map(
                fn (array $a): string => "- [{$a['title']}]({$a['url']}) (`{$a['category']}`)",
                array_slice($matchedArticles, 0, 4)
            );
            $sections[] = "### Matching Articles & Guides\n" . implode("\n", $lines);
        }

        if ($matchedVideos !== []) {
            $lines = array_map(
                fn (array $v): string => "- **{$v['title']}** (`{$v['category']}`)",
                array_slice($matchedVideos, 0, 3)
            );
            $sections[] = "### Matching Learning Videos\n" . implode("\n", $lines);
        }

        return implode("\n\n", $sections)
            . "\n\nExplore more in our [Course Catalog](/courses) or [Community Hub](/hub), or [register](/register) to get started!";
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<string>  $tokens
     * @param  list<string>  $fields
     * @return list<array<string, mixed>>
     */
    private static function rankMatches(array $items, array $tokens, array $fields): array
    {
        $scored = [];

        foreach ($items as $item) {
            $score = 0;
            $title = strtolower((string) ($item['title'] ?? ''));

            foreach ($tokens as $token) {
                if ($title !== '' && str_contains($title, $token)) {
                    $score += 3;
                    continue;
                }

                foreach ($fields as $field) {
                    if ($field === 'title') {
                        continue;
                    }
                    $val = strtolower((string) ($item[$field] ?? ''));
                    if ($val !== '' && str_contains($val, $token)) {
                        $score += 1;
                        break;
                    }
                }
            }

            if ($score >= 3 || (count($tokens) === 1 && $score >= 1)) {
                $scored[] = ['score' => $score, 'item' => $item];
            }
        }

        usort($scored, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(fn (array $entry): array => $entry['item'], $scored);
    }

    /**
     * @return list<string>
     */
    private static function extractSearchTokens(string $normalized): array
    {
        $words = preg_split('/[^a-z0-9\+\#]+/i', $normalized) ?: [];
        $genericCategoryWords = [
            'course', 'courses', 'track', 'tracks', 'program', 'programs', 'intake', 'intakes',
            'tuition', 'fee', 'fees', 'payment', 'payments', 'enroll', 'enrollment', 'register',
            'portal', 'portals', 'student', 'parent', 'instructor', 'delivery', 'mode', 'modes',
            'internship', 'internships', 'opportunity', 'opportunities', 'article', 'articles',
            'blog', 'tutorial', 'tutorials', 'certificate', 'certificates', 'contact', 'support',
        ];

        $tokens = [];
        foreach ($words as $word) {
            $word = strtolower(trim($word));
            if (strlen($word) < 3) {
                continue;
            }
            if (in_array($word, self::STOP_WORDS, true) || in_array($word, $genericCategoryWords, true)) {
                continue;
            }
            $tokens[] = $word;
        }

        return array_values(array_unique($tokens));
    }

    private static function greetingResponse(): string
    {
        return <<<'MD'
Hi! I'm **Candice**, your AI Brand Ambassador and Admissions Counselor at **think.er HUB**.

I can help you explore:
- **Courses & Intakes**: Web Development, Mobile Apps (Flutter), Data Science & AI, Cloud/DevOps, and Kids & Teens Coding.
- **Tuition & Payments**: Fees, flexible installments, Mobile Money (MTN, Airtel, Zamtel), and promo codes.
- **Portals & Learning Experience**: Student, Parent, Instructor, and Contributor portals.
- **Internships & Opportunities**: Active career placements, scholarships, and hackathons.

What would you like to know about **think.er HUB** today?
MD;
    }

    private static function coursesResponse(string $normalized): string
    {
        $forKids = self::matchesAny($normalized, ['kid', 'kids', 'teen', 'teens', 'child', 'children', 'scratch']);
        $snap = self::snapshot();
        $courses = $snap['courses'];

        if ($forKids) {
            $kidsCourses = array_values(array_filter($courses, fn (array $c): bool => (bool) ($c['for_kids'] ?? false)));
            if ($kidsCourses !== []) {
                $courses = $kidsCourses;
            }
        }

        if ($courses !== []) {
            $lines = array_map(function (array $c): string {
                $intake = $c['next_intake'] ? " — Next intake: **{$c['next_intake']}**" : ' — Open enrollment';
                return "- **{$c['title']}** (`{$c['code']}`) · Fee: {$c['fees']}{$intake}";
            }, array_slice($courses, 0, 8));

            $list = implode("\n", $lines);

            return <<<MD
### Available Courses at think.er HUB

{$list}

You can explore full syllabuses and enroll anytime in our [Course Catalog](/courses), or [create a free account](/register) to get started.
MD;
        }

        return <<<'MD'
### Learning Tracks at think.er HUB

- **Full-Stack Web Development**: HTML5/CSS3, JavaScript, PHP & Laravel, React, databases, and REST APIs.
- **Mobile App Engineering**: Cross-platform apps using Flutter, Dart, and Android integrations.
- **Data Science & AI**: Python data analysis, machine learning fundamentals, and practical AI tooling.
- **Kids & Teens Coding**: Scratch visual programming, foundational Python, and game development.
- **Cloud & DevOps**: Git/GitHub, CI/CD pipelines, containerization, and modern hosting.

Browse current schedules and fees in our [Course Catalog](/courses).
MD;
    }

    private static function tuitionAndPaymentsResponse(): string
    {
        return <<<'MD'
### Tuition & Payment Options

- **Transparent Fees**: Clear upfront course fees listed on each program page with no hidden subscription charges.
- **Flexible Installments**: Split tuition payments across milestones or cohort terms.
- **Supported Payment Methods**:
  - **Mobile Money**: MTN, Airtel, and Zamtel
  - **Cards**: Visa & Mastercard
  - **Bank Transfers** and **Cash/POS** at our Lusaka campus
- **Promo Codes & Scholarships**: Enter any active promo code at checkout for instant discounts.

View exact course fees in the [Course Catalog](/courses) or email **support@thinkerhub.net** for installment assistance.
MD;
    }

    private static function admissionsAndOnboardingResponse(): string
    {
        return <<<'MD'
### How to Enroll at think.er HUB

1. **Create a free account** at [Register](/register).
2. **Browse the catalog** at [Courses](/courses) and select your preferred track.
3. **Choose an intake** (or open enrollment) and complete tuition payment or apply a promo code.
4. **Access your Student Portal** at [/learn](/learn) for your timetable, materials, and cohort channels.

**Prerequisites**: No prior coding experience is needed for beginner tracks—just a working laptop, internet connection, and curiosity!
MD;
    }

    private static function deliveryModesResponse(): string
    {
        return <<<'MD'
### Learning Delivery Modes

- **Live Virtual**: Interactive online classes with live screen-sharing, Q&A, and instructor feedback.
- **Live Physical**: Hands-on lab workshops and cohort sessions at our **Lusaka, Zambia** campus.
- **On-Demand Video Guides**: High-yield recorded walkthroughs and code-along lessons available 24/7 in the Student Portal.
- **Reading Materials**: Curated lecture slides, downloadable PDF study notes, and concise cheatsheets.

Explore programs by format in our [Course Catalog](/courses).
MD;
    }

    private static function portalsResponse(): string
    {
        return <<<'MD'
### The think.er HUB Portal Ecosystem

- **Student Portal (`/learn`)**: Class timetables, 24/7 AI Study Buddy, lesson materials, quizzes, assignments, certificates, and cohort chats.
- **Parent Portal (`/parent`)**: Real-time attendance tracking, quiz scorecards, milestone badges, and direct instructor messaging for guardians.
- **Instructor Portal (`/teach`)**: Cohort timetables, AI Teaching Co-Pilot, digital attendance registers, grading, and broadcasts.
- **Contributor Studio (`/contribute`)**: Write and publish technical tutorials and developer guides on the [Thinker Hub](/hub).
- **Admin Portal (`/manage`)**: Platform oversight, admissions, and financial analytics.

Get started by signing in or registering at [/register](/register).
MD;
    }

    private static function opportunitiesResponse(): string
    {
        $snap = self::snapshot();

        if ($snap['opportunities'] !== []) {
            $lines = array_map(function (array $o): string {
                $exp = $o['expires_at'] ? " (Closes: {$o['expires_at']})" : '';
                return "- **{$o['title']}** — {$o['provider']} (`{$o['type']}`){$exp}";
            }, array_slice($snap['opportunities'], 0, 6));

            $list = implode("\n", $lines);

            return <<<MD
### Active Opportunities & Career Programs

{$list}

Sign in to the [Student Portal](/learn) or visit the [Community Hub](/hub) to apply and explore new internships, scholarships, and hackathons.
MD;
        }

        return <<<'MD'
### Internships, Scholarships & Career Services

**think.er HUB** connects learners with partner **internships**, **junior developer placements**, **merit-based scholarships**, and **community hackathons**.

Browse the latest postings on our [Community Hub](/hub) or inside the Student Portal after registering at [/register](/register).
MD;
    }

    private static function articlesResponse(): string
    {
        $snap = self::snapshot();

        if ($snap['articles'] !== []) {
            $lines = array_map(
                fn (array $a): string => "- [{$a['title']}]({$a['url']}) (`{$a['category']}`)",
                array_slice($snap['articles'], 0, 6)
            );

            $list = implode("\n", $lines);

            return <<<MD
### Latest Articles & Tutorials on Thinker Hub

{$list}

Explore all guides and developer stories on the [Thinker Hub](/hub).
MD;
        }

        return <<<'MD'
### Thinker Hub Community & Tutorials

Explore technical tutorials, programming tips, and career guides written by instructors and community contributors on the [Thinker Hub](/hub).
MD;
    }

    private static function certificationsResponse(): string
    {
        return <<<'MD'
### Verified Certificates & Credentials

- **Certificate of Completion**: Awarded upon completing your course milestones, required evaluations, and capstone project.
- **Online Verification**: Every certificate includes a unique, tamper-proof **Credential ID** that employers and institutions can verify online.

Browse certified programs in our [Course Catalog](/courses).
MD;
    }

    private static function gamificationResponse(): string
    {
        return <<<'MD'
### Gamification, XP & Thinker Coins

- **Earn Rewards**: Collect **XP points** and **Thinker Coins** through attendance streaks, quiz mastery, and timely coursework submissions.
- **Claim Hub**: Redeem your Thinker Coins for tech swag, merchandise, discount vouchers, and bonus workshops.
- **Cohort Leaderboards**: Track milestones and celebrate top learners in your cohort.
MD;
    }

    private static function becomeInstructorResponse(): string
    {
        return <<<'MD'
### Teach at think.er HUB

Experienced software engineers, designers, data scientists, and tech educators can apply to join our faculty:
- Submit your application online at [Apply as an Instructor](/instructors/apply).
- Questions? Reach out to our academic team at **support@thinkerhub.net**.
MD;
    }

    private static function contactResponse(): string
    {
        return <<<'MD'
### Contact think.er HUB

- **Campus**: Lusaka, Zambia *(with global online & hybrid access)*
- **Email**: **support@thinkerhub.net**
- **Contact Form**: Visit our [Contact Page](/contact)

Our admissions and support team will be glad to help!
MD;
    }

    private static function generalOverviewResponse(): string
    {
        return <<<'MD'
I'm **Candice** from **think.er HUB**! Here are the quickest ways I can help you right now:

- **Courses & Fees**: Browse active tracks and upcoming intakes in the [Course Catalog](/courses).
- **Admissions**: [Register for a free account](/register) — no prior coding experience required for beginner tracks.
- **Community & Opportunities**: Read tutorials and check internships on the [Thinker Hub](/hub).
- **Direct Support**: Reach our team anytime at **support@thinkerhub.net** or via our [Contact Page](/contact).

Feel free to ask me about **courses**, **tuition & payments**, **delivery modes**, **portals**, or **internships**!
MD;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function matchesAny(string $text, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $text) === 1) {
                return true;
            }
        }

        return false;
    }
}
