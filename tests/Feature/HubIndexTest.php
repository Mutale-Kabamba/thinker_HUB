<?php

namespace Tests\Feature;

use App\Livewire\Public\HubIndex;
use App\Livewire\Public\HubShow;
use App\Models\HubPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HubIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_page_can_be_rendered(): void
    {
        $response = $this->get('/hub');

        $response->assertOk();
        $response->assertSeeLivewire(HubIndex::class);
    }

    public function test_videos_are_prioritized_first_in_default_sorting(): void
    {
        $author = User::factory()->create();

        $opportunity = HubPost::create([
            'title' => 'Sample Opportunity',
            'slug' => 'sample-opportunity',
            'type' => 'opportunity',
            'category' => 'Career',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $blog = HubPost::create([
            'title' => 'Sample Blog',
            'slug' => 'sample-blog',
            'type' => 'blog',
            'category' => 'Technology',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $tip = HubPost::create([
            'title' => 'Sample Tip',
            'slug' => 'sample-tip',
            'type' => 'tip_trick',
            'category' => 'Programming',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $video = HubPost::create([
            'title' => 'Sample Video',
            'slug' => 'sample-video',
            'type' => 'video',
            'category' => 'Programming',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        Livewire::test(HubIndex::class)
            ->assertViewHas('posts', function ($posts) use ($video, $tip, $blog, $opportunity) {
                $ids = $posts->pluck('id')->toArray();
                return $ids[0] === $video->id
                    && $ids[1] === $tip->id
                    && $ids[2] === $blog->id
                    && $ids[3] === $opportunity->id;
            });
    }

    public function test_filtering_by_type(): void
    {
        $author = User::factory()->create();

        $video = HubPost::create([
            'title' => 'Laravel Video Tutorial',
            'slug' => 'laravel-video-tutorial',
            'type' => 'video',
            'category' => 'Programming',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $blog = HubPost::create([
            'title' => 'Vue JS Guide',
            'slug' => 'vue-js-guide',
            'type' => 'blog',
            'category' => 'Programming',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        Livewire::test(HubIndex::class)
            ->call('selectType', 'video')
            ->assertSee($video->title)
            ->assertDontSee($blog->title);
    }

    public function test_filtering_by_search_and_category(): void
    {
        $author = User::factory()->create();

        $post1 = HubPost::create([
            'title' => 'Unique Keyword Title',
            'slug' => 'unique-keyword-title',
            'type' => 'blog',
            'category' => 'Design',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $post2 = HubPost::create([
            'title' => 'Another Article',
            'slug' => 'another-article',
            'type' => 'blog',
            'category' => 'Career',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        Livewire::test(HubIndex::class)
            ->set('search', 'Unique Keyword')
            ->assertSee('Unique Keyword Title')
            ->assertDontSee('Another Article')
            ->call('resetSearch')
            ->assertSee('Another Article')
            ->call('selectCategory', 'Design')
            ->assertSee('Unique Keyword Title')
            ->assertDontSee('Another Article')
            ->call('resetFilters')
            ->assertSee('Another Article');
    }

    public function test_full_detail_page_can_be_rendered(): void
    {
        $author = User::factory()->create(['name' => 'Jane Mentor']);

        $post = HubPost::create([
            'title' => 'Deep Dive into Laravel Architecture',
            'slug' => 'deep-dive-into-laravel-architecture',
            'type' => 'blog',
            'category' => 'Programming',
            'excerpt' => 'Detailed explanation of service providers and containers.',
            'content' => '<p>Service providers are the central place of all Laravel application bootstrapping.</p>',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $response = $this->get('/hub/' . $post->slug);

        $response->assertOk();
        $response->assertSee('Deep Dive into Laravel Architecture');
        $response->assertSee('Jane Mentor');
        $response->assertSee('Service providers are the central place');
    }

    public function test_opportunity_external_link_and_full_page(): void
    {
        $author = User::factory()->create();

        $opp = HubPost::create([
            'title' => 'Senior Backend Engineer',
            'slug' => 'senior-backend-engineer',
            'type' => 'opportunity',
            'category' => 'Career',
            'opportunity_link' => 'https://external-jobs.com/apply/123',
            'opportunity_deadline' => now()->addDays(10),
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        $response = $this->get('/hub/' . $opp->slug);

        $response->assertOk();
        $response->assertSee('https://external-jobs.com/apply/123');
        $response->assertSee('Apply / Access Opportunity');
    }

    public function test_dynamic_submission_modal_routes_opportunity_and_tips(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(HubIndex::class)
            ->call('openSubmitModal')
            ->set('submitType', 'tip_trick')
            ->set('submitTitle', 'Clever Eloquent Query Tip')
            ->set('submitCategory', 'Programming')
            ->set('submitExcerpt', 'Use chunking to optimize memory.')
            ->set('submitCodeSnippet', 'User::chunk(100, function ($users) {});')
            ->set('submitProTip', 'Always index foreign keys!')
            ->call('submitResource');

        $this->assertDatabaseHas('hub_posts', [
            'title' => 'Clever Eloquent Query Tip',
            'type' => 'tip_trick',
            'code_snippet' => 'User::chunk(100, function ($users) {});',
            'pro_tip' => 'Always index foreign keys!',
            'is_published' => true,
        ]);

        Livewire::test(HubIndex::class)
            ->call('openSubmitModal')
            ->set('submitType', 'opportunity')
            ->set('submitTitle', 'Full Stack Developer Internship')
            ->set('submitProvider', 'TechHub Africa')
            ->set('submitOpportunityType', 'Internship')
            ->set('submitLocation', 'Remote')
            ->set('submitCompensation', '$1,200/mo')
            ->set('submitRequirements', "3+ years PHP\nVue.js experience")
            ->set('submitOpportunityLink', 'https://techhub.africa/apply')
            ->call('submitResource');

        $this->assertDatabaseHas('opportunities', [
            'title' => 'Full Stack Developer Internship',
            'provider' => 'TechHub Africa',
            'type' => 'Internship',
            'link_url' => 'https://techhub.africa/apply',
        ]);
    }

    public function test_media_attachment_submission_and_download_route(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->create('cheat-sheet.pdf', 500, 'application/pdf');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(HubIndex::class)
            ->call('openSubmitModal')
            ->set('submitType', 'tip_trick')
            ->set('submitTitle', 'Laravel Performance Cheat Sheet')
            ->set('submitCategory', 'Programming')
            ->set('submitFiles', [$file])
            ->call('submitResource');

        $post = HubPost::where('title', 'Laravel Performance Cheat Sheet')->first();
        $this->assertNotNull($post);
        $this->assertCount(1, $post->media);

        $media = $post->media->first();
        $this->assertEquals('cheat-sheet.pdf', $media->original_name);

        $downloadResponse = $this->get(route('media.download', $media->id));
        $downloadResponse->assertOk();
    }

    public function test_header_shows_dashboard_for_authenticated_user_and_login_for_guests(): void
    {
        $guestResponse = $this->get('/hub');
        $guestResponse->assertOk();
        $guestResponse->assertSee('Login');
        $guestResponse->assertDontSee('Dashboard (');

        $user = User::factory()->create(['name' => 'Alex Student', 'role' => 'student']);
        $authResponse = $this->actingAs($user)->get('/hub');
        $authResponse->assertOk();
        $authResponse->assertSee('Dashboard (Alex)');
    }

    public function test_contributor_registration_creates_inactive_user_pending_admin_approval(): void
    {
        Livewire::test(HubIndex::class)
            ->set('regName', 'Jane Blogger')
            ->set('regEmail', 'jane@blogger.com')
            ->set('regRole', 'blogger')
            ->set('regPassword', 'secret1234')
            ->set('regPasswordConfirmation', 'secret1234')
            ->call('registerContributor');

        $user = User::where('email', 'jane@blogger.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('blogger', $user->role);
        $this->assertFalse($user->is_active); // Inactive until approved by Admin!
    }

    public function test_contributor_roles_enforce_submission_permissions(): void
    {
        $blogger = User::factory()->create(['role' => 'blogger', 'is_active' => true]);
        $researcher = User::factory()->create(['role' => 'researcher', 'is_active' => true]);
        $employer = User::factory()->create(['role' => 'employer', 'is_active' => true]);

        $this->assertTrue($blogger->canSubmitType('blog'));
        $this->assertFalse($blogger->canSubmitType('opportunity'));

        $this->assertTrue($researcher->canSubmitType('tip_trick'));
        $this->assertFalse($researcher->canSubmitType('blog'));

        $this->assertTrue($employer->canSubmitType('opportunity'));
        $this->assertFalse($employer->canSubmitType('tip_trick'));
    }
}
