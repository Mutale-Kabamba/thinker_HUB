<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate — {{ $certificate->course->title ?? 'Course' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            color: #1e293b;
        }

        .toolbar {
            margin-bottom: 1.25rem;
            display: flex;
            gap: 0.6rem;
        }

        .toolbar button, .toolbar a {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.5rem 1.1rem;
            border-radius: 8px;
            border: 1px solid #0f766e;
            background: #0f766e;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar a { background: #fff; color: #0f766e; }

        .certificate {
            width: 1122px; /* A4 landscape at 96dpi */
            max-width: 100%;
            aspect-ratio: 297 / 210;
            background: #fffdf8;
            border: 2px solid #b45309;
            outline: 10px solid #fffdf8;
            box-shadow: 0 0 0 12px #b45309, 0 24px 60px rgba(0,0,0,.25);
            padding: 3.5rem 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .certificate::before {
            content: '';
            position: absolute;
            inset: 14px;
            border: 1px solid #d6b47b;
            pointer-events: none;
        }

        .brand {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #b45309;
            font-weight: 700;
        }

        h1 {
            font-size: 2.6rem;
            font-weight: 400;
            letter-spacing: 0.08em;
            margin-top: 0.75rem;
            color: #7c2d12;
        }

        .subtitle { font-size: 0.95rem; color: #64748b; margin-top: 0.4rem; font-style: italic; }

        .recipient {
            font-size: 2.1rem;
            margin-top: 1.5rem;
            color: #0f172a;
            border-bottom: 1px solid #d6b47b;
            padding: 0 2rem 0.4rem;
        }

        .course-label { margin-top: 1.4rem; font-size: 0.95rem; color: #64748b; font-style: italic; }

        .course-title {
            font-size: 1.7rem;
            color: #0f766e;
            margin-top: 0.35rem;
            font-weight: 700;
        }

        .meta {
            margin-top: auto;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.78rem;
            color: #475569;
            padding-top: 2rem;
        }

        .meta .block { text-align: center; }
        .meta .line { width: 180px; border-top: 1px solid #94a3b8; margin-bottom: 0.35rem; }

        .seal {
            width: 84px;
            height: 84px;
            border-radius: 999px;
            border: 3px double #b45309;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            color: #b45309;
            background: radial-gradient(circle, #fffbeb 0%, #fef3c7 100%);
        }

        .verify {
            margin-top: 0.6rem;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .verify strong { color: #64748b; letter-spacing: 0.12em; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .certificate { box-shadow: none; width: 100%; height: 100vh; }
            @page { size: A4 landscape; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Download / Print PDF</button>
        <a href="{{ url('/learn/certificates') }}">Back to My Certificates</a>
    </div>

    <div class="certificate">
        <p class="brand">{{ config('app.name', 'Thinker HUB') }}</p>
        <h1>Certificate of Completion</h1>
        <p class="subtitle">This is to certify that</p>

        <p class="recipient">{{ $certificate->user->name ?? 'Student' }}</p>

        <p class="course-label">has successfully completed the course</p>
        <p class="course-title">{{ $certificate->course->title ?? 'Course' }}</p>

        <div class="meta">
            <div class="block">
                <div class="line"></div>
                {{ $certificate->course->instructors->first()?->name ?? 'Course Instructor' }}<br>
                <span style="color:#94a3b8;">Instructor</span>
            </div>
            <div class="seal">🎓</div>
            <div class="block">
                <div class="line"></div>
                {{ $certificate->issued_at->format('F j, Y') }}<br>
                <span style="color:#94a3b8;">Date of Issue</span>
            </div>
        </div>

        <p class="verify">
            Verify authenticity at {{ $certificate->verificationUrl() }}<br>
            Verification code: <strong>{{ $certificate->verification_code }}</strong>
        </p>
    </div>
</body>
</html>
