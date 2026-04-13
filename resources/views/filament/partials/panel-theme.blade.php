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
        width: 220px;
        border: 1px solid var(--hub-border);
        border-radius: 999px;
        padding: 0.38rem 0.68rem;
        font-size: 0.76rem;
        background: var(--hub-surface);
        color: var(--hub-ink);
    }

    .hub-top-search-wrap { display: none; }

    @media (min-width: 900px) {
        .fi-topbar {
            position: relative;
        }

        .hub-top-search-wrap {
            display: block;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
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
</style>
