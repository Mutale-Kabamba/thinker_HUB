<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Thinker HUB Academic Report')</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 8pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* --- HEADER & FOOTER --- */
        .report-header {
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-text {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .header-logo-text span {
            color: #0d9488;
        }

        .header-tagline {
            font-size: 6.5pt;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            color: #64748b;
            margin-top: 1px;
        }

        .header-meta {
            text-align: right;
            font-size: 7pt;
            color: #475569;
            line-height: 1.3;
        }

        .header-title-badge {
            font-size: 7.5pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 18px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 6.5pt;
            color: #94a3b8;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* --- SECTION BOXES & PANELS --- */
        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 8px;
            font-weight: 700;
            font-size: 8pt;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .section-body {
            padding: 6px 8px;
        }

        /* --- METRICS / SUMMARY TILES TABLE --- */
        .stats-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .stats-grid-table td {
            text-align: center;
            padding: 5px 6px;
            border-right: 1px solid #e2e8f0;
            background: #fcfdfe;
        }

        .stats-grid-table td:last-child {
            border-right: none;
        }

        .stat-value {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .stat-caption {
            font-size: 6pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 2px;
            letter-spacing: 0.3px;
        }

        /* --- DATA TABLES --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 8px;
        }

        .data-table th {
            background: #f8fafc;
            color: #334155;
            text-align: left;
            padding: 4px 6px;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #cbd5e1;
        }

        .data-table td {
            padding: 3.5px 6px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background: #fcfdfe;
        }

        /* --- COMPACT BADGES --- */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .badge-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .badge-info { background: #f0f9ff; color: #075985; border: 1px solid #bae6fd; }
        .badge-gray { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

        /* --- QUIZ ANSWER SHEETS (MINIMAL & CLEAN) --- */
        .question-item {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 6px;
            padding: 5px 7px;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .question-title-row {
            font-size: 7.5pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .option-list {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-top: 2px;
        }

        .option-list td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .opt-correct {
            color: #15803d;
            font-weight: 700;
            background: #f0fdf4;
            border-radius: 3px;
        }

        .opt-wrong {
            color: #b91c1c;
            font-weight: 700;
            background: #fef2f2;
            border-radius: 3px;
        }

        .opt-neutral {
            color: #475569;
        }

        .explanation-text {
            font-size: 6.5pt;
            color: #334155;
            background: #f8fafc;
            border-left: 2px solid #64748b;
            padding: 2px 6px;
            margin-top: 3px;
            border-radius: 0 3px 3px 0;
        }

        /* --- UTILITIES --- */
        .page-break {
            page-break-after: always;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #64748b;
        }

        .progress-bar-bg {
            background: #e2e8f0;
            border-radius: 2px;
            height: 5px;
            width: 100%;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 2px;
            background: #0d9488;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td style="width: 50%; vertical-align: middle;">
                    <div class="header-logo-text">think<span>.er</span> HUB</div>
                    <div class="header-tagline">Academic & Performance Record</div>
                </td>
                <td class="header-meta" style="width: 50%; vertical-align: middle;">
                    <div class="header-title-badge">@yield('report_type', 'Official Academic Report')</div>
                    <div><strong>Date:</strong> {{ $generated_at ?? now()->format('Y-m-d H:i') }} • <strong>Doc ID:</strong> THUB-{{ date('Ymd') }}-{{ rand(1000, 9999) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Content --}}
    <div class="report-content">
        @yield('content')
    </div>

    {{-- Footer --}}
    <div class="report-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 65%;">
                    Thinker HUB LMS • Official Academic & Verification Record
                </td>
                <td style="width: 35%; text-align: right;">
                    Confidential • Authorized Personnel Only
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
