<div class="hub-shell">
    <style>
        .hub-shell {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
            font-family: inherit;
            color: #0f172a;
        }

        .dark .hub-shell,
        .fi-theme-dark .hub-shell {
            color: #f8fafc;
        }

        /* --- Design Tokens & Core Utilities --- */
        .hub-chip {
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.25rem !important;
            line-height: 1 !important;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.65rem;
            padding: 0.2rem 0.45rem;
            letter-spacing: 0.01em;
        }

        .hub-chip-primary {
            background: #f0fdf4;
            color: #0f4a43;
            border: 1px solid #bbf7d0;
        }
        .dark .hub-chip-primary,
        .fi-theme-dark .hub-chip-primary {
            background: #132e2a;
            color: #34d399;
            border-color: #165b53;
        }

        .hub-chip-gray {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .dark .hub-chip-gray,
        .fi-theme-dark .hub-chip-gray {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155;
        }

        .hub-chip-green {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .dark .hub-chip-green,
        .fi-theme-dark .hub-chip-green {
            background: #143522;
            color: #4ade80;
            border-color: #166534;
        }

        .hub-chip-amber {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .dark .hub-chip-amber,
        .fi-theme-dark .hub-chip-amber {
            background: #362208;
            color: #fbbf24;
            border-color: #78350f;
        }

        .hub-chip-blue {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .dark .hub-chip-blue,
        .fi-theme-dark .hub-chip-blue {
            background: #0f2347;
            color: #60a5fa;
            border-color: #1e40af;
        }

        .hub-chip-red {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .dark .hub-chip-red,
        .fi-theme-dark .hub-chip-red {
            background: #361010;
            color: #f87171;
            border-color: #7f1d1d;
        }

        /* --- Clean Header Card --- */
        .claim-hub-header {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            color: #ffffff;
            width: 100%;
            box-sizing: border-box;
        }

        .dark .claim-hub-header,
        .fi-theme-dark .claim-hub-header {
            background: #102028;
            border-color: #2d4048;
        }

        .claim-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        .claim-header-info {
            max-width: 420px;
            width: 100%;
            box-sizing: border-box;
        }

        .claim-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 0.62rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }

        .claim-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
        }

        .claim-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }

        .claim-header-desc {
            margin: 0.25rem 0 0 0;
            font-size: 0.75rem;
            color: #94a3b8;
            line-height: 1.4;
        }

        /* --- Clean Metric Cards Grid --- */
        .claim-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(125px, 1fr));
            gap: 0.5rem;
            width: 100%;
            flex: 1;
            max-width: 480px;
            box-sizing: border-box;
        }

        .claim-stat-box {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.55rem 0.7rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            box-sizing: border-box;
        }

        .dark .claim-stat-box,
        .fi-theme-dark .claim-stat-box {
            background: #09181f;
            border-color: #2d4048;
        }

        .stat-label {
            font-size: 0.62rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.04em;
        }

        .dark .stat-label,
        .fi-theme-dark .stat-label {
            color: #94a3b8;
        }

        .stat-value-group {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            margin-top: 0.2rem;
        }

        .stat-number {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        .stat-unit {
            font-size: 0.62rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .stat-subtext {
            font-size: 0.62rem;
            color: #64748b;
            margin-top: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dark .stat-subtext,
        .fi-theme-dark .stat-subtext {
            color: #94a3b8;
        }

        /* --- Section Container Card --- */
        .claim-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.45rem;
            width: 100%;
            box-sizing: border-box;
        }

        .dark .claim-section-card,
        .fi-theme-dark .claim-section-card {
            background: #102028;
            border-color: #2d4048;
        }

        /* --- Navigation Tab Bar --- */
        .claim-tab-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.4rem;
            width: 100%;
            box-sizing: border-box;
        }

        .claim-tab-grid {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            max-width: 440px;
            box-sizing: border-box;
        }

        .claim-tab-btn {
            flex: 1;
            padding: 0.42rem 0.65rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.12s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background: #ffffff;
            color: #475569;
            text-align: center;
            touch-action: manipulation;
            box-sizing: border-box;
            min-width: 0;
            white-space: nowrap;
        }

        .claim-tab-btn:hover {
            border-color: #94a3b8;
            color: #0f172a;
            background: #f8fafc;
        }

        .claim-tab-btn.active {
            background: #7C3AED;
            color: #ffffff;
            border-color: #7C3AED;
            box-shadow: 0 4px 12px -2px rgba(124, 58, 237, 0.35);
        }

        .dark .claim-tab-btn,
        .fi-theme-dark .claim-tab-btn {
            background: #102028;
            color: #cbd5e1;
            border-color: #2d4048;
        }

        .dark .claim-tab-btn:hover,
        .fi-theme-dark .claim-tab-btn:hover {
            background: #112831;
            color: #ffffff;
            border-color: #475569;
        }

        .dark .claim-tab-btn.active,
        .fi-theme-dark .claim-tab-btn.active {
            background: #7C3AED;
            color: #ffffff;
            border-color: #7C3AED;
        }

        .claim-history-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 0.42rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.12s ease;
        }

        .claim-history-btn:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        .dark .claim-history-btn,
        .fi-theme-dark .claim-history-btn {
            background: #102028;
            border-color: #2d4048;
            color: #f8fafc;
        }

        .dark .claim-history-btn:hover,
        .fi-theme-dark .claim-history-btn:hover {
            background: #112831;
            border-color: #475569;
        }

        /* --- Filter Chips (Horizontal Scroll) --- */
        .chip-scroll-bar {
            display: flex;
            gap: 0.3rem;
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 0.1rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            width: 100%;
            box-sizing: border-box;
        }

        .chip-scroll-bar::-webkit-scrollbar {
            display: none;
        }

        .chip-scroll-btn {
            white-space: nowrap;
            flex-shrink: 0;
            padding: 0.28rem 0.6rem;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.12s ease;
            box-sizing: border-box;
            touch-action: manipulation;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        .chip-scroll-btn:hover {
            border-color: #94a3b8;
            color: #0f172a;
        }

        .chip-scroll-btn.active {
            border-color: #0f4a43;
            background: #0f4a43;
            color: #ffffff;
        }

        .dark .chip-scroll-btn,
        .fi-theme-dark .chip-scroll-btn {
            background: #09181f;
            border-color: #2d4048;
            color: #cbd5e1;
        }

        .dark .chip-scroll-btn:hover,
        .fi-theme-dark .chip-scroll-btn:hover {
            background: #112831;
            color: #ffffff;
            border-color: #475569;
        }

        .dark .chip-scroll-btn.active,
        .fi-theme-dark .chip-scroll-btn.active {
            background: #006a67;
            border-color: #006a67;
            color: #ffffff;
        }

        /* --- Inputs & Cards --- */
        .claim-controls-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
            box-sizing: border-box;
        }

        .claim-inputs-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.35rem;
            width: 100%;
            box-sizing: border-box;
        }

        .hub-input {
            padding: 0.35rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.12s ease;
        }

        .hub-input:focus {
            border-color: #0f4a43;
        }

        .dark .hub-input,
        .fi-theme-dark .hub-input {
            background: #09181f;
            color: #f8fafc;
            border-color: #2d4048;
        }

        .dark .hub-input:focus,
        .fi-theme-dark .hub-input:focus {
            border-color: #14b8a6;
        }

        /* --- Reward Cards Grid --- */
        .rewards-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 0.65rem;
            width: 100%;
            box-sizing: border-box;
        }

        .reward-card {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.12s ease, transform 0.12s ease;
            box-sizing: border-box;
            width: 100%;
        }

        .reward-card:hover {
            border-color: #0f4a43;
        }

        .dark .reward-card,
        .fi-theme-dark .reward-card {
            background: #102028;
            border-color: #2d4048;
        }

        .dark .reward-card:hover,
        .fi-theme-dark .reward-card:hover {
            border-color: #14b8a6;
        }

        .reward-visual-box {
            background: #f8fafc;
            padding: 0.65rem;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80px;
            border-bottom: 1px solid #f1f5f9;
        }

        .dark .reward-visual-box,
        .fi-theme-dark .reward-visual-box {
            background: #09181f;
            border-bottom-color: #2d4048;
        }

        .reward-icon-box {
            width: 2rem;
            height: 2rem;
            border-radius: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f4a43;
        }

        .dark .reward-icon-box,
        .fi-theme-dark .reward-icon-box {
            background: #132e2a;
            border-color: #165b53;
            color: #34d399;
        }

        .reward-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin: 0.1rem 0 0 0;
        }

        .dark .reward-title,
        .fi-theme-dark .reward-title {
            color: #f8fafc;
        }

        .reward-desc {
            font-size: 0.68rem;
            color: #64748b;
            margin-top: 0.2rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
        }

        .dark .reward-desc,
        .fi-theme-dark .reward-desc {
            color: #94a3b8;
        }

        .reward-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 0.45rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.3rem;
        }

        .dark .reward-footer,
        .fi-theme-dark .reward-footer {
            border-top-color: #2d4048;
        }

        .reward-claim-btn {
            background: #0f4a43;
            color: #ffffff;
            padding: 0.3rem 0.55rem;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.7rem;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.12s ease;
        }

        .reward-claim-btn:hover:not(:disabled) {
            background: #115e59;
        }

        .dark .reward-claim-btn:not(:disabled),
        .fi-theme-dark .reward-claim-btn:not(:disabled) {
            background: #006a67;
            color: #ffffff;
        }

        .dark .reward-claim-btn:hover:not(:disabled),
        .fi-theme-dark .reward-claim-btn:hover:not(:disabled) {
            background: #008884;
        }

        .reward-claim-btn:disabled {
            background: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
        }

        .dark .reward-claim-btn:disabled,
        .fi-theme-dark .reward-claim-btn:disabled {
            background: #1a2f38;
            color: #64748b;
            border: 1px solid #2d4048;
        }

        .claim-empty-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem 1rem;
            text-align: center;
            color: #0f172a;
        }

        .dark .claim-empty-box,
        .fi-theme-dark .claim-empty-box {
            background: #102028;
            border-color: #2d4048;
            color: #f8fafc;
        }

        /* --- Matrix Table --- */
        .matrix-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.65rem 0.75rem;
            width: 100%;
            box-sizing: border-box;
        }

        .dark .matrix-header-card,
        .fi-theme-dark .matrix-header-card {
            background: #102028;
            border-color: #2d4048;
        }

        .matrix-header-title {
            font-size: clamp(0.88rem, 2.5vw, 1rem);
            font-weight: 700;
            margin: 0.1rem 0 0 0;
            color: #0f172a;
        }

        .dark .matrix-header-title,
        .fi-theme-dark .matrix-header-title {
            color: #f8fafc;
        }

        .matrix-course-highlight {
            color: #0f4a43;
        }

        .dark .matrix-course-highlight,
        .fi-theme-dark .matrix-course-highlight {
            color: #2dd4bf;
        }

        .matrix-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
            font-size: 0.75rem;
            text-align: left;
        }

        .matrix-table th {
            background: #f8fafc;
            color: #475569;
            padding: 0.5rem 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.62rem;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .dark .matrix-table th,
        .fi-theme-dark .matrix-table th {
            background: #09181f;
            color: #94a3b8;
            border-bottom-color: #2d4048;
        }

        .matrix-table td {
            padding: 0.55rem 0.65rem;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            vertical-align: middle;
        }

        .dark .matrix-table td,
        .fi-theme-dark .matrix-table td {
            border-bottom-color: #1a2f38;
            color: #e2e8f0;
        }

        .matrix-mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.65rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            box-sizing: border-box;
            width: 100%;
        }

        .dark .matrix-mobile-card,
        .fi-theme-dark .matrix-mobile-card {
            background: #102028;
            border-color: #2d4048;
            color: #f8fafc;
        }

        .matrix-pill-strip {
            display: flex;
            gap: 0.3rem;
            align-items: center;
            flex-wrap: wrap;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.3rem 0.45rem;
        }

        .dark .matrix-pill-strip,
        .fi-theme-dark .matrix-pill-strip {
            background: #09181f;
            border-color: #2d4048;
        }

        .matrix-policy-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.65rem 0.75rem;
        }

        .dark .matrix-policy-box,
        .fi-theme-dark .matrix-policy-box {
            background: #09181f;
            border-color: #2d4048;
        }

        /* --- Modal Overlay & Card --- */
        .hub-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            box-sizing: border-box;
        }

        .hub-modal-card {
            max-width: 440px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            padding: 0.9rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            color: #0f172a;
        }

        .dark .hub-modal-card,
        .fi-theme-dark .hub-modal-card {
            background: #102028;
            border-color: #2d4048;
            color: #f8fafc;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .modal-stat-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.55rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.32rem;
            font-size: 0.72rem;
        }

        .dark .modal-stat-box,
        .fi-theme-dark .modal-stat-box {
            background: #09181f;
            border-color: #2d4048;
        }

        .modal-history-row {
            padding: 0.55rem 0.65rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .dark .modal-history-row,
        .fi-theme-dark .modal-history-row {
            background: #09181f;
            border-color: #2d4048;
        }

        /* Media Queries */
        @media (min-width: 641px) {
            .claim-inputs-row {
                grid-template-columns: 1fr 1fr;
                max-width: 420px;
            }
        }

        @media (min-width: 768px) {
            .matrix-mobile-view { display: none !important; }
            .matrix-desktop-view { display: block !important; }
        }

        @media (max-width: 767px) {
            .matrix-mobile-view {
                display: flex !important;
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
                box-sizing: border-box;
            }
            .matrix-desktop-view { display: none !important; }
        }

        @media (max-width: 640px) {
            .claim-hub-header {
                padding: 0.85rem;
            }
            .claim-tab-container {
                flex-direction: column;
                align-items: stretch;
                gap: 0.35rem;
            }
            .claim-tab-grid {
                max-width: 100% !important;
            }
            .claim-history-btn {
                width: 100% !important;
            }
        }

        @media (max-width: 480px) {
            .claim-stats-grid {
                grid-template-columns: 1fr 1fr;
                max-width: 100%;
            }
            .claim-stat-box-cap {
                grid-column: 1 / -1;
            }
            .rewards-grid-container {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    {{-- 1. Main Header Card with Live Gamification Metrics --}}
    <div class="claim-hub-header">
        <div class="claim-header-flex">
            {{-- Title & Info --}}
            <div class="claim-header-info">
                <div class="claim-badge-pill">
                    <span class="claim-badge-dot"></span>
                    <span>Gamification Hub</span>
                </div>
                <h1 class="claim-header-title">
                    Claim Hub & Point Rules
                </h1>
                <p class="claim-header-desc">
                    Redeem Thinker Coins (TC) for rewards set by instructors, and inspect active XP rules.
                </p>
            </div>

            {{-- Metric Widgets --}}
            <div class="claim-stats-grid">
                {{-- Spendable Coins --}}
                <div class="claim-stat-box">
                    <span class="stat-label">Spendable Coins</span>
                    <div class="stat-value-group">
                        <span class="stat-number">🪙 {{ number_format($user?->spendable_coins ?? 0) }}</span>
                        <span class="stat-unit">TC</span>
                    </div>
                    <span class="stat-subtext">Ready to spend</span>
                </div>

                {{-- Rank & Multiplier --}}
                <div class="claim-stat-box">
                    <span class="stat-label">Current Rank</span>
                    <div class="stat-value-group">
                        <span class="stat-number" style="font-size: 0.95rem;">{{ $userRank['rank_name'] }}</span>
                        <span class="hub-chip" style="background: rgba(255,255,255,0.1); color: #94a3b8; padding: 0.05rem 0.25rem; font-size: 0.58rem; border: 1px solid rgba(255,255,255,0.15);">
                            {{ $userRank['multiplier'] }}x
                        </span>
                    </div>
                    <span class="stat-subtext">{{ number_format($user?->lifetime_xp ?? 0) }} Lifetime XP</span>
                </div>

                {{-- Daily Cap Progress --}}
                <div class="claim-stat-box claim-stat-box-cap">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label">Daily Cap</span>
                        <span style="font-size: 0.65rem; font-weight: 600; color: #cbd5e1;">{{ $todayCoins }}/{{ $dailyCap }} TC</span>
                    </div>
                    <div style="width: 100%; height: 3px; background: rgba(255, 255, 255, 0.15); border-radius: 999px; margin-top: 0.25rem; overflow: hidden;">
                        <div style="width: {{ min(100, round(($todayCoins / max(1, $dailyCap)) * 100)) }}%; height: 100%; background: #10b981; border-radius: 999px;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.2rem; font-size: 0.6rem; color: #94a3b8;">
                        <span>Streak: {{ $user?->current_streak ?? 0 }}d</span>
                        <button wire:click="toggleHistoryModal" type="button" style="background: none; border: none; padding: 0; color: #38bdf8; cursor: pointer; font-weight: 600; font-size: 0.6rem;">
                            Claims ({{ $claimRequests->count() }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Alert Feedback Messages --}}
    @if ($successMessage)
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; display: flex; justify-content: space-between; align-items: center; padding: 0.45rem 0.65rem; width: 100%; box-sizing: border-box; border-radius: 6px; font-size: 0.75rem;">
            <span style="font-weight: 600;">{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)" type="button" style="background: none; border: none; cursor: pointer; color: #166534; padding: 0 0 0 0.4rem;">&times;</button>
        </div>
    @endif

    @if ($errorMessage)
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; display: flex; justify-content: space-between; align-items: center; padding: 0.45rem 0.65rem; width: 100%; box-sizing: border-box; border-radius: 6px; font-size: 0.75rem;">
            <span style="font-weight: 600;">{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)" type="button" style="background: none; border: none; cursor: pointer; color: #991b1b; padding: 0 0 0 0.4rem;">&times;</button>
        </div>
    @endif

    {{-- 3. Navigation Tab Bar --}}
    <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 0.35rem; width: 100%; box-sizing: border-box;" class="border-b border-slate-200 dark:border-slate-800">
        <div class="claim-tab-container">
            <div class="claim-tab-grid">
                <button
                    wire:click="switchTab('rewards')"
                    type="button"
                    class="claim-tab-btn {{ $activeTab === 'rewards' ? 'active' : '' }}"
                >
                    <svg style="width: 0.8rem; height: 0.8rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V3.5A2.5 2.5 0 109.5 6H12zm0 0v13m0-13V3.5A2.5 2.5 0 1114.5 6H12zM3 12h18M4 8h16a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1z"/></svg>
                    <span>Course Rewards</span>
                    <span class="hub-chip {{ $activeTab === 'rewards' ? 'hub-chip-primary' : 'hub-chip-gray' }}" style="padding: 0.05rem 0.25rem; font-size: 0.58rem;">{{ $items->total() }}</span>
                </button>

                <button
                    wire:click="switchTab('matrix')"
                    type="button"
                    class="claim-tab-btn {{ $activeTab === 'matrix' ? 'active' : '' }}"
                >
                    <svg style="width: 0.8rem; height: 0.8rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Point Earning Matrix</span>
                    <span class="hub-chip {{ $activeTab === 'matrix' ? 'hub-chip-primary' : 'hub-chip-gray' }}" style="padding: 0.05rem 0.25rem; font-size: 0.58rem;">30% TC</span>
                </button>
            </div>

            <div>
                <button
                    wire:click="toggleHistoryModal"
                    type="button"
                    class="claim-history-btn"
                >
                    <svg style="width: 0.8rem; height: 0.8rem; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>My Claims History</span>
                    @if ($claimRequests->isNotEmpty())
                        <span class="hub-chip hub-chip-primary" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $claimRequests->count() }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: REWARDS STORE                                                      --}}
    {{-- ========================================================================= --}}
    @if ($activeTab === 'rewards')
        {{-- Controls Box --}}
        <section class="claim-section-card">
            <div class="claim-controls-box">
                {{-- Category Filters --}}
                <div class="chip-scroll-bar" style="flex: 1; min-width: 0;">
                    @php
                        $categories = [
                            'all' => ['label' => 'All Items', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            'data' => ['label' => 'Data & Airtime', 'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0'],
                            'merch' => ['label' => 'Swag', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                            'voucher' => ['label' => 'Vouchers', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                            'perk' => ['label' => 'Perks', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ];
                    @endphp
                    @foreach ($categories as $catKey => $catData)
                        <button
                            wire:click="$set('selectedCategory', '{{ $catKey }}')"
                            type="button"
                            class="chip-scroll-btn {{ $selectedCategory === $catKey ? 'active' : '' }}"
                        >
                            <svg style="width: 0.7rem; height: 0.7rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $catData['icon'] }}"/></svg>
                            <span>{{ $catData['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Course Selector & Search --}}
                <div class="claim-inputs-row">
                    @if ($enrolledCourses->isNotEmpty())
                        <select
                            wire:model.live="selectedCourse"
                            class="hub-input"
                            style="font-weight: 600;"
                        >
                            <option value="all">All Enrolled Courses</option>
                            @foreach ($enrolledCourses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }}</option>
                            @endforeach
                            <option value="general">General Platform</option>
                        </select>
                    @endif

                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search rewards..."
                        class="hub-input"
                    />
                </div>
            </div>
        </section>

        {{-- Reward Items Grid --}}
        <div class="rewards-grid-container">
            @forelse ($items as $item)
                @php
                    $canAfford = ($user?->spendable_coins ?? 0) >= $item->coin_cost;
                    $inStock = $item->isInStock();
                @endphp
                <div class="reward-card">
                    {{-- Visual Top Header --}}
                    <div class="reward-visual-box">
                        {{-- Category Badge --}}
                        <span style="position: absolute; top: 6px; left: 6px; font-size: 0.58rem;" class="hub-chip hub-chip-gray">
                            {{ match($item->category) {
                                'data' => 'Data',
                                'merch' => 'Swag',
                                'voucher' => 'Voucher',
                                'perk' => 'Perk',
                                default => ucfirst($item->category),
                            } }}
                        </span>

                        {{-- Stock Badge --}}
                        <span style="position: absolute; top: 6px; right: 6px; font-size: 0.58rem;" class="hub-chip {{ $inStock ? ($item->isUnlimited() ? 'hub-chip-green' : 'hub-chip-blue') : 'hub-chip-red' }}">
                            @if ($item->isUnlimited())
                                In Stock
                            @elseif ($item->stock_quantity > 0)
                                {{ $item->stock_quantity }} left
                            @else
                                Out of Stock
                            @endif
                        </span>

                        {{-- Image / Icon --}}
                        @if ($item->image_path)
                            <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" style="max-height: 48px; max-width: 100%; object-fit: contain;" />
                        @else
                            <div class="reward-icon-box">
                                @if($item->category === 'data')
                                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @elseif($item->category === 'merch')
                                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                @elseif($item->category === 'voucher')
                                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                @else
                                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Card Content Details --}}
                    <div style="padding: 0.6rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 0.5rem;">
                        <div>
                            @if ($item->course)
                                <span class="hub-chip hub-chip-primary" style="margin-bottom: 0.2rem; font-size: 0.58rem;">
                                    {{ $item->course->title }}
                                </span>
                            @else
                                <span class="hub-chip hub-chip-gray" style="margin-bottom: 0.2rem; font-size: 0.58rem;">
                                    Platform Reward
                                </span>
                            @endif

                            <h3 class="reward-title">{{ $item->title }}</h3>

                            @if ($item->description)
                                <p class="reward-desc">
                                    {{ $item->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Footer & Claim Action --}}
                        <div class="reward-footer">
                            <div style="display: flex; align-items: center; gap: 0.2rem;">
                                <span style="font-size: 0.9rem; font-weight: 700; color: #d97706;">🪙 {{ number_format($item->coin_cost) }}</span>
                                <span style="font-size: 0.62rem; font-weight: 600; color: #64748b;" class="dark:text-slate-400">TC</span>
                            </div>

                            <button
                                wire:click="openRedeemModal({{ $item->id }})"
                                type="button"
                                @disabled(! $inStock || ! $canAfford)
                                class="reward-claim-btn"
                            >
                                @if (! $inStock)
                                    Out of Stock
                                @elseif (! $canAfford)
                                    Need TC
                                @else
                                    Claim Item
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="claim-empty-box" style="grid-column: 1 / -1;">
                    <svg style="width: 1.75rem; height: 1.75rem; color: #94a3b8; margin: 0 auto 0.3rem auto; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0;">No Course Rewards Available</h3>
                    <p style="max-width: 360px; margin: 0.2rem auto 0 auto; font-size: 0.72rem; color: #64748b;" class="dark:text-slate-400">
                        There are currently no listed items matching this category or course filter.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div style="margin-top: 0.25rem; width: 100%; overflow-x: auto;">
                {{ $items->links() }}
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: POINT EARNING MATRIX                                               --}}
    {{-- ========================================================================= --}}
    @elseif ($activeTab === 'matrix')
        <div style="display: flex; flex-direction: column; gap: 0.65rem; width: 100%;">
            {{-- Header Matrix Control Card --}}
            <section class="matrix-header-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="font-size: 0.6rem; font-weight: 700; color: #0f4a43; text-transform: uppercase; letter-spacing: 0.04em;" class="dark:text-teal-400">Gamification Rules</span>
                        <h2 class="matrix-header-title">
                            Earning Matrix for:
                            <span class="matrix-course-highlight">{{ $targetCourse ? $targetCourse->title : 'Global Platform Default' }}</span>
                        </h2>
                        <p style="margin-top: 0.15rem; font-size: 0.72rem; color: #64748b;" class="dark:text-slate-400">
                            Active XP values set by instructors. <strong>Coins (TC) generate at 30% of XP</strong>.
                        </p>
                    </div>

                    {{-- Course Switcher --}}
                    @if ($enrolledCourses->isNotEmpty())
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; width: 100%; max-width: 260px;">
                            <label style="font-size: 0.7rem; font-weight: 600; color: #64748b; white-space: nowrap;" class="dark:text-slate-400">Course:</label>
                            <select
                                wire:model.live="matrixCourseId"
                                class="hub-input"
                                style="font-weight: 600;"
                            >
                                @foreach ($enrolledCourses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Category Filter Chips --}}
                <div class="chip-scroll-bar" style="margin-top: 0.45rem; border-top: 1px solid #f1f5f9; padding-top: 0.4rem;" class="dark:border-slate-800">
                    @foreach ($matrixCategories as $matKey => $matLabel)
                        <button
                            wire:click="$set('matrixCategory', '{{ $matKey }}')"
                            type="button"
                            class="chip-scroll-btn {{ $matrixCategory === $matKey ? 'active' : '' }}"
                        >
                            {{ $matLabel }}
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- 1. MOBILE CARDS VIEW --}}
            <div class="matrix-mobile-view">
                @forelse ($effectiveMatrix as $row)
                    <div class="matrix-mobile-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.35rem;">
                            <div>
                                <h3 style="font-size: 0.8rem; font-weight: 700; margin: 0; line-height: 1.25;">
                                    {{ $row['activity_name'] ?? ($row['label'] ?? ucfirst(str_replace('_', ' ', $row['activity_key'] ?? 'Activity'))) }}
                                </h3>
                                @if (! empty($row['activity_key']) && $row['activity_key'] !== 'custom')
                                    <span style="font-size: 0.6rem; color: #64748b; font-family: monospace;" class="dark:text-slate-400">Key: {{ $row['activity_key'] }}</span>
                                @endif
                            </div>

                            <div>
                                @if ($row['enabled'] ?? true)
                                    <span class="hub-chip hub-chip-green" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">Active</span>
                                @else
                                    <span class="hub-chip hub-chip-red" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">Disabled</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <span class="hub-chip hub-chip-gray" style="font-size: 0.6rem;">
                                {{ $row['category'] ?? 'General' }}
                            </span>
                        </div>

                        <div class="matrix-pill-strip">
                            <span class="hub-chip hub-chip-primary" style="font-size: 0.65rem; font-weight: 700;">
                                +{{ $row['xp'] ?? 0 }} XP
                            </span>
                            <span class="hub-chip hub-chip-amber" style="font-size: 0.65rem; font-weight: 700;">
                                {{ $row['coins'] ?? (int) round(((float) ($row['xp'] ?? 0)) * 0.30) }} TC
                            </span>
                            <span style="font-size: 0.6rem; color: #64748b; margin-left: auto;" class="dark:text-slate-400">
                                30% TC
                            </span>
                        </div>

                        <div style="font-size: 0.65rem; color: #64748b;" class="dark:text-slate-400">
                            {{ $row['limit'] ?? 'Standard activity' }}
                        </div>
                    </div>
                @empty
                    <div class="claim-empty-box">
                        <svg style="width: 1.75rem; height: 1.75rem; color: #94a3b8; margin: 0 auto 0.3rem auto; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0;">No Point Earning Rules Set</h3>
                        <p style="max-width: 360px; margin: 0.2rem auto 0 auto; font-size: 0.72rem; color: #64748b;" class="dark:text-slate-400">
                            The instructor or admin has not configured custom gamification point rules for this course yet.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- 2. DESKTOP DATA TABLE --}}
            <section class="matrix-desktop-view matrix-header-card" style="padding: 0; overflow-x: auto;">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th>Action / Activity</th>
                            <th>Category</th>
                            <th>Base XP</th>
                            <th>Coins (30% TC)</th>
                            <th>Constraints</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($effectiveMatrix as $row)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">
                                        {{ $row['activity_name'] ?? ($row['label'] ?? ucfirst(str_replace('_', ' ', $row['activity_key'] ?? 'Activity'))) }}
                                    </div>
                                    @if (! empty($row['activity_key']) && $row['activity_key'] !== 'custom')
                                        <div style="font-size: 0.6rem; color: #64748b;" class="dark:text-slate-400">
                                            Key: <code>{{ $row['activity_key'] }}</code>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="hub-chip hub-chip-gray" style="font-size: 0.62rem;">
                                        {{ $row['category'] ?? 'General' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="hub-chip hub-chip-primary" style="font-size: 0.65rem; font-weight: 700;">
                                        +{{ $row['xp'] ?? 0 }} XP
                                    </span>
                                </td>
                                <td>
                                    <span class="hub-chip hub-chip-amber" style="font-size: 0.65rem; font-weight: 700;">
                                        {{ $row['coins'] ?? (int) round(((float) ($row['xp'] ?? 0)) * 0.30) }} TC
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 0.65rem; color: #64748b;" class="dark:text-slate-400">
                                        {{ $row['limit'] ?? 'Standard activity' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($row['enabled'] ?? true)
                                        <span class="hub-chip hub-chip-green" style="font-size: 0.6rem;">Active</span>
                                    @else
                                        <span class="hub-chip hub-chip-red" style="font-size: 0.6rem;">Disabled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem 1.25rem; color: #64748b;">
                                    <div style="font-size: 0.85rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--hub-ink, #0f172a);">No Point Earning Rules Set</div>
                                    <p style="font-size: 0.72rem; color: #64748b; margin: 0;">The instructor or admin has not configured custom gamification point rules for this course yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            {{-- Policy Information Box --}}
            <section class="matrix-policy-box">
                <div style="display: flex; gap: 0.4rem; align-items: flex-start;">
                    <svg style="width: 1rem; height: 1rem; color: #0f4a43; flex-shrink: 0; margin-top: 0.05rem;" class="dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <h4 style="font-size: 0.78rem; font-weight: 700; margin: 0;">Gamification Policy Notes</h4>
                        <ul style="margin: 0.15rem 0 0 0; padding-left: 0.85rem; font-size: 0.7rem; color: #475569; line-height: 1.35;" class="dark:text-slate-400">
                            <li><strong>Lifetime XP:</strong> Non-spendable points determining Student Rank Tier.</li>
                            <li><strong>Thinker Coins (TC):</strong> Earned at <strong>30% of XP</strong> for redeeming rewards.</li>
                            <li><strong>Daily Cap:</strong> Max 150 spendable TC per calendar day.</li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: CLAIM CONFIRMATION MODAL                                         --}}
    {{-- ========================================================================= --}}
    @if ($showModal && $selectedItem)
        <div class="hub-modal-overlay">
            <div class="hub-modal-card">
                {{-- Modal Header --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.45rem; margin-bottom: 0.65rem;" class="dark:border-slate-800">
                    <div>
                        <span style="margin: 0; font-size: 0.6rem; font-weight: 700; color: #0f4a43; text-transform: uppercase;" class="dark:text-teal-400">Claim Confirmation</span>
                        <h3 style="font-size: 0.9rem; font-weight: 700; margin-top: 0.05rem;">{{ $selectedItem->title }}</h3>
                    </div>
                    <button wire:click="closeModal" type="button" style="background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #64748b; line-height: 1; padding: 0;">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    <div class="modal-stat-box">
                        <div>
                            <span style="color: #64748b;" class="dark:text-slate-400">Claim Cost:</span>
                            <div style="font-weight: 700; font-size: 0.88rem; color: #d97706; margin-top: 0.05rem;">
                                {{ number_format($selectedItem->coin_cost) }} TC
                            </div>
                        </div>
                        <div>
                            <span style="color: #64748b;" class="dark:text-slate-400">Remaining After:</span>
                            <div style="font-weight: 700; font-size: 0.88rem; margin-top: 0.05rem;">
                                {{ number_format(max(0, ($user?->spendable_coins ?? 0) - $selectedItem->coin_cost)) }} TC
                            </div>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 600; margin-bottom: 0.15rem;">
                            WhatsApp / Phone Number:
                        </label>
                        <input
                            wire:model="phoneNumber"
                            type="text"
                            placeholder="e.g. +260 97 123 4567"
                            class="hub-input"
                        />
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 600; margin-bottom: 0.15rem;">
                            Fulfillment Notes (Optional):
                        </label>
                        <textarea
                            wire:model="deliveryNotes"
                            rows="2"
                            placeholder="Campus pickup preference, network carrier, or size."
                            class="hub-input"
                        ></textarea>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="border-top: 1px solid #e2e8f0; padding-top: 0.65rem; margin-top: 0.75rem; display: flex; justify-content: flex-end; gap: 0.35rem;" class="dark:border-slate-800">
                    <button
                        wire:click="closeModal"
                        type="button"
                        class="claim-tab-btn"
                        style="padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 600; font-size: 0.72rem; cursor: pointer; flex: initial;"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="redeemItem"
                        type="button"
                        class="reward-claim-btn"
                        style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.72rem; cursor: pointer;"
                    >
                        Confirm Claim ({{ number_format($selectedItem->coin_cost) }} TC)
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: MY CLAIMS HISTORY MODAL                                          --}}
    {{-- ========================================================================= --}}
    @if ($showHistoryModal)
        <div class="hub-modal-overlay">
            <div class="hub-modal-card" style="max-width: 520px;">
                {{-- Modal Header --}}
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.45rem; margin-bottom: 0.65rem;" class="dark:border-slate-800">
                    <div>
                        <span style="margin: 0; font-size: 0.6rem; font-weight: 700; color: #0f4a43; text-transform: uppercase;" class="dark:text-teal-400">Account History</span>
                        <h3 style="font-size: 0.9rem; font-weight: 700; margin-top: 0.05rem;">My Claim Requests</h3>
                    </div>
                    <button wire:click="toggleHistoryModal" type="button" style="background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #64748b; line-height: 1; padding: 0;">&times;</button>
                </div>

                {{-- Claims List --}}
                <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                    @forelse ($claimRequests as $claim)
                        <div class="modal-history-row">
                            <div style="min-width: 0; flex: 1;">
                                <div style="display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap;">
                                    <h4 style="font-size: 0.78rem; font-weight: 700; margin: 0;">{{ $claim->claimItem?->title ?? 'Claimed Item' }}</h4>
                                    @if ($claim->claimItem?->course)
                                        <span class="hub-chip hub-chip-primary" style="font-size: 0.55rem;">
                                            {{ $claim->claimItem->course->title }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 0.65rem; color: #64748b; margin-top: 0.1rem;" class="dark:text-slate-400">
                                    {{ $claim->created_at->format('M d, Y • h:i A') }} • {{ number_format($claim->coins_spent) }} TC
                                </div>
                                @if ($claim->admin_notes)
                                    <div style="font-size: 0.65rem; color: #0f4a43; margin-top: 0.1rem; overflow-wrap: anywhere;" class="dark:text-teal-300">
                                        <strong>Instructor Note:</strong> {{ $claim->admin_notes }}
                                    </div>
                                @endif
                            </div>

                            <div style="flex-shrink: 0;">
                                @if ($claim->isFulfilled())
                                    <span class="hub-chip hub-chip-green" style="font-size: 0.58rem;">Fulfilled</span>
                                @elseif ($claim->isApproved())
                                    <span class="hub-chip hub-chip-blue" style="font-size: 0.58rem;">Approved</span>
                                @elseif ($claim->isRejected())
                                    <span class="hub-chip hub-chip-red" style="font-size: 0.58rem;">Rejected</span>
                                @else
                                    <span class="hub-chip hub-chip-amber" style="font-size: 0.58rem;">Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 1.25rem 0.5rem; color: #64748b;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: #94a3b8; margin: 0 auto 0.25rem auto; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p style="margin: 0; font-weight: 600; font-size: 0.75rem;">No reward claims submitted yet.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Modal Footer --}}
                <div style="border-top: 1px solid #e2e8f0; padding-top: 0.65rem; margin-top: 0.75rem; display: flex; justify-content: flex-end;" class="dark:border-slate-800">
                    <button
                        wire:click="toggleHistoryModal"
                        type="button"
                        class="claim-history-btn"
                        style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.72rem; cursor: pointer;"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>