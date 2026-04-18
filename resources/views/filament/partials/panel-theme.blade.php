<style>
    :root {
        --hub-bg: #f8fafc;
        --hub-card: #ffffff;
        --hub-surface: #ffffff;
        --hub-surface-soft: #f8fafc;
        --hub-border: #e2e8f0;
        --hub-ink: #0f172a;
        --hub-muted: #64748b;
        --hub-primary: #0f766e;
        --hub-primary-soft: #ccfbf1;
        --hub-accent: #f59e0b;
        --hub-danger: #dc2626;
        --hub-success: #16a34a;
    }

    .dark {
        --hub-bg: #0b1220;
        --hub-card: #111827;
        --hub-surface: #111827;
        --hub-surface-soft: #0f172a;
        --hub-border: #253247;
        --hub-ink: #e5e7eb;
        --hub-muted: #94a3b8;
        --hub-primary-soft: #134e4a;
    }

    .hub-shell {
        display: grid;
        gap: 0.75rem;
        max-width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
        word-break: break-word;
    }

    .hub-grid {
        display: grid;
        gap: 0.75rem;
    }

    .hub-grid-2 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .hub-grid-3 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .hub-grid-4 { grid-template-columns: repeat(1, minmax(0, 1fr)); }

    @media (min-width: 900px) {
        .hub-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .hub-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .hub-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }

    .hub-card {
        border: 1px solid var(--hub-border);
        background: var(--hub-card);
        border-radius: 12px;
        padding: 0.8rem;
        box-shadow: none;
        box-sizing: border-box;
        max-width: 100%;
    }

    .hub-card-dark {
        border: 1px solid #0f766e;
        background: #0f766e;
        color: #ecfeff;
    }

    .hub-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--hub-ink);
    }

    .hub-eyebrow {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--hub-muted);
        font-weight: 700;
    }

    .hub-copy {
        margin-top: 0.3rem;
        color: var(--hub-muted);
        font-size: 0.82rem;
        line-height: 1.35;
    }

    .hub-metric {
        margin-top: 0.35rem;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--hub-ink);
    }

    .hub-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.22rem 0.55rem;
        font-size: 0.7rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .hub-chip-primary { background: var(--hub-primary-soft); color: #0f766e; border-color: #5eead4; }
    .hub-chip-blue { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .hub-chip-amber { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .hub-chip-green { background: #dcfce7; color: #166534; border-color: #86efac; }
    .hub-chip-red { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .hub-chip-gray { background: #e5e7eb; color: #374151; border-color: #d1d5db; }

    .hub-btn {
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0.42rem 0.68rem;
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .hub-btn-primary { background: var(--hub-primary); color: #fff; }
    .hub-btn-primary:hover { background: #115e59; }
    .hub-btn-muted { background: var(--hub-surface); color: var(--hub-ink); border-color: var(--hub-border); }
    .hub-btn-muted:hover { background: var(--hub-surface-soft); }
    .hub-btn-danger { background: #fff1f2; color: #be123c; border-color: #fecdd3; }

    .hub-input, .hub-textarea {
        width: 100%;
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 0.45rem 0.6rem;
        font-size: 0.8rem;
        background: var(--hub-surface);
        color: var(--hub-ink);
    }

    .hub-textarea { min-height: 80px; resize: vertical; }

    .hub-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--hub-border);
        border-radius: 10px;
        overflow: hidden;
        font-size: 0.8rem;
        background: var(--hub-surface);
    }

    .hub-table th,
    .hub-table td {
        text-align: left;
        padding: 0.5rem 0.6rem;
        border-bottom: 1px solid var(--hub-border);
        color: var(--hub-ink);
    }

    .hub-table thead th {
        background: var(--hub-surface-soft);
        color: var(--hub-muted);
        font-weight: 700;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .hub-table tbody tr:last-child td { border-bottom: none; }

    .hub-stack { display: grid; gap: 0.55rem; }

    .hub-top-search {
        width: 100%;
        border: 1px solid var(--hub-border);
        border-radius: 999px;
        padding: 0.38rem 0.68rem;
        font-size: 0.76rem;
        background: var(--hub-surface);
        color: var(--hub-ink);
    }

    /* Combined search + notification group */
    .hub-top-bar-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.5rem;
    }

    .hub-top-search-form {
        flex: 1;
        min-width: 0;
    }

    /* Mobile: search+notif centered, profile pushed right */
    @media (max-width: 899px) {
        .hub-top-bar-group {
            flex: 1;
            min-width: 0;
            gap: 0.25rem;
            padding: 0 0.25rem;
        }

        .hub-top-search {
            width: 100%;
            font-size: 0.72rem;
            padding: 0.32rem 0.55rem;
        }

        /* Reorder: push profile (fi-topbar-end) to the far right */
        .fi-topbar > .fi-topbar-end {
            order: 99;
            margin-inline-start: 0;
            flex-shrink: 0;
        }
    }

    /* Desktop: centre the group in the topbar */
    @media (min-width: 900px) {
        .fi-topbar {
            position: relative;
        }

        .hub-top-bar-group {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
            padding: 0;
        }

        .hub-top-search {
            width: clamp(240px, 28vw, 360px);
        }
    }

    [id^="overview-"] {
        scroll-margin-top: 6rem;
    }

    [x-cloak] {
        display: none !important;
    }

    .hub-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.65rem;
    }

    .hub-calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.35rem;
    }

    .hub-day {
        text-align: center;
        border-radius: 6px;
        padding: 0.28rem 0.1rem;
        font-size: 0.68rem;
        background: var(--hub-surface-soft);
        color: var(--hub-muted);
        border: 1px solid var(--hub-border);
    }

    .hub-day-today { background: #0f766e; color: #fff; border-color: #115e59; }
    .hub-day-due { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .hub-day-past { opacity: 0.45; }
    .hub-day-selected { outline: 2px solid #3b82f6; outline-offset: 1px; border-radius: 8px; }
    .hub-day:hover { background: var(--hub-surface); }

    /* Keep admin data tables denser and closer to Filament compact layout. */
    .fi-panel-admin .hub-table {
        font-size: 0.76rem;
    }

    .fi-panel-admin .hub-table th,
    .fi-panel-admin .hub-table td {
        padding: 0.42rem 0.5rem;
    }

    .fi-panel-admin .fi-ta-table,
    .fi-panel-admin [class*="fi-ta-table"] {
        font-size: 0.78rem;
    }

    .fi-panel-admin .fi-ta-header-cell,
    .fi-panel-admin .fi-ta-cell,
    .fi-panel-admin [class*="fi-ta-header-cell"],
    .fi-panel-admin [class*="fi-ta-cell"] {
        padding-top: 0.42rem;
        padding-bottom: 0.42rem;
    }

    .fi-panel-admin .fi-input,
    .fi-panel-admin .fi-select-input,
    .fi-panel-admin .fi-ta-search-field input {
        min-height: 2rem;
        font-size: 0.78rem;
    }

    .dark .hub-chip-primary {
        color: #99f6e4;
        border-color: #134e4a;
    }

    .dark .hub-chip-blue {
        background: #1e2a4a;
        color: #93c5fd;
        border-color: #1e3a5f;
    }

    .dark .hub-chip-gray {
        background: #1f2937;
        color: #cbd5e1;
        border-color: #334155;
    }

    .dark .hub-chip-amber {
        background: #3b2f13;
        color: #fcd34d;
        border-color: #5b4b1f;
    }

    .dark .hub-chip-green {
        background: #113526;
        color: #86efac;
        border-color: #166534;
    }

    .dark .hub-chip-red {
        background: #3a1418;
        color: #fca5a5;
        border-color: #7f1d1d;
    }

    /* ============================================================ */
    /* GLOBAL MOBILE RESPONSIVE UTILITIES                           */
    /* ============================================================ */
    .hub-desktop-only { display: block !important; }
    .hub-mobile-only  { display: none !important; }

    /* Stats grid: 4 columns on desktop by default */
    .hub-stats-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    @media (max-width: 768px) {
        .hub-desktop-only { display: none !important; }
        .hub-mobile-only  { display: block !important; }

        /* Filament layout: prevent horizontal overflow on mobile */
        .fi-layout,
        .fi-main-ctn,
        .fi-main,
        .fi-page,
        .fi-page-header-main-ctn,
        .fi-page-main,
        .fi-page-content {
            max-width: 100vw !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        /* Override Filament max-width utility on mobile */
        .fi-main[class*="fi-width-"] {
            max-width: 100% !important;
        }

        /* Constrain Filament page header padding */
        .fi-header {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        /* Stack grid items vertically on mobile */
        .hub-grid-3 > .hub-card[style*="grid-column: span 2"] {
            grid-column: span 1 !important;
        }

        /* Make hub-table scroll horizontal on mobile */
        .hub-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Tighter padding on cards */
        .hub-card { padding: 0.65rem 0.75rem; }

        /* Hub links wrap nicely */
        .hub-links { gap: 0.3rem; }
        .hub-links .hub-btn { font-size: 0.7rem; padding: 0.35rem 0.55rem; }

        /* Calendar day cells */
        .hub-day { font-size: 0.62rem; padding: 0.22rem 0; }

        /* Material filters: stack vertically on mobile */
        .hub-filter-row {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem !important;
        }

        .hub-filter-row select {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        /* Mobile card improvements */
        .hub-mobile-card {
            padding: 0.85rem 1rem;
        }

        .hub-mobile-card-actions {
            gap: 0.25rem;
        }

        .hub-action-btn {
            font-size: 0.7rem;
            padding: 0.28rem 0.5rem;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Admin/instructor stat widgets: 2 columns on tablet */
        .hub-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.5rem !important;
        }

        .hub-stats-grid .hub-card {
            padding: 0.6rem 0.7rem;
        }

        .hub-metric {
            font-size: 1.1rem;
        }

        /* Filament built-in table responsiveness */
        .fi-ta-table {
            font-size: 0.72rem;
        }

        .fi-ta-header-cell,
        .fi-ta-cell {
            padding: 0.35rem 0.4rem !important;
        }

        /* Filament action modals: full width on mobile */
        .fi-modal-window {
            max-width: calc(100vw - 1rem) !important;
            margin: 0.5rem !important;
        }

        /* Filament form components: prevent overflow */
        .fi-fo-field-wrp,
        .fi-fo-component-ctn {
            max-width: 100% !important;
            overflow-x: hidden;
        }

        /* Filament action buttons in table rows */
        .fi-ta-actions {
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        /* Fee row stacking on mobile (public course detail page) */
        .hub-fee-row {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.25rem !important;
        }

        /* Cookie table scroll on mobile */
        .hub-legal-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Mobile card for replacing tables on small screens */
    .hub-mobile-card {
        border: 1px solid var(--hub-border);
        border-radius: 10px;
        padding: 0.7rem 0.85rem;
        background: var(--hub-card);
        margin-bottom: 0.5rem;
        box-sizing: border-box;
        max-width: 100%;
        overflow: hidden;
    }

    .hub-mobile-card-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .hub-mobile-card-meta {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        flex-wrap: wrap;
    }

    .hub-mobile-card-actions {
        display: flex;
        gap: 0.35rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .hub-action-btn {
        background: none;
        border: 1px solid var(--hub-border);
        border-radius: 6px;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        cursor: pointer;
        font-weight: 600;
    }

    .hub-span-2 { grid-column: span 2; }

    @media (max-width: 768px) {
        .hub-span-2 { grid-column: span 1 !important; }

        /* ---- Quiz Centre listing ---- */
        .hub-quiz-listing .hub-mobile-card {
            padding: 0.75rem 0.85rem;
        }

        .hub-quiz-listing .hub-mobile-card-row {
            align-items: center;
        }

        .hub-quiz-listing .hub-mobile-card-meta {
            gap: 0.5rem;
            font-size: 0.75rem;
        }

        .hub-quiz-listing .hub-mobile-card-meta span {
            white-space: nowrap;
        }

        .hub-quiz-listing .hub-mobile-card-actions {
            margin-top: 0.6rem;
        }

        .hub-quiz-listing .hub-mobile-card-actions .hub-action-btn {
            flex: 1;
            text-align: center;
            padding: 0.45rem 0.5rem;
            font-size: 0.78rem;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ---- Quiz take-quiz page ---- */
        .hub-quiz-timer-bar {
            padding: 0.4rem 0.65rem !important;
            font-size: 0.75rem !important;
            border-radius: 8px !important;
        }

        .hub-quiz-nav-dots {
            gap: 0.25rem !important;
        }

        .hub-quiz-nav-dots button {
            width: 24px !important;
            height: 24px !important;
            font-size: 0.62rem !important;
        }

        .hub-quiz-option label {
            padding: 0.5rem 0.65rem !important;
            font-size: 0.82rem !important;
        }

        .hub-quiz-question-card {
            padding: 0.85rem !important;
        }

        /* ---- Schedule calendar ---- */
        .hub-calendar-table {
            min-width: 320px !important;
        }

        .hub-calendar-table td {
            height: 3.5rem !important;
            padding: 0.15rem !important;
        }

        .hub-calendar-table th {
            font-size: 0.6rem !important;
            padding: 0.25rem 0.1rem !important;
        }

        .hub-calendar-session {
            font-size: 0.5rem !important;
            padding: 0.1rem 0.15rem !important;
        }

        .hub-calendar-day-num {
            font-size: 0.6rem !important;
        }

        /* Schedule legend */
        .hub-legend {
            gap: 0.5rem !important;
            font-size: 0.6rem !important;
        }

        /* Schedule/reschedule forms: stack inputs */
        .hub-form-row {
            flex-direction: column !important;
            gap: 0.4rem !important;
        }

        .hub-form-row > div {
            width: 100% !important;
        }

        .hub-form-row input[type="date"],
        .hub-form-row input[type="time"] {
            width: 100% !important;
        }

        /* Schedule filter row */
        .hub-schedule-filters {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.4rem !important;
        }

        .hub-schedule-filters > div {
            width: 100% !important;
        }

        .hub-schedule-filters select {
            width: 100% !important;
        }

        .hub-schedule-filters .hub-filter-count {
            margin-left: 0 !important;
            align-self: flex-start !important;
        }

        /* Session cards meta row */
        .hub-session-meta {
            gap: 0.5rem !important;
            font-size: 0.72rem !important;
        }

        /* Course progress grid */
        .hub-progress-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Extra-small screens (≤ 480px) */
    @media (max-width: 480px) {
        .hub-shell { gap: 0.5rem; }

        .hub-card { padding: 0.55rem 0.65rem; }

        .hub-eyebrow { font-size: 0.58rem; }
        .hub-title { font-size: 0.95rem !important; }
        .hub-copy { font-size: 0.76rem; }

        .hub-mobile-card {
            padding: 0.65rem 0.75rem;
            margin-bottom: 0.4rem;
        }

        .hub-mobile-card-row p:first-child {
            font-size: 0.82rem !important;
        }

        .hub-mobile-card-meta {
            gap: 0.35rem;
            font-size: 0.72rem;
        }

        .hub-action-btn {
            font-size: 0.68rem;
            padding: 0.25rem 0.45rem;
            min-height: 36px;
        }

        .hub-chip {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.4rem !important;
        }

        /* Stats grid stacks to 1 column on very small screens */
        .hub-stats-grid {
            grid-template-columns: 1fr !important;
        }

        /* All hub buttons get touch-friendly minimum height */
        .hub-btn {
            min-height: 36px;
        }

        /* Quiz centre: stack meta vertically on very small screens */
        .hub-quiz-listing .hub-mobile-card-meta {
            flex-direction: column;
            gap: 0.15rem;
        }

        .hub-quiz-listing .hub-mobile-card-actions .hub-action-btn {
            width: 100%;
        }
    }

    /* ============================================================ */
    /* NOTIFICATION BELL                                             */
    /* ============================================================ */
    .hub-notif-bell:hover { color: var(--hub-ink); }
    .hub-notif-bell:focus { outline: 2px solid var(--hub-primary); outline-offset: 2px; border-radius: 6px; }

    /* Close button row — hidden on desktop */
    .hub-notif-close-row { display: none; }

    /* Desktop: overlay is just a positioner, no backdrop */
    .hub-notif-overlay {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        z-index: 100;
    }

    .hub-notif-panel {
        width: 340px;
        max-height: 420px;
        background: var(--hub-card);
        border: 1px solid var(--hub-border);
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Mobile: overlay becomes fullscreen backdrop with centered panel */
    @media (max-width: 899px) {
        .hub-notif-close-row {
            display: flex;
            justify-content: flex-end;
            padding: 0.5rem 0.75rem 0;
        }

        .hub-notif-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.45);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hub-notif-panel {
            width: calc(100% - 2rem);
            max-width: 380px;
            max-height: 75vh;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
            z-index: 9999;
        }
    }
</style>
