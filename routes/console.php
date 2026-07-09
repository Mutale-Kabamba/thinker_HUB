<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;
use App\Models\Course;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use Spatie\Sitemap\Tags\Url;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:probe {--to=}', function (): int {
    $to = (string) ($this->option('to') ?: config('mail.from.address'));

    if ($to === '') {
        $this->error('No recipient email provided. Use --to=address@example.com or configure MAIL_FROM_ADDRESS.');
        return self::FAILURE;
    }

    $this->info('Mail configuration snapshot:');
    $this->line('MAIL_MAILER: '.(string) config('mail.default'));
    $this->line('SMTP host: '.(string) config('mail.mailers.smtp.host'));
    $this->line('SMTP port: '.(string) config('mail.mailers.smtp.port'));
    $this->line('SMTP scheme: '.var_export(config('mail.mailers.smtp.scheme'), true));
    $this->line('From address: '.(string) config('mail.from.address'));
    $this->line('To address: '.$to);

    try {
        Mail::raw('Thinker HUB mail probe at '.now()->toDateTimeString(), function ($message) use ($to): void {
            $message->to($to)->subject('Thinker HUB Mail Probe');
        });

        $this->info('Mail probe sent successfully.');
        return self::SUCCESS;
    } catch (\Throwable $exception) {
        $this->error('Mail probe failed: '.$exception->getMessage());
        return self::FAILURE;
    }
})->purpose('Send a probe email and print exact SMTP diagnostics.');

Artisan::command('seo:generate', function (): int {
    $this->info('Generating sitemap index, child sitemaps, and robots.txt...');

    $staticSitemap = Sitemap::create()
        ->add(
            Url::create(route('home'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(1.0)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.courses'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.9)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.instructors'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.contact'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.privacy'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.4)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.cookies'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.4)
                ->setLastModificationDate(now())
        )
        ->add(
            Url::create(route('landing.terms'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.4)
                ->setLastModificationDate(now())
        );

    $coursesSitemap = Sitemap::create();

    try {
        $hasSqliteDatabaseFile = true;

        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');
            $hasSqliteDatabaseFile = $sqlitePath !== '' && is_file($sqlitePath);
        }

        if ($hasSqliteDatabaseFile && Schema::hasTable('courses')) {
            Course::query()
                ->where('is_active', true)
                ->latest('updated_at')
                ->get(['id', 'title', 'code', 'updated_at'])
                ->each(function (Course $course) use ($coursesSitemap): void {
                    $slugSource = trim((string) ($course->title ?: $course->code ?: $course->id));
                    $slug = Str::slug($slugSource);

                    $courseUrl = Url::create(route('landing.courses.show', ['course' => $course->id, 'slug' => $slug]))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                        ->setLastModificationDate($course->updated_at ?? now());

                    // Attach a representative image for richer sitemap indexing.
                    $courseUrl->addImage(asset('images/logos/green.png'), $course->title, '', $course->title);

                    $coursesSitemap->add($courseUrl);
                });
        }
    } catch (\Throwable $e) {
        report($e);
        $this->warn('Courses could not be loaded for sitemap. Static pages were still generated.');
    }

    $staticPath = public_path('sitemap-static.xml');
    $coursesPath = public_path('sitemap-courses.xml');
    $indexPath = public_path('sitemap.xml');

    $staticSitemap->writeToFile($staticPath);
    $coursesSitemap->writeToFile($coursesPath);

    SitemapIndex::create()
        ->add(
            SitemapTag::create(url('/sitemap-static.xml'))
                ->setLastModificationDate(now())
        )
        ->add(
            SitemapTag::create(url('/sitemap-courses.xml'))
                ->setLastModificationDate(now())
        )
        ->writeToFile($indexPath);

    $appUrl = rtrim((string) config('app.url'), '/');
    $sitemapUrl = ($appUrl !== '' ? $appUrl : url('/')).'/sitemap.xml';

    $robots = implode(PHP_EOL, [
        'User-agent: *',
        'Allow: /',
        'Sitemap: '.$sitemapUrl,
        '',
    ]);

    file_put_contents(public_path('robots.txt'), $robots);

    $this->info('SEO assets generated successfully.');

    return self::SUCCESS;
})->purpose('Generate SEO sitemap.xml and robots.txt using Spatie sitemap.');

Schedule::command('seo:generate')
    ->dailyAt('02:30')
    ->withoutOverlapping();
