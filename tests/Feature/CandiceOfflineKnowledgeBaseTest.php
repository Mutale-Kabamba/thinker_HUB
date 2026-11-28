<?php

namespace Tests\Feature;

use App\Ai\Agents\PublicCourseAdvisor;
use App\Ai\Services\CandiceKnowledgeBase;
use App\Livewire\Ai\PublicChatWidget;
use App\Models\Course;
use App\Models\HubPost;
use App\Models\Opportunity;
use App\Models\ResourceVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandiceOfflineKnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_candice_falls_back_to_preloaded_knowledge_base_when_ai_model_fails(): void
    {
        Course::create([
            'title' => 'Full-Stack Laravel Bootcamp',
            'code' => 'LAR-301',
            'fees' => 'ZMW 2,500',
            'is_active' => true,
        ]);

        PublicCourseAdvisor::fake(function () {
            throw new \RuntimeException('AI provider [gemini] is overloaded.');
        });

        Livewire::test(PublicChatWidget::class)
            ->set('message', 'What courses are currently available?')
            ->call('sendMessage')
            ->assertSee('Full-Stack Laravel Bootcamp')
            ->assertSee('LAR-301')
            ->assertDontSee('overloaded');
    }

    public function test_new_course_article_opportunity_and_video_uploads_auto_sync_into_knowledge_base(): void
    {
        // Warm cache before uploads
        $initial = CandiceKnowledgeBase::snapshot();
        $this->assertEmpty($initial['courses']);

        $author = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Upload a brand-new course, article, opportunity, and video
        Course::create([
            'title' => 'Cybersecurity Defense Essentials',
            'code' => 'SEC-202',
            'fees' => 'ZMW 3,200',
            'is_active' => true,
        ]);

        HubPost::create([
            'title' => 'Deploying Docker Containers in Production',
            'slug' => 'deploying-docker-containers-in-production',
            'type' => 'blog',
            'category' => 'DevOps',
            'excerpt' => 'Step-by-step guide to Docker deployments.',
            'content' => 'Full Docker tutorial content.',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        Opportunity::create([
            'title' => 'Lusaka Robotics Fellowship',
            'type' => 'Scholarship',
            'provider' => 'Zambia Innovation Fund',
            'is_published' => true,
        ]);

        ResourceVideo::create([
            'title' => 'Kubernetes Networking Walkthrough',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'category' => 'Cloud & DevOps',
            'is_recorded_lesson' => false,
            'is_published' => true,
        ]);

        // Observer should have automatically invalidated cache so every new upload is immediately searchable
        $this->assertStringContainsString('Cybersecurity Defense Essentials', CandiceKnowledgeBase::answer('Tell me about Cybersecurity Defense'));
        $this->assertStringContainsString('Deploying Docker Containers in Production', CandiceKnowledgeBase::answer('Do you have anything on Docker?'));
        $this->assertStringContainsString('Lusaka Robotics Fellowship', CandiceKnowledgeBase::answer('Tell me about Robotics Fellowship'));
        $this->assertStringContainsString('Kubernetes Networking Walkthrough', CandiceKnowledgeBase::answer('Any videos on Kubernetes?'));

        // Live AI system prompt also receives the auto-synced catalog snapshot
        $instructions = (new PublicCourseAdvisor)->instructions();
        $this->assertStringContainsString('Cybersecurity Defense Essentials', $instructions);
        $this->assertStringContainsString('Deploying Docker Containers in Production', $instructions);
        $this->assertStringContainsString('Lusaka Robotics Fellowship', $instructions);
    }

    public function test_candice_knowledge_base_covers_core_institutional_topics(): void
    {
        Opportunity::create([
            'title' => 'Junior Frontend Internship',
            'type' => 'Job',
            'provider' => 'Lusaka Tech Labs',
            'is_published' => true,
        ]);

        $this->assertStringContainsString('Mobile Money', CandiceKnowledgeBase::answer('How do tuition and payment work?'));
        $this->assertStringContainsString('Live Virtual', CandiceKnowledgeBase::answer('What delivery modes do you support?'));
        $this->assertStringContainsString('Student Portal', CandiceKnowledgeBase::answer('How do the Student and Parent portals work?'));
        $this->assertStringContainsString('Junior Frontend Internship', CandiceKnowledgeBase::answer('What internships exist?'));
        $this->assertStringContainsString('Candice', CandiceKnowledgeBase::answer('Hello'));
    }
}
