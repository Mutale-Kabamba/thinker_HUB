<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Thinker HUB Official Academic Report')</title>
    <style>
        @page {
            margin: 12mm 12mm 16mm 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* --- HEADER & FOOTER --- */
        .report-header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-text {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .header-logo-text span {
            color: #0d9488;
        }

        .header-tagline {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #64748b;
        }

        .header-meta {
            text-align: right;
            font-size: 7.5pt;
            color: #475569;
        }

        .header-badge {
            display: inline-block;
            background: #f0fdfa;
            color: #0f766e;
            border: 1px solid #99f6e4;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .report-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            font-size: 7pt;
            color: #94a3b8;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* --- CARDS & PANELS --- */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 14px;
            background: #ffffff;
        }

        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 12px;
            font-weight: 700;
            font-size: 9.5pt;
            color: #0f172a;
        }

        .card-body {
            padding: 10px 12px;
        }

        /* --- METRICS STATS TILES --- */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .stat-tile {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .stat-val {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 3px;
        }

        /* --- TABLES --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #334155;
            text-align: left;
            padding: 6px 8px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
        }

        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }

        .data-table tr:nth-child(even) td {
            background: #fafbfc;
        }

        /* --- BADGES --- */
        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 9999px;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-purple { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* --- QUIZ ANSWER SHEETS --- */
        .question-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 10px;
            padding: 8px 10px;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .question-header {
            font-weight: 700;
            font-size: 8.5pt;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .option-row {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 7.5pt;
            margin-bottom: 3px;
            border: 1px solid transparent;
        }

        .option-correct {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
            font-weight: 600;
        }

        .option-wrong-choice {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #991b1b;
            font-weight: 600;
        }

        .option-neutral {
            background: #f8fafc;
            color: #475569;
        }

        .explanation-box {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 4px 8px;
            font-size: 7pt;
            color: #1e40af;
            margin-top: 5px;
            border-radius: 0 4px 4px 0;
        }

        /* --- PAGE UTILITIES --- */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .progress-bar-container {
            background: #e2e8f0;
            border-radius: 9999px;
            height: 7px;
            width: 100%;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td style="width: 55%; vertical-align: middle;">
                    <div class="header-logo-text">think<span>.er</span> HUB</div>
                    <div class="header-tagline">Think. Learn. Disrupt. • Academic & Analytics Center</div>
                </td>
                <td class="header-meta" style="width: 45%; vertical-align: middle;">
                    <div class="header-badge">OFFICIAL INSTITUTIONAL REPORT</div>
                    <div><strong>Generated:</strong> {{ $generated_at ?? now()->format('F d, Y • H:i') }}</div>
                    <div><strong>System:</strong> Thinker HUB Core Academic Engine</div>
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
                <td style="width: 60%;">
                    Confidential & Proprietary • Thinker HUB Learning Management System
                </td>
                <td style="width: 40%; text-align: right;">
                    Official Academic Record • Verified Digital Signature
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
