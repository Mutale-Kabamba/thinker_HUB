<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification — {{ config('app.name', 'Thinker HUB') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
            max-width: 480px;
            width: 100%;
            padding: 2.25rem;
            text-align: center;
        }
        .badge { font-size: 2.6rem; }
        h1 { font-size: 1.3rem; margin-top: 0.6rem; }
        .valid h1 { color: #15803d; }
        .invalid h1 { color: #b91c1c; }
        dl { margin-top: 1.25rem; text-align: left; }
        dt { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-top: 0.8rem; }
        dd { font-size: 0.95rem; font-weight: 600; margin-top: 0.15rem; }
        .note { margin-top: 1.25rem; font-size: 0.8rem; color: #64748b; }
        code { background: #f1f5f9; padding: 0.1rem 0.4rem; border-radius: 5px; font-size: 0.8rem; }
    </style>
</head>
<body>
    @if ($certificate)
        <div class="card valid">
            <div class="badge">✅</div>
            <h1>Authentic Certificate</h1>
            <dl>
                <dt>Awarded to</dt>
                <dd>{{ $certificate->user->name ?? 'Student' }}</dd>
                <dt>Course</dt>
                <dd>{{ $certificate->course->title ?? 'Course' }}</dd>
                <dt>Date of issue</dt>
                <dd>{{ $certificate->issued_at->format('F j, Y') }}</dd>
                <dt>Verification code</dt>
                <dd><code>{{ $certificate->verification_code }}</code></dd>
            </dl>
            <p class="note">This certificate was issued by {{ config('app.name', 'Thinker HUB') }} and is verified as authentic.</p>
        </div>
    @else
        <div class="card invalid">
            <div class="badge">❌</div>
            <h1>Certificate Not Found</h1>
            <p class="note">
                No certificate matches verification code <code>{{ $code }}</code>.
                The code may be mistyped, or the certificate may have been revoked.
            </p>
        </div>
    @endif
</body>
</html>
