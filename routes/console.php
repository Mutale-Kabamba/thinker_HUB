<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

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
