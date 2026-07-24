<x-filament-panels::page>

    <div class="hub-shell">

        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Achievements</p>
            <h2 class="hub-title" style="font-size:1.05rem;">My Certificates</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Certificates you have earned for completing courses. Each one carries a public verification link.</p>
        </section>

        @if (count($certificates) === 0)
            <section class="hub-card" style="text-align:center;padding:2rem 1rem;">
                <p style="font-size:1.6rem;">🎓</p>
                <p class="hub-copy" style="margin-top:0.4rem;">No certificates yet.</p>
                <p class="hub-copy" style="margin-top:0.25rem;color:var(--hub-muted);font-size:0.8rem;">
                    Enroll in a course and pass all of its quizzes — a "Claim Certificate" button will then appear on the Courses page.
                </p>
            </section>
        @else
            <div class="hub-stack">
                @foreach ($certificates as $certificate)
                    <section class="hub-card" style="padding:0.85rem 1rem;border-left:4px solid #b45309;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.6rem;flex-wrap:wrap;">
                            <div style="min-width:0;">
                                <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.92rem;">🎓 {{ $certificate['course_title'] }}</p>
                                <p style="margin:0.2rem 0 0;font-size:0.74rem;color:var(--hub-muted);">
                                    {{ $certificate['course_code'] }} · Issued {{ $certificate['issued_at'] }}
                                </p>
                                <p style="margin:0.35rem 0 0;font-size:0.74rem;color:var(--hub-muted);">
                                    Verification code:
                                    <code style="background:var(--hub-surface);padding:0.1rem 0.4rem;border-radius:5px;font-weight:700;color:var(--hub-ink);letter-spacing:0.08em;">{{ $certificate['verification_code'] }}</code>
                                </p>
                                <p style="margin:0.25rem 0 0;font-size:0.72rem;color:var(--hub-muted);word-break:break-all;">
                                    Verify at:
                                    <a href="{{ $certificate['verification_url'] }}" target="_blank" rel="noopener" style="color:var(--hub-primary);">{{ $certificate['verification_url'] }}</a>
                                </p>
                            </div>
                            <a href="{{ $certificate['download_url'] }}" target="_blank" rel="noopener" class="hub-btn hub-btn-primary" style="font-size:0.78rem;padding:0.4rem 0.8rem;text-decoration:none;flex-shrink:0;">
                                ⬇ Download PDF
                            </a>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

    </div>

</x-filament-panels::page>
