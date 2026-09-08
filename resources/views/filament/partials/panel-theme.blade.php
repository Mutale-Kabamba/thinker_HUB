<style>
    :root {
        --color-canvas: #f8fafc;
        --color-surface: #ffffff;
        --color-surface-hover: #f1f5f9;
        --color-border: #e2e8f0;
        --color-text-main: #0f172a;
        --color-text-muted: #64748b;
        
        --color-brand-primary: #0d9488;
        --color-brand-primary-hover: #0f766e;
        --color-brand-secondary: #6366f1;
        
        --badge-mint-bg: #ecfdf5;
        --badge-mint-text: #059669;
        --badge-coral-bg: #fff1f2;
        --badge-coral-text: #e11d48;
        --badge-amber-bg: #fffbeb;
        --badge-amber-text: #d97706;
        --badge-sky-bg: #eff6ff;
        --badge-sky-text: #2563eb;

        --hub-bg: #f8fafc;
        --hub-card: #ffffff;
        --hub-surface: #ffffff;
        --hub-surface-soft: #f1f5f9;
        --hub-border: #e2e8f0;
        --hub-ink: #0f172a;
        --hub-muted: #64748b;
        --hub-primary: #0d9488;
        --hub-primary-soft: #ccfbf1;
        --hub-accent: #6366f1;
        --hub-danger: #e11d48;
        --hub-success: #059669;
    }

    .dark {
        --color-canvas: #0b141a;
        --color-surface: #111b21;
        --color-surface-hover: #202c33;
        --color-border: #1f2c34;
        --color-text-main: #f1f5f9;
        --color-text-muted: #94a3b8;

        --hub-bg: #0b141a;
        --hub-card: #111b21;
        --hub-surface: #111b21;
        --hub-surface-soft: #202c33;
        --hub-border: #1f2c34;
        --hub-ink: #f1f5f9;
        --hub-muted: #94a3b8;
        --hub-primary-soft: #134e48;
    }

    .fi-layout {
        background-color: var(--color-canvas) !important;
        background-image: none !important;
        min-height: 100dvh !important;
    }

    .fi-main,
    .fi-page,
    .fi-content {
        background-color: transparent !important;
        background-image: none !important;
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .hub-card:not(.hub-card-dark) {
        border-radius: 16px !important;
        border: 1px solid var(--color-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02) !important;
        background: var(--color-surface) !important;
    }

    .dark .fi-section,
    .dark .fi-ta-ctn,
    .dark .fi-wi-stats-overview-stat,
    .dark .hub-card:not(.hub-card-dark) {
        background: #111b21 !important;
        border-color: #1f2c34 !important;
        box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.4) !important;
    }

    .hub-card-dark {
        border-radius: 16px !important;
        background: #0f766e !important;
        border: 1px solid #0f766e !important;
        box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.3) !important;
        color: #ffffff !important;
    }

    .hub-card-dark .hub-title,
    .hub-card-dark h1,
    .hub-card-dark h2,
    .hub-card-dark h3,
    .hub-card-dark h4 {
        color: #ffffff !important;
    }

    .hub-card-dark .hub-copy,
    .hub-card-dark p {
        color: #e2e8f0 !important;
    }

    .hub-card-dark .hub-eyebrow {
        color: #94a3b8 !important;
    }

    /* ==========================================================================
       GLOBAL PREMIUM BUTTON DESIGN SYSTEM (ALL PANELS)
       ========================================================================== */
    [hidden],
    .hidden,
    .fi-hidden,
    [x-cloak],
    [style*="display: none"],
    [style*="display:none"] {
        display: none !important;
    }

    .fi-btn:not([hidden]):not(.hidden),
    button.fi-btn:not([hidden]):not(.hidden),
    a.fi-btn:not([hidden]):not(.hidden),
    .hub-btn,
    .hub-btn-primary,
    .fi-ac-btn-action,
    .fi-page-header-actions .fi-btn,
    .fi-header-actions .fi-btn,
    .fi-form-actions .fi-btn,
    .fi-modal-footer-actions .fi-btn,
    .fi-modal-window footer .fi-btn,
    .fi-ta-actions .fi-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        font-family: inherit !important;
        font-weight: 700 !important;
        font-size: 0.8125rem !important;
        line-height: 1.25rem !important;
        border-radius: 9999px !important;
        padding: 0.5rem 1.2rem !important;
        cursor: pointer !important;
        text-decoration: none !important;
        border: 1px solid transparent;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }

    .fi-btn:disabled,
    button.fi-btn:disabled {
        opacity: 0.55 !important;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
    }

    /* --- PRIMARY BUTTONS (Save changes, Create, Submit, New, etc.) --- */
    .fi-btn-color-primary,
    [data-color="primary"].fi-btn,
    .hub-btn-primary,
    .fi-form-actions button[type="submit"]:not(.fi-btn-color-gray):not(.fi-btn-color-danger),
    .fi-modal-footer-actions button[type="submit"]:not(.fi-btn-color-gray):not(.fi-btn-color-danger) {
        background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%) !important;
        color: #ffffff !important;
        border-color: #6D28D9 !important;
        box-shadow: 0 4px 14px -2px rgba(124, 58, 237, 0.38) !important;
    }

    .fi-btn-color-primary:hover,
    [data-color="primary"].fi-btn:hover,
    .hub-btn-primary:hover,
    .fi-form-actions button[type="submit"]:not(.fi-btn-color-gray):not(.fi-btn-color-danger):hover,
    .fi-modal-footer-actions button[type="submit"]:not(.fi-btn-color-gray):not(.fi-btn-color-danger):hover {
        background: linear-gradient(135deg, #6D28D9 0%, #5B21B6 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 20px -2px rgba(124, 58, 237, 0.48) !important;
    }

    .fi-btn-color-primary:active,
    [data-color="primary"].fi-btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px -2px rgba(124, 58, 237, 0.3) !important;
    }

    .fi-btn-color-primary svg,
    [data-color="primary"].fi-btn svg {
        color: #ffffff !important;
    }

    /* --- GRAY / SECONDARY BUTTONS (Cancel, Close, Dismiss, Filter, Back, etc.) --- */
    .fi-btn-color-gray,
    [data-color="gray"].fi-btn,
    .fi-btn-color-secondary,
    .hub-btn-secondary,
    .fi-modal-close-btn,
    .fi-form-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info),
    .fi-modal-footer-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info) {
        background: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .fi-btn-color-gray:hover,
    [data-color="gray"].fi-btn:hover,
    .fi-btn-color-secondary:hover,
    .hub-btn-secondary:hover,
    .fi-modal-close-btn:hover,
    .fi-form-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info):hover,
    .fi-modal-footer-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info):hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 8px -2px rgba(15, 23, 42, 0.08) !important;
    }

    .dark .fi-btn-color-gray,
    .dark [data-color="gray"].fi-btn,
    .dark .fi-btn-color-secondary,
    .dark .hub-btn-secondary,
    .dark .fi-modal-close-btn,
    .dark .fi-form-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info),
    .dark .fi-modal-footer-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info) {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .dark .fi-btn-color-gray:hover,
    .dark [data-color="gray"].fi-btn:hover,
    .dark .fi-btn-color-secondary:hover,
    .dark .hub-btn-secondary:hover,
    .dark .fi-modal-close-btn:hover,
    .dark .fi-form-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info):hover,
    .dark .fi-modal-footer-actions button[type="button"]:not(.fi-btn-color-primary):not(.fi-btn-color-warning):not(.fi-btn-color-danger):not(.fi-btn-color-success):not(.fi-btn-color-info):hover {
        background: #334155 !important;
        color: #f8fafc !important;
        border-color: #475569 !important;
    }

    /* --- WARNING BUTTONS (Import Sessions, Export, Pending, etc.) --- */
    .fi-btn-color-warning,
    [data-color="warning"].fi-btn,
    .hub-btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: #ffffff !important;
        border-color: #d97706 !important;
        box-shadow: 0 4px 14px -2px rgba(217, 119, 6, 0.35) !important;
    }

    .fi-btn-color-warning:hover,
    [data-color="warning"].fi-btn:hover,
    .hub-btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px -2px rgba(217, 119, 6, 0.45) !important;
    }

    .fi-btn-color-warning svg,
    [data-color="warning"].fi-btn svg {
        color: #ffffff !important;
    }

    /* --- DANGER BUTTONS (Delete, Revoke, Remove, Reject, etc.) --- */
    .fi-btn-color-danger,
    [data-color="danger"].fi-btn,
    .hub-btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
        box-shadow: 0 4px 14px -2px rgba(220, 38, 38, 0.35) !important;
    }

    .fi-btn-color-danger:hover,
    [data-color="danger"].fi-btn:hover,
    .hub-btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px -2px rgba(220, 38, 38, 0.45) !important;
    }

    .fi-btn-color-danger svg,
    [data-color="danger"].fi-btn svg {
        color: #ffffff !important;
    }

    /* --- SUCCESS BUTTONS (Approve, Complete, Graded, Accept, etc.) --- */
    .fi-btn-color-success,
    [data-color="success"].fi-btn,
    .hub-btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 4px 14px -2px rgba(5, 150, 105, 0.35) !important;
    }

    .fi-btn-color-success:hover,
    [data-color="success"].fi-btn:hover,
    .hub-btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px -2px rgba(5, 150, 105, 0.45) !important;
    }

    .fi-btn-color-success svg,
    [data-color="success"].fi-btn svg {
        color: #ffffff !important;
    }

    /* --- INFO / TEAL BUTTONS (Download, Explore, Details, etc.) --- */
    .fi-btn-color-info,
    [data-color="info"].fi-btn,
    .hub-btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
        color: #ffffff !important;
        border-color: #0891b2 !important;
        box-shadow: 0 4px 14px -2px rgba(8, 145, 178, 0.35) !important;
    }

    .fi-btn-color-info:hover,
    [data-color="info"].fi-btn:hover,
    .hub-btn-info:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px -2px rgba(8, 145, 178, 0.45) !important;
    }

    .fi-btn-color-info svg,
    [data-color="info"].fi-btn svg {
        color: #ffffff !important;
    }

    /* --- BUTTON SIZES --- */
    .fi-btn-size-xs {
        padding: 0.25rem 0.7rem !important;
        font-size: 0.72rem !important;
    }

    .fi-btn-size-sm,
    .fi-ta-actions .fi-btn {
        padding: 0.35rem 0.9rem !important;
        font-size: 0.75rem !important;
    }

    .fi-btn-size-md,
    .fi-page-header-actions .fi-btn,
    .fi-header-actions .fi-btn,
    .fi-form-actions .fi-btn,
    .fi-modal-footer-actions .fi-btn {
        padding: 0.52rem 1.25rem !important;
        font-size: 0.8125rem !important;
    }

    .fi-btn-size-lg {
        padding: 0.65rem 1.5rem !important;
        font-size: 0.9rem !important;
    }

    /* --- ICON BUTTONS (Circle Actions) --- */
    .fi-icon-btn,
    button.fi-icon-btn,
    a.fi-icon-btn {
        border-radius: 9999px !important;
        padding: 0.45rem;
        transition: all 0.18s ease;
    }

    .fi-icon-btn:hover,
    button.fi-icon-btn:hover,
    a.fi-icon-btn:hover {
        background: rgba(124, 58, 237, 0.12) !important;
        color: #7C3AED !important;
        transform: scale(1.06) !important;
    }

    .dark .fi-icon-btn:hover,
    .dark button.fi-icon-btn:hover,
    .dark a.fi-icon-btn:hover {
        background: rgba(168, 85, 247, 0.2) !important;
        color: #c084fc !important;
    }

    /* --- ACTION & MODAL FOOTER BAR LAYOUT --- */
    .fi-form-actions,
    .fi-modal-footer-actions,
    .fi-modal-window footer,
    .fi-page-header-actions,
    .fi-header-actions {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        flex-wrap: wrap !important;
    }

    .fi-input,
    .fi-select-input,
    .fi-textarea {
        border-radius: 12px !important;
        border-color: color-mix(in oklab, var(--hub-border) 84%, #ffffff 16%) !important;
    }

    .fi-ta-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .fi-ta-header-cell {
        background: color-mix(in oklab, var(--hub-primary-soft) 34%, #ffffff 66%);
        color: color-mix(in oklab, var(--hub-muted) 62%, #173a3a 38%);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .fi-ta-cell,
    .fi-ta-header-cell {
        padding-top: 0.48rem !important;
        padding-bottom: 0.48rem !important;
    }

    .fi-ta-row:hover {
        background: color-mix(in oklab, var(--hub-primary-soft) 44%, #ffffff 56%);
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

    /* WhatsApp Full-Screen UI Theme */
    .whatsapp-container {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        display: flex;
        position: relative;
    }
    .dark .whatsapp-container {
        border-color: #1f2c34;
        background-color: #111b21;
    }
    .whatsapp-chat-pane {
        background-color: #efeae2 !important;
        background-image: radial-gradient(#d1d7db 0.8px, transparent 0.8px) !important;
        background-size: 16px 16px !important;
    }
    .dark .whatsapp-chat-pane {
        background-color: #0b141a !important;
        background-image: radial-gradient(#1f2c34 0.8px, transparent 0.8px) !important;
        background-size: 16px 16px !important;
    }
    .whatsapp-bubble-mine {
        background-color: #d9fdd3 !important;
        color: #111b21 !important;
        border-radius: 0.75rem 0.75rem 0.2rem 0.75rem !important;
        box-shadow: 0 1px 1.5px rgba(11,20,26,.13) !important;
    }
    .dark .whatsapp-bubble-mine {
        background-color: #005c4b !important;
        color: #e9edef !important;
        box-shadow: 0 1px 1.5px rgba(0,0,0,.3) !important;
    }
    .whatsapp-bubble-other {
        background-color: #ffffff !important;
        color: #111b21 !important;
        border-radius: 0.75rem 0.75rem 0.75rem 0.2rem !important;
        box-shadow: 0 1px 1.5px rgba(11,20,26,.13) !important;
    }
    .dark .whatsapp-bubble-other {
        background-color: #202c33 !important;
        color: #e9edef !important;
        box-shadow: 0 1px 1.5px rgba(0,0,0,.3) !important;
    }
    .whatsapp-header-bar {
        background-color: #f0f2f5 !important;
        border-color: #e9edef !important;
    }
    .dark .whatsapp-header-bar {
        background-color: #202c33 !important;
        border-color: #2a3942 !important;
    }
    .whatsapp-composer-bar {
        background-color: #f0f2f5 !important;
        border-color: #e9edef !important;
    }
    .dark .whatsapp-composer-bar {
        background-color: #202c33 !important;
        border-color: #2a3942 !important;
    }
    .whatsapp-send-btn {
        background-color: #00a884 !important;
        color: #ffffff !important;
    }
    .whatsapp-send-btn:hover {
        background-color: #008069 !important;
    }
    .hub-tab-chats-active {
        background-color: #008069 !important;
        color: #ffffff !important;
    }
    .dark .hub-tab-chats-active {
        background-color: #00a884 !important;
        color: #ffffff !important;
    }

    .hub-card-dark {
        border: 1px solid #7C3AED;
        background: linear-gradient(135deg, #7C3AED, #4F46E5);
        color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 14px -2px rgba(124, 58, 237, 0.35);
    }

    .hub-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.01em;
    }
    .dark .hub-title {
        color: #f8fafc;
    }

    .hub-eyebrow {
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7C3AED;
        font-weight: 800;
    }
    .dark .hub-eyebrow {
        color: #c084fc;
    }

    .hub-copy {
        margin-top: 0.25rem;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.45;
    }
    .dark .hub-copy {
        color: #94a3b8;
    }

    .hub-metric {
        margin-top: 0.35rem;
        font-size: 1.5rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .dark .hub-metric {
        color: #f8fafc;
    }

    /* ============================================================ */
    /* EDTECH SAAS DASHBOARD DESIGN SYSTEM                         */
    /* ============================================================ */
    .edtech-header-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        background: #ffffff;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    @media (min-width: 768px) {
        .edtech-header-card {
            flex-direction: row;
            align-items: center;
        }
    }
    .dark .edtech-header-card {
        background: #102028;
        border-color: #233842;
    }

    .edtech-stat-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .edtech-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .edtech-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .edtech-stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark .edtech-stat-card {
        background: #102028;
        border-color: #233842;
    }
    .edtech-stat-card:hover {
        box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04);
        transform: translateY(-2px);
    }

    .edtech-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        overflow: hidden;
    }
    .dark .edtech-card,
    .fi-theme-dark .edtech-card,
    html.dark .edtech-card,
    html.fi-theme-dark .edtech-card {
        background: #102028 !important;
        border-color: #233842 !important;
        color: #f1f5f9 !important;
    }
    .edtech-card:hover {
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06), 0 2px 4px -2px rgba(15, 23, 42, 0.03);
    }
    
    .edtech-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        width: 100%;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .edtech-dashboard-grid {
            grid-template-columns: 1fr 340px;
        }
    }
    @media (min-width: 1280px) {
        .edtech-dashboard-grid {
            grid-template-columns: 1fr 380px;
        }
    }

    .edtech-hero-banner {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 60%, #14b8a6 100%);
        color: #ffffff;
        border-radius: 20px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.35);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.25rem;
    }
    @media (min-width: 768px) {
        .edtech-hero-banner {
            flex-direction: row;
            align-items: center;
        }
    }

    .edtech-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }
    .edtech-table {
        width: 100%;
        text-align: left;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .edtech-table th {
        padding: 0.75rem 1rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .edtech-table th {
        background: #09181f;
        color: #94a3b8;
        border-color: #233842;
    }
    .edtech-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .dark .edtech-table td {
        border-color: #1a2e38;
        color: #cbd5e1;
    }
    .edtech-table tr:hover td {
        background: #f8fafc;
    }
    .dark .edtech-table tr:hover td {
        background: #112831;
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

    .hub-chip-primary { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .hub-chip-blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .hub-chip-amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .hub-chip-green { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .hub-chip-red { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
    .hub-chip-gray { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    .hub-btn {
        border: 1px solid transparent;
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        font-size: 0.76rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
    }

    .hub-btn-primary { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; box-shadow: 0 4px 12px -2px rgba(13, 148, 136, 0.35); }
    .hub-btn-primary:hover { background: #0f766e; transform: translateY(-1px); }
    .hub-btn-muted { background: #ffffff; color: #0f172a; border-color: #e2e8f0; }
    .dark .hub-btn-muted { background: #102028; color: #f1f5f9; border-color: #233842; }
    .hub-btn-muted:hover { background: #f8fafc; border-color: #cbd5e1; }
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

    /* Mobile: hamburger left, search bar full width, profile/notifications right (logo hidden) */
    @media (max-width: 1023px) {
        .fi-topbar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.45rem !important;
            padding: 0.35rem 0.6rem !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 40 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn {
            order: 1 !important;
            align-items: center;
            justify-content: center;
            width: 2.2rem !important;
            height: 2.2rem !important;
            border-radius: 999px !important;
            background: var(--hub-surface-soft, #f1f5f9) !important;
            color: var(--hub-ink, #0f172a) !important;
            border: 1px solid var(--hub-border, #e2e8f0) !important;
            z-index: 50 !important;
            flex-shrink: 0 !important;
        }

        .fi-topbar-open-sidebar-btn:not([style*="display: none"]),
        .fi-topbar-close-sidebar-btn:not([style*="display: none"]) {
            display: inline-flex !important;
        }

        .fi-topbar-open-sidebar-btn[style*="display: none"],
        .fi-topbar-close-sidebar-btn[style*="display: none"] {
            display: none !important;
        }

        .dark .fi-topbar-open-sidebar-btn,
        .dark .fi-topbar-close-sidebar-btn {
            background: var(--hub-surface-soft, #162c36) !important;
            color: var(--hub-ink, #f1f5f9) !important;
            border-color: var(--hub-border, #233842) !important;
        }

        /* Hide brand logo completely on mobile topbar for full-width search experience */
        .fi-topbar-start,
        .fi-topbar .fi-logo,
        .fi-topbar-brand,
        .fi-topbar-logo {
            display: none !important;
        }

        .hub-topbar-badge {
            display: none !important;
        }

        .hub-top-bar-group {
            order: 2 !important;
            flex: 1 1 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            gap: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .hub-top-search-form {
            flex: 1 1 auto !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
        }

        .hub-top-search {
            width: 100% !important;
            font-size: 0.78rem !important;
            padding: 0.4rem 0.8rem !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }

        /* Reorder: push profile & notifications (fi-topbar-end) to the far right */
        .fi-topbar > .fi-topbar-end {
            order: 3 !important;
            margin-inline-start: 0 !important;
            flex-shrink: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
        }

        /* Sidebar off-canvas overlay & z-index */
        .fi-sidebar-close-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9990 !important;
            cursor: pointer !important;
            background-color: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            transition: opacity 0.25s ease !important;
        }

        .fi-sidebar,
        .fi-main-sidebar {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            width: min(290px, 85vw) !important;
            max-width: min(290px, 85vw) !important;
            z-index: 9995 !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2) !important;
            transform: translateX(-100%) !important;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow: hidden !important;
        }

        .fi-sidebar.fi-sidebar-open,
        .fi-main-sidebar.fi-sidebar-open {
            transform: translateX(0) !important;
        }

        .fi-sidebar-nav {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
        }
    }

    /* Desktop: centre the group in the topbar, show logo in top navigation, remove collapse toggle */
    @media (min-width: 1024px) {
        .fi-topbar {
            position: relative;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .fi-topbar-start {
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            min-width: fit-content !important;
        }

        .fi-topbar-start > a,
        .fi-topbar-start a.fi-topbar-brand,
        .fi-topbar-start .fi-topbar-brand,
        .fi-topbar-start .fi-topbar-logo {
            display: inline-flex !important;
            align-items: center !important;
        }

        /* Hide the sidebar header & logo on desktop since the logo is placed in the top navigation bar */
        .fi-sidebar-header,
        .fi-sidebar header,
        .fi-sidebar .fi-logo,
        .fi-sidebar a.fi-logo {
            display: none !important;
            height: 0 !important;
            min-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            visibility: hidden !important;
        }

        /* Remove hide/unhide sidebar collapse toggle on desktop view */
        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn,
        .fi-sidebar-close-btn,
        .fi-sidebar-trigger,
        .fi-topbar-start button,
        button[title*="sidebar" i],
        button[aria-label*="sidebar" i],
        button[title*="collapse" i],
        button[title*="expand" i] {
            display: none !important;
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

    /* Calendar Hover Popover */
    .hub-cal-popover {
        background: #0f172a !important;
        color: #f8fafc !important;
        border: 1px solid #334155 !important;
        border-radius: 8px !important;
        padding: 0.55rem 0.7rem !important;
        font-size: 0.75rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        pointer-events: none !important;
        width: max-content !important;
        max-width: 250px !important;
        backdrop-filter: blur(8px) !important;
        box-sizing: border-box !important;
    }

    .hub-cal-popover-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
        padding-bottom: 0.35rem !important;
        margin-bottom: 0.35rem !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
    }

    .hub-cal-popover-dot {
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        background: #10b981 !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
    }

    .hub-cal-popover-title {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #38bdf8 !important;
    }

    .hub-cal-popover-badge {
        font-size: 0.62rem !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        background: rgba(255, 255, 255, 0.08) !important;
        padding: 0.1rem 0.35rem !important;
        border-radius: 4px !important;
    }

    .hub-cal-popover-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.3rem !important;
    }

    .hub-cal-popover-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 0.35rem !important;
        font-size: 0.73rem !important;
        color: #e2e8f0 !important;
        line-height: 1.35 !important;
    }

    .hub-cal-popover-hint {
        margin-top: 0.4rem !important;
        padding-top: 0.3rem !important;
        border-top: 1px dashed rgba(255, 255, 255, 0.1) !important;
        font-size: 0.62rem !important;
        color: #64748b !important;
        text-align: center !important;
    }

    @media (hover: none) or (max-width: 640px) {
        .hub-cal-popover {
            display: none !important;
        }
    }

    /* ============================================================ */
    /* SCHEDULE & INTERACTIVE CALENDAR SYSTEM                       */
    /* ============================================================ */
    /* ============================================================ */
    /* MODERN HIGH-DENSITY SCHEDULE & TIMETABLE SYSTEM (2-COLUMN)   */
    /* ============================================================ */
    .hub-schedule-workspace {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
        max-width: 100%;
    }

    /* 1. Top Control Bar */
    .hub-schedule-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.55rem 0.85rem;
        background: var(--hub-card);
        border: 1px solid var(--hub-border);
        border-radius: 12px;
    }

    .hub-topbar-left {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .hub-topbar-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--hub-ink);
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .hub-topbar-title-icon {
        width: 1.05rem;
        height: 1.05rem;
        color: var(--hub-primary);
    }

    .hub-topbar-right {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    /* Segmented view switcher pills */
    .hub-segmented-tabs {
        display: inline-flex;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 2px;
        gap: 2px;
    }

    .hub-tab {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--hub-muted);
        background: transparent;
        border: none;
        border-radius: 6px;
        padding: 0.22rem 0.65rem;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.2;
    }

    .hub-tab:hover {
        color: var(--hub-ink);
    }

    .hub-tab.is-active {
        background: var(--hub-primary);
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        font-weight: 700;
    }

    /* Period Navigator */
    .hub-nav-cluster {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .hub-nav-arrows {
        display: inline-flex;
        align-items: center;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 2px 4px;
        gap: 2px;
    }

    .hub-nav-arrow-btn {
        background: transparent;
        border: none;
        color: var(--hub-muted);
        cursor: pointer;
        padding: 2px;
        display: flex;
        align-items: center;
        border-radius: 4px;
        transition: color 0.12s;
    }

    .hub-nav-arrow-btn:hover {
        color: var(--hub-ink);
    }

    .hub-today-btn {
        background: transparent;
        border: none;
        color: var(--hub-primary);
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
        transition: opacity 0.12s;
    }

    .hub-today-btn:hover {
        opacity: 0.85;
    }

    .hub-current-period-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
        white-space: nowrap;
        letter-spacing: -0.01em;
    }

    /* 2. Main 2-Column Schedule Grid */
    .hub-schedule-main-grid {
        display: grid;
        grid-template-columns: 1.42fr 1fr;
        gap: 0.75rem;
        align-items: start;
        width: 100%;
    }

    .hub-schedule-card {
        background: var(--hub-card);
        border: 1px solid var(--hub-border);
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .hub-pane-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid var(--hub-border);
    }

    .hub-pane-title-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .hub-pane-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
    }

    .hub-pane-icon {
        width: 0.95rem;
        height: 0.95rem;
        color: var(--hub-primary);
    }

    .hub-pane-hint {
        font-size: 0.62rem;
        color: var(--hub-muted);
        background: var(--hub-surface-soft);
        padding: 0.12rem 0.4rem;
        border-radius: 4px;
        border: 1px solid var(--hub-border);
    }

    .hub-pane-count-badge {
        font-size: 0.65rem;
        color: var(--hub-muted);
        font-weight: 600;
    }

    /* Status Filters (Side Panel) */
    .hub-side-status-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .hub-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.18rem 0.45rem;
        border-radius: 6px;
        border: 1px solid var(--hub-border);
        background: var(--hub-surface-soft);
        color: var(--hub-muted);
        cursor: pointer;
        transition: all 0.12s ease;
        line-height: 1.2;
    }

    .hub-filter-pill:hover {
        border-color: var(--hub-primary);
        color: var(--hub-ink);
    }

    .hub-filter-pill.is-active {
        background: color-mix(in oklab, var(--hub-primary-soft) 28%, var(--hub-surface));
        border-color: var(--hub-primary);
        color: var(--hub-ink);
        font-weight: 700;
    }

    .hub-filter-count {
        font-size: 0.62rem;
        opacity: 0.85;
        font-weight: 700;
    }

    /* Status Dot Indicators */
    .hub-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .hub-status-dot.is-scheduled {
        background: #14b8a6;
        box-shadow: 0 0 5px rgba(20, 184, 166, 0.45);
    }

    .hub-status-dot.is-completed {
        background: #10b981;
        box-shadow: 0 0 5px rgba(16, 185, 129, 0.45);
    }

    .hub-status-dot.is-rescheduled {
        background: #38bdf8;
        box-shadow: 0 0 5px rgba(56, 189, 248, 0.45);
    }

    .hub-status-dot.is-cancelled {
        background: #f43f5e;
        box-shadow: 0 0 5px rgba(244, 63, 94, 0.45);
    }

    /* Search Box */
    .hub-side-search-box {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .hub-search-icon {
        position: absolute;
        left: 0.5rem;
        width: 0.75rem;
        height: 0.75rem;
        color: var(--hub-muted);
        pointer-events: none;
    }

    .hub-search-input {
        width: 100%;
        padding: 0.25rem 0.5rem 0.25rem 1.55rem;
        font-size: 0.7rem;
        border-radius: 6px;
        border: 1px solid var(--hub-border);
        background: var(--hub-surface-soft);
        color: var(--hub-ink);
        outline: none;
        transition: border-color 0.15s ease;
    }

    .hub-search-input:focus {
        border-color: var(--hub-primary);
    }

    /* Side Session List */
    .hub-side-session-list {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        max-height: 420px;
        overflow-y: auto;
        padding-right: 2px;
    }

    .hub-side-session-card {
        text-align: left;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-left: 3px solid var(--hub-primary);
        border-radius: 8px;
        padding: 0.55rem 0.7rem;
        cursor: pointer;
        transition: all 0.12s ease;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        width: 100%;
        min-width: 0;
    }

    .hub-side-session-card.is-scheduled { border-left-color: #14b8a6; }
    .hub-side-session-card.is-completed { border-left-color: #10b981; }
    .hub-side-session-card.is-rescheduled { border-left-color: #38bdf8; }
    .hub-side-session-card.is-cancelled { border-left-color: #f43f5e; }

    .hub-side-session-card:hover {
        transform: translateY(-1px);
        border-color: var(--hub-primary);
        border-left-color: var(--hub-primary);
        background: var(--hub-surface);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .hub-side-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.65rem;
        gap: 0.4rem;
    }

    .hub-side-card-code {
        color: var(--hub-primary);
        font-weight: 800;
    }

    .hub-side-card-type {
        color: var(--hub-muted);
        font-size: 0.6rem;
    }

    .hub-side-card-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
        line-height: 1.3;
    }

    .hub-side-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.35rem;
        font-size: 0.62rem;
        color: var(--hub-muted);
        margin-top: 0.1rem;
    }

    .hub-side-card-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Custom Date Range Toolbar */
    .hub-custom-date-bar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.45rem 0.75rem;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        flex-wrap: wrap;
        margin-bottom: 0.4rem;
    }

    .hub-custom-date-bar label {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--hub-muted);
    }

    .hub-custom-date-bar input[type="date"] {
        padding: 0.2rem 0.45rem;
        font-size: 0.7rem;
        border-radius: 6px;
        border: 1px solid var(--hub-border);
        background: var(--hub-surface);
        color: var(--hub-ink);
    }

    .hub-custom-date-hint {
        font-size: 0.65rem;
        color: var(--hub-muted);
    }

    /* ============================================================ */
    /* MONTH VIEW                                                   */
    /* ============================================================ */
    .hub-month-grid {
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 0.25rem;
    }

    .hub-month-dow-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.25rem;
        text-align: center;
        font-size: 0.62rem;
        font-weight: 700;
        color: var(--hub-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding-bottom: 0.3rem;
        border-bottom: 1px solid var(--hub-border);
    }

    .hub-month-body {
        display: grid;
        gap: 0.25rem;
        width: 100%;
    }

    .hub-month-row {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.25rem;
        min-width: 0;
    }

    .hub-month-cell {
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 0.25rem 0.3rem;
        min-height: 68px;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        transition: border-color 0.12s, background 0.12s;
        cursor: pointer;
    }

    .hub-month-cell:hover {
        border-color: var(--hub-primary);
    }

    .hub-month-cell.is-dimmed {
        opacity: 0.28;
        background: transparent;
        border-color: transparent;
        cursor: default;
    }

    .hub-month-cell.is-today {
        background: color-mix(in oklab, var(--hub-primary-soft) 20%, var(--hub-surface)) !important;
        border-color: var(--hub-primary) !important;
    }

    .hub-month-cell-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.15rem;
        line-height: 1;
    }

    .hub-month-day-num {
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--hub-ink);
    }

    .hub-month-day-num.is-today-badge {
        background: var(--hub-primary);
        color: #ffffff;
        border-radius: 50%;
        width: 1.1rem;
        height: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 800;
    }

    .hub-month-more-badge {
        font-size: 0.55rem;
        font-weight: 700;
        color: var(--hub-primary);
        background: color-mix(in oklab, var(--hub-primary-soft) 30%, var(--hub-surface));
        padding: 0.05rem 0.22rem;
        border-radius: 4px;
    }

    .hub-month-cell-events {
        display: flex;
        flex-direction: column;
        gap: 0.12rem;
        overflow-y: auto;
        max-height: 52px;
        min-width: 0;
    }

    .hub-month-event-item {
        text-align: left;
        background: var(--hub-surface);
        border: 1px solid var(--hub-border);
        border-radius: 4px;
        padding: 0.12rem 0.25rem;
        font-size: 0.55rem;
        color: var(--hub-ink);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.2rem;
        width: 100%;
        min-width: 0;
        line-height: 1.2;
        transition: transform 0.1s ease, border-color 0.1s ease;
    }

    .hub-month-event-item:hover {
        transform: translateY(-1px);
        border-color: var(--hub-primary);
    }

    .hub-event-indicator {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .hub-event-indicator.is-scheduled { background: #14b8a6; }
    .hub-event-indicator.is-completed { background: #10b981; }
    .hub-event-indicator.is-rescheduled { background: #38bdf8; }
    .hub-event-indicator.is-cancelled { background: #f43f5e; }

    .hub-event-time {
        font-size: 0.52rem;
        color: var(--hub-muted);
        font-weight: 600;
        white-space: nowrap;
    }

    .hub-event-title {
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
    }

    /* ============================================================ */
    /* WEEK VIEW                                                    */
    /* ============================================================ */
    .hub-week-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.35rem;
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0.25rem;
    }

    .hub-week-column {
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 0.35rem 0.25rem;
        display: flex;
        flex-direction: column;
        min-height: 340px;
        min-width: 0;
    }

    .hub-week-column.is-today {
        background: color-mix(in oklab, var(--hub-primary-soft) 16%, var(--hub-surface));
        border-color: color-mix(in oklab, var(--hub-primary) 70%, var(--hub-border));
    }

    .hub-week-column-header {
        text-align: center;
        padding-bottom: 0.3rem;
        border-bottom: 1px solid var(--hub-border);
        margin-bottom: 0.3rem;
    }

    .hub-week-day-name {
        display: block;
        font-size: 0.58rem;
        font-weight: 700;
        color: var(--hub-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .hub-week-day-num {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--hub-ink);
        margin-top: 0.02rem;
        display: inline-block;
    }

    .hub-week-day-num.is-today-pill {
        color: var(--hub-primary);
    }

    .hub-week-today-tag {
        display: block;
        font-size: 0.48rem;
        font-weight: 800;
        background: var(--hub-primary);
        color: #ffffff;
        padding: 0.04rem 0.25rem;
        border-radius: 999px;
        width: fit-content;
        margin: 0.1rem auto 0;
        letter-spacing: 0.03em;
    }

    .hub-week-column-sessions {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        flex: 1;
        overflow-y: auto;
        min-width: 0;
    }

    .hub-week-session-card {
        text-align: left;
        background: var(--hub-surface);
        border: 1px solid var(--hub-border);
        border-radius: 6px;
        padding: 0.3rem 0.35rem;
        cursor: pointer;
        transition: all 0.12s ease;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        width: 100%;
        min-width: 0;
        border-left: 2.5px solid var(--hub-primary);
    }

    .hub-week-session-card.is-scheduled { border-left-color: #14b8a6; }
    .hub-week-session-card.is-completed { border-left-color: #10b981; }
    .hub-week-session-card.is-rescheduled { border-left-color: #38bdf8; }
    .hub-week-session-card.is-cancelled { border-left-color: #f43f5e; }

    .hub-week-session-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        border-color: var(--hub-primary);
        border-left-color: var(--hub-primary);
    }

    .hub-week-session-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.15rem;
    }

    .hub-week-session-time {
        font-size: 0.55rem;
        font-weight: 700;
        color: var(--hub-muted);
    }

    .hub-status-micro-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.55rem;
        font-weight: 700;
        padding: 0.06rem 0.28rem;
        border-radius: 4px;
        background: var(--hub-surface-soft);
        color: var(--hub-muted);
    }

    .hub-status-micro-pill.is-scheduled { color: #14b8a6; background: rgba(20, 184, 166, 0.12); }
    .hub-status-micro-pill.is-completed { color: #10b981; background: rgba(16, 185, 129, 0.12); }
    .hub-status-micro-pill.is-rescheduled { color: #38bdf8; background: rgba(56, 189, 248, 0.12); }
    .hub-status-micro-pill.is-cancelled { color: #f43f5e; background: rgba(244, 63, 94, 0.12); }
    .hub-status-micro-pill.is-accepted { color: #10b981; background: rgba(16, 185, 129, 0.12); }
    .hub-status-micro-pill.is-declined { color: #94a3b8; background: rgba(148, 163, 184, 0.12); }
    .hub-status-micro-pill.is-pending { color: #f59e0b; background: rgba(245, 158, 11, 0.12); }
    .hub-status-micro-pill.is-present { color: #10b981; background: rgba(16, 185, 129, 0.12); }
    .hub-status-micro-pill.is-late { color: #f59e0b; background: rgba(245, 158, 11, 0.12); }
    .hub-status-micro-pill.is-apology { color: #38bdf8; background: rgba(56, 189, 248, 0.12); }

    .hub-week-session-title {
        font-size: 0.62rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
        line-height: 1.25;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .hub-code-prefix {
        color: var(--hub-primary);
        font-weight: 800;
    }

    .hub-week-session-user {
        display: flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.55rem;
        color: var(--hub-muted);
        margin-top: 0.05rem;
    }

    .hub-user-icon {
        width: 0.6rem;
        height: 0.6rem;
    }

    .hub-week-empty-day {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--hub-muted);
        opacity: 0.35;
        font-size: 0.75rem;
    }

    /* ============================================================ */
    /* DAY VIEW (CHRONOLOGICAL AGENDA TIMELINE)                     */
    /* ============================================================ */
    .hub-day-agenda {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        width: 100%;
    }

    .hub-day-agenda-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid var(--hub-border);
    }

    .hub-day-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
    }

    .hub-day-subtitle {
        font-size: 0.68rem;
        color: var(--hub-muted);
        margin: 0.08rem 0 0;
    }

    .hub-today-badge-subtle {
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--hub-primary);
        background: color-mix(in oklab, var(--hub-primary-soft) 25%, var(--hub-surface));
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        border: 1px solid var(--hub-primary);
    }

    .hub-day-agenda-list {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        max-height: 420px;
        overflow-y: auto;
    }

    .hub-day-session-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.55rem 0.75rem;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-left: 3.5px solid var(--hub-primary);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.12s ease;
        width: 100%;
        text-align: left;
    }

    .hub-day-session-card.is-scheduled { border-left-color: #14b8a6; }
    .hub-day-session-card.is-completed { border-left-color: #10b981; }
    .hub-day-session-card.is-rescheduled { border-left-color: #38bdf8; }
    .hub-day-session-card.is-cancelled { border-left-color: #f43f5e; }

    .hub-day-session-card:hover {
        border-color: var(--hub-primary);
        border-left-color: var(--hub-primary);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .hub-day-time-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        text-align: center;
    }

    .hub-day-time-start {
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--hub-ink);
    }

    .hub-day-time-end {
        font-size: 0.6rem;
        color: var(--hub-muted);
    }

    .hub-day-card-body {
        flex: 1;
        min-width: 0;
    }

    .hub-day-card-meta {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        flex-wrap: wrap;
        margin-bottom: 0.15rem;
    }

    .hub-code-badge {
        font-size: 0.62rem;
        font-weight: 800;
        color: var(--hub-primary);
    }

    .hub-type-badge {
        font-size: 0.58rem;
        color: var(--hub-muted);
    }

    .hub-day-session-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--hub-ink);
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hub-day-course-name {
        font-size: 0.68rem;
        color: var(--hub-muted);
        margin: 0.1rem 0 0;
    }

    .hub-day-card-action {
        color: var(--hub-muted);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hub-chevron-icon {
        width: 1rem;
        height: 1rem;
    }

    .hub-empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--hub-muted);
        background: var(--hub-surface-soft);
        border-radius: 10px;
        border: 1px dashed var(--hub-border);
    }

    .hub-empty-icon {
        width: 1.5rem;
        height: 1.5rem;
        margin: 0 auto 0.35rem;
        opacity: 0.4;
    }

    /* ============================================================ */
    /* CUSTOM VIEW                                                  */
    /* ============================================================ */
    .hub-custom-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
        gap: 0.35rem;
        width: 100%;
        max-height: 420px;
        overflow-y: auto;
    }

    .hub-custom-day-cell {
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        padding: 0.35rem;
        display: flex;
        flex-direction: column;
        min-height: 85px;
        min-width: 0;
    }

    .hub-custom-day-cell.is-today {
        background: color-mix(in oklab, var(--hub-primary-soft) 20%, var(--hub-surface));
        border-color: var(--hub-primary);
    }

    .hub-custom-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--hub-border);
        padding-bottom: 0.15rem;
        margin-bottom: 0.2rem;
    }

    .hub-custom-day-name {
        font-size: 0.6rem;
        font-weight: 700;
        color: var(--hub-muted);
    }

    .hub-custom-day-num {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--hub-ink);
    }

    .hub-custom-day-num.is-today-badge {
        color: var(--hub-primary);
    }

    .hub-custom-day-sessions {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        flex: 1;
        overflow-y: auto;
        min-width: 0;
    }

    .hub-custom-session-btn {
        text-align: left;
        background: var(--hub-surface);
        border: 1px solid var(--hub-border);
        border-radius: 4px;
        padding: 0.15rem 0.3rem;
        font-size: 0.55rem;
        color: var(--hub-ink);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.2rem;
        width: 100%;
        min-width: 0;
    }

    .hub-custom-session-btn:hover {
        border-color: var(--hub-primary);
    }

    .hub-custom-time {
        font-size: 0.52rem;
        color: var(--hub-muted);
        font-weight: 600;
        white-space: nowrap;
    }

    .hub-custom-title {
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
    }

    .hub-custom-empty {
        font-size: 0.55rem;
        color: var(--hub-muted);
        opacity: 0.35;
        margin: auto;
    }

    /* ============================================================ */
    /* BOTTOM SECTIONS (RESCHEDULE & PROGRESS)                      */
    /* ============================================================ */
    .hub-reschedule-card,
    .hub-progress-card {
        background: var(--hub-card);
        border: 1px solid var(--hub-border);
        border-radius: 12px;
        padding: 0.7rem 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        width: 100%;
    }

    .hub-reschedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .hub-reschedule-title-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .hub-counter-badge {
        font-size: 0.62rem;
        font-weight: 700;
        padding: 0.1rem 0.4rem;
        border-radius: 999px;
        background: color-mix(in oklab, var(--hub-primary-soft) 30%, var(--hub-surface));
        color: var(--hub-primary);
    }

    .hub-toggle-history-label {
        font-size: 0.68rem;
        color: var(--hub-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
    }

    .hub-reschedule-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 0.2rem;
    }

    .hub-reschedule-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.45rem 0.65rem;
        background: var(--hub-surface-soft);
        border: 1px solid var(--hub-border);
        border-radius: 8px;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .hub-reschedule-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.7rem;
        flex-wrap: wrap;
    }

    .hub-reschedule-session {
        font-weight: 700;
        color: var(--hub-ink);
    }

    .hub-reschedule-msg {
        color: var(--hub-muted);
    }

    .hub-reschedule-pref {
        color: var(--hub-primary);
        font-weight: 600;
    }

    .hub-reschedule-empty-text {
        font-size: 0.72rem;
        color: var(--hub-muted);
        text-align: center;
        margin: 0.25rem 0;
        opacity: 0.8;
    }

    /* Course Progress & Attendance Bars */
    .hub-progress-items {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .hub-progress-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .hub-progress-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.72rem;
    }

    .hub-progress-course-name {
        font-weight: 700;
        color: var(--hub-ink);
    }

    .hub-progress-fraction {
        font-weight: 700;
        color: var(--hub-primary);
    }

    .hub-progress-fraction.is-good { color: var(--hub-success); }
    .hub-progress-fraction.is-warning { color: var(--hub-danger); }

    .hub-progress-track {
        height: 4px;
        background: var(--hub-border);
        border-radius: 999px;
        overflow: hidden;
        width: 100%;
    }

    .hub-progress-fill {
        height: 100%;
        background: var(--hub-primary);
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .hub-progress-fill.is-good { background: var(--hub-success); }
    .hub-progress-fill.is-warning { background: var(--hub-danger); }

    .hub-attendance-log {
        margin-top: 0.35rem;
        padding-top: 0.35rem;
        border-top: 1px solid var(--hub-border);
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .hub-attendance-log-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.65rem;
    }

    .hub-log-session-name {
        color: var(--hub-ink);
        font-weight: 500;
    }

    /* Custom Scrollbars */
    .hub-side-session-list::-webkit-scrollbar,
    .hub-week-grid::-webkit-scrollbar,
    .hub-month-cell-events::-webkit-scrollbar,
    .hub-day-agenda-list::-webkit-scrollbar,
    .hub-custom-grid::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .hub-side-session-list::-webkit-scrollbar-track,
    .hub-week-grid::-webkit-scrollbar-track,
    .hub-day-agenda-list::-webkit-scrollbar-track,
    .hub-custom-grid::-webkit-scrollbar-track {
        background: transparent;
    }
    .hub-side-session-list::-webkit-scrollbar-thumb,
    .hub-week-grid::-webkit-scrollbar-thumb,
    .hub-day-agenda-list::-webkit-scrollbar-thumb,
    .hub-custom-grid::-webkit-scrollbar-thumb {
        background: var(--hub-border);
        border-radius: 999px;
    }
    .hub-side-session-list::-webkit-scrollbar-thumb:hover,
    .hub-week-grid::-webkit-scrollbar-thumb:hover,
    .hub-day-agenda-list::-webkit-scrollbar-thumb:hover,
    .hub-custom-grid::-webkit-scrollbar-thumb:hover {
        background: var(--hub-primary);
    }

    .hub-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .hub-modal-card {
        max-width: 540px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.5rem;
        position: relative;
        animation: popIn 0.2s ease-out;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
        border-radius: 16px;
        background: var(--hub-card);
        border: 1px solid var(--hub-border);
    }

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

        /* ── Filament v5 mobile overflow fix ──────────────────────
           .fi-main-ctn uses w-screen (100vw) which includes the
           scrollbar width and overflows. Override to 100%. */
        .fi-main-ctn {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fi-layout {
            overflow-x: hidden !important;
        }

        .fi-main {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Reduce Filament page vertical spacing on mobile */
        .fi-page-header-main-ctn {
            padding-top: 1rem !important;
            padding-bottom: 0 !important;
            gap: 0.75rem !important;
        }

        .fi-page-content {
            gap: 0.5rem !important;
        }

        /* Constrain Filament page header padding */
        .fi-header {
            padding-left: 0 !important;
            padding-right: 0 !important;
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

    /* Responsive visibility utilities */
    .hub-desktop-only {
        display: block !important;
    }
    .hub-mobile-only {
        display: none !important;
    }

    /* Mobile card for replacing tables on small screens */
    .hub-mobile-card {
        border: 1px solid var(--hub-border);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        background: var(--hub-card);
        margin-bottom: 0.65rem;
        box-sizing: border-box;
        max-width: 100%;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
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
        margin-top: 0.5rem;
        font-size: 0.78rem;
        flex-wrap: wrap;
    }

    .hub-mobile-card-actions {
        display: flex;
        gap: 0.35rem;
        margin-top: 0.65rem;
        flex-wrap: wrap;
    }

    .hub-action-btn {
        background: none;
        border: 1px solid var(--hub-border);
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.15s ease;
    }

    .hub-span-2 { grid-column: span 2; }

    @media (max-width: 768px) {
        .hub-desktop-only {
            display: none !important;
        }
        .hub-mobile-only {
            display: block !important;
        }
        .hub-span-2 { grid-column: span 1 !important; }

        /* ---- Quiz Centre listing ---- */
        .hub-quiz-listing {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
            max-width: 100%;
        }

        .hub-quiz-listing .hub-mobile-card {
            padding: 0.75rem 0.85rem;
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .hub-quiz-listing .hub-action-btn {
            width: 100%;
            box-sizing: border-box;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
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

        /* ---- Schedule & Interactive Calendar Responsive Rules ---- */
        .hub-schedule-topbar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem !important;
        }

        .hub-topbar-left,
        .hub-topbar-right {
            justify-content: space-between !important;
            width: 100% !important;
        }

        .hub-schedule-main-grid {
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
        }

        .hub-week-grid {
            grid-template-columns: repeat(7, minmax(95px, 1fr)) !important;
        }

        .hub-modal-card {
            padding: 1.15rem !important;
            max-width: 100% !important;
        }

        .hub-calendar-table {
            min-width: 300px !important;
        }

        .hub-calendar-table td {
            height: 3.2rem !important;
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

    /* Mobile screens (≤ 640px) */
    @media (max-width: 640px) {
        .hub-range-pills {
            width: 100% !important;
            justify-content: space-between !important;
        }

        .hub-range-btn {
            flex: 1 !important;
            text-align: center !important;
            padding: 0.35rem 0.35rem !important;
            font-size: 0.72rem !important;
        }

        .hub-schedule-controls {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.45rem !important;
        }

        .hub-period-nav {
            justify-content: space-between !important;
            width: 100% !important;
        }

        .hub-period-title {
            text-align: center !important;
            font-size: 0.85rem !important;
            margin-top: 0.1rem !important;
            display: block !important;
            width: 100% !important;
        }

        /* Month calendar on mobile */
        .hub-cal-dow-row {
            font-size: 0.65rem !important;
            gap: 2px !important;
            padding-bottom: 0.3rem !important;
            margin-bottom: 0.3rem !important;
        }

        .hub-cal-month-week {
            gap: 3px !important;
        }

        .hub-cal-day-cell {
            min-height: 48px !important;
            padding: 0.25rem 0.15rem !important;
            border-radius: 6px !important;
            align-items: center !important;
            justify-content: flex-start !important;
            cursor: pointer !important;
        }

        .hub-cal-day-header {
            width: 100% !important;
            justify-content: center !important;
            position: relative !important;
        }

        .hub-cal-day-num {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
        }

        .hub-cal-count-badge {
            font-size: 0.52rem !important;
            padding: 0 0.25rem !important;
            position: absolute !important;
            right: 0 !important;
            top: -2px !important;
        }

        /* Hide full text buttons inside cramped month grid on mobile */
        .hub-cal-events-desktop {
            display: none !important;
        }

        /* Show color-coded event dot indicators on mobile */
        .hub-cal-events-mobile {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 3px !important;
            margin-top: 0.25rem !important;
        }

        .hub-cal-dot {
            width: 5px !important;
            height: 5px !important;
        }

        /* Status filter pills on mobile */
        .hub-status-filter-pills button {
            flex: 1 1 auto !important;
            text-align: center !important;
            justify-content: center !important;
            min-height: 32px !important;
            font-size: 0.68rem !important;
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
    /* INSTRUCTOR PANEL MOBILE ENHANCEMENTS                          */
    /* ============================================================ */
    @media (max-width: 768px) {
        /* Instructor overview: course cards stack in single column */
        .fi-panel-instructor .hub-grid-2 {
            grid-template-columns: 1fr !important;
        }

        /* Filament sidebar toggle button — ensure visible and tappable */
        .fi-layout-sidebar-toggle-btn-ctn {
            padding: 0.5rem !important;
        }

        /* Instructor resource tables: enable horizontal scroll */
        .fi-panel-instructor .fi-ta-ctn {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Session list action buttons: stack on small screens */
        .fi-panel-instructor .hub-card > div:last-child > .hub-btn {
            flex: 1;
            min-width: 0;
            text-align: center;
        }

        /* Filament header title sizing on mobile */
        .fi-panel-instructor .fi-header-heading {
            font-size: 1.25rem !important;
        }

        /* Filament account widget: compact on mobile */
        .fi-panel-instructor .fi-account-widget {
            padding: 0.5rem !important;
        }
    }

    @media (max-width: 480px) {
        /* Extra-small: full-width buttons in instructor session cards */
        .fi-panel-instructor .hub-card .hub-btn {
            width: 100%;
            justify-content: center;
        }

        .fi-panel-instructor .hub-card .hub-btn + .hub-btn {
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

    /* ============================================================ */
    /* WORKSPACE BADGES (TOPBAR & SIDEBAR)                          */
    /* ============================================================ */
    .hub-sidebar-badge-container {
        padding: 0.35rem 0.75rem 0.6rem;
        margin-bottom: 0.2rem;
    }

    .hub-sidebar-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.32rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.73rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        width: 100%;
        justify-content: center;
        text-transform: capitalize;
    }

    .hub-topbar-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.26rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .dark .hub-sidebar-badge,
    .dark .hub-topbar-badge {
        background: rgba(15, 23, 42, 0.75) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #f1f5f9 !important;
    }

    @media (max-width: 899px) {
        .hub-topbar-badge {
            display: none !important;
        }
    }

    /* =========================================================================
       SIDEBAR NOTIFICATION BADGES (MINIMAL, MODERN INDICATOR PILLS)
       ========================================================================= */
    .fi-sidebar-item-badge-ctn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-inline-start: auto !important;
    }

    .fi-sidebar-item-badge-ctn .fi-badge {
        font-weight: 600 !important;
        font-size: 0.68rem !important;
        line-height: 1 !important;
        letter-spacing: 0.02em;
        border-radius: 9999px !important;
        padding: 0.2rem 0.45rem !important;
        min-width: 1.25rem !important;
        box-shadow: none !important;
        text-shadow: none !important;
        filter: none !important;
        animation: none !important;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Minimal danger / rose indicator pills (e.g. pending assignments, urgent evaluations) */
    .fi-sidebar-item-badge-ctn .fi-color-danger,
    .fi-sidebar-item-badge-ctn .fi-badge[class*="danger"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="red"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="rose"] {
        background: rgba(244, 63, 94, 0.1) !important;
        color: #e11d48 !important;
        border: 1px solid rgba(244, 63, 94, 0.2) !important;
    }

    .dark .fi-sidebar-item-badge-ctn .fi-color-danger,
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="danger"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="red"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="rose"] {
        background: rgba(244, 63, 94, 0.15) !important;
        color: #fb7185 !important;
        border-color: rgba(244, 63, 94, 0.3) !important;
    }

    /* Minimal warning / amber indicator pills (e.g. available quizzes, pending items) */
    .fi-sidebar-item-badge-ctn .fi-color-warning,
    .fi-sidebar-item-badge-ctn .fi-badge[class*="warning"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="amber"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="orange"] {
        background: rgba(245, 158, 11, 0.1) !important;
        color: #d97706 !important;
        border: 1px solid rgba(245, 158, 11, 0.2) !important;
    }

    .dark .fi-sidebar-item-badge-ctn .fi-color-warning,
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="warning"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="amber"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="orange"] {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }

    /* Minimal success / emerald indicator pills (e.g. live sessions, schedule today) */
    .fi-sidebar-item-badge-ctn .fi-color-success,
    .fi-sidebar-item-badge-ctn .fi-badge[class*="success"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="emerald"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="teal"] {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.2) !important;
    }

    .dark .fi-sidebar-item-badge-ctn .fi-color-success,
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="success"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="emerald"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="teal"] {
        background: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
    }

    /* Minimal info / primary indicator pills (e.g. new resources, materials) */
    .fi-sidebar-item-badge-ctn .fi-color-info,
    .fi-sidebar-item-badge-ctn .fi-badge[class*="info"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="sky"],
    .fi-sidebar-item-badge-ctn .fi-badge[class*="blue"],
    .fi-sidebar-item-badge-ctn .fi-color-primary,
    .fi-sidebar-item-badge-ctn .fi-badge[class*="primary"] {
        background: rgba(14, 165, 233, 0.1) !important;
        color: #0284c7 !important;
        border: 1px solid rgba(14, 165, 233, 0.2) !important;
    }

    .dark .fi-sidebar-item-badge-ctn .fi-color-info,
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="info"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="sky"],
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="blue"],
    .dark .fi-sidebar-item-badge-ctn .fi-color-primary,
    .dark .fi-sidebar-item-badge-ctn .fi-badge[class*="primary"] {
        background: rgba(14, 165, 233, 0.15) !important;
        color: #38bdf8 !important;
        border-color: rgba(14, 165, 233, 0.3) !important;
    }

    /* =========================================================================
       NATIVE MOBILE APP & PWA OPTIMIZATIONS (iOS & Android)
       ========================================================================= */

    /* Global Touch & Feel */
    html, body {
        -webkit-tap-highlight-color: transparent !important;
        -webkit-touch-callout: none;
        overscroll-behavior-y: contain;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Safe Area Insets for Mobile Notches & Gesture Home Indicators */
    body {
        padding-left: env(safe-area-inset-left);
        padding-right: env(safe-area-inset-right);
    }

    .fi-topbar {
        padding-top: max(0.2rem, env(safe-area-inset-top)) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    .fi-sidebar {
        padding-bottom: max(1rem, env(safe-area-inset-bottom)) !important;
    }

    /* =========================================================================
       GLOBAL UNIFIED SIDEBAR ARCHITECTURE (MINIMAL, INTUITIVE & STREAMLINED)
       ========================================================================= */
    
    /* 1. Sidebar Container, Surfaces & Borders */
    .fi-sidebar,
    .fi-main-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid rgba(226, 232, 240, 0.85) !important;
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), width 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: none !important;
    }
    .dark .fi-sidebar,
    .dark .fi-main-sidebar {
        background-color: #0b1120 !important;
        border-right: 1px solid rgba(30, 41, 59, 0.85) !important;
    }

    /* Desktop Sidebar Width Expansion (ensures labels never truncate awkwardly) */
    @media (min-width: 1024px) {
        .fi-sidebar,
        .fi-main-sidebar {
            width: 17.5rem !important;
        }
    }

    /* 2. Sleek Custom Scrollbar on Sidebar Nav (replaces chunky OS scrollbar) */
    .fi-sidebar-nav {
        scrollbar-width: thin !important;
        scrollbar-color: rgba(148, 163, 184, 0.2) transparent !important;
        padding: 0.4rem 0.65rem 1.5rem 0.65rem !important;
    }
    .fi-sidebar-nav::-webkit-scrollbar {
        width: 4px !important;
    }
    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: transparent !important;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.18) !important;
        border-radius: 9999px !important;
    }
    .fi-sidebar-nav:hover::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.38) !important;
    }

    /* 3. Header & Brand Logo Area (Mobile Drawer Only) */
    @media (max-width: 1023px) {
        .fi-sidebar-header {
            height: 4.25rem !important;
            min-height: 4.25rem !important;
            padding: 0 1.25rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-bottom: 1px solid rgba(241, 245, 249, 0.9) !important;
        }
        .dark .fi-sidebar-header {
            border-bottom: 1px solid rgba(30, 41, 59, 0.7) !important;
        }
    }

    /* 4. Navigation Group Headers */
    .fi-sidebar-group {
        margin-bottom: 0.4rem !important;
    }
    .fi-sidebar-group-header,
    .fi-sidebar-group-btn,
    .fi-sidebar-group-button,
    .fi-sidebar-group-label {
        padding: 0.45rem 0.75rem 0.2rem 0.75rem !important;
        margin-top: 0.4rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .fi-sidebar-group-label,
    .fi-sidebar-group-button span,
    .fi-sidebar-group-header span {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        color: #94a3b8 !important;
    }
    .dark .fi-sidebar-group-label,
    .dark .fi-sidebar-group-button span,
    .dark .fi-sidebar-group-header span {
        color: #64748b !important;
    }
    .fi-sidebar-group-collapse-btn svg,
    .fi-sidebar-group-button svg {
        width: 0.85rem !important;
        height: 0.85rem !important;
        color: #94a3b8 !important;
        transition: transform 0.2s ease !important;
    }

    /* 5. Navigation Items & Links */
    .fi-sidebar-item {
        margin: 1.5px 0 !important;
    }
    .fi-sidebar-item-btn,
    .fi-sidebar-item-button,
    a.fi-sidebar-item-btn,
    button.fi-sidebar-item-btn {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.45rem 0.75rem !important;
        min-height: 2.35rem !important;
        border-radius: 0.65rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        line-height: 1.25rem !important;
        color: #475569 !important;
        background-color: transparent !important;
        border: 1px solid transparent !important;
        box-shadow: none !important;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer !important;
    }
    .dark .fi-sidebar-item-btn,
    .dark .fi-sidebar-item-button,
    .dark a.fi-sidebar-item-btn,
    .dark button.fi-sidebar-item-btn {
        color: #cbd5e1 !important;
    }

    /* Item Icons */
    .fi-sidebar-item-btn svg,
    .fi-sidebar-item-button svg,
    .fi-sidebar-item-icon {
        width: 1.2rem !important;
        height: 1.2rem !important;
        min-width: 1.2rem !important;
        min-height: 1.2rem !important;
        color: #64748b !important;
        transition: color 0.15s ease, transform 0.15s ease !important;
        flex-shrink: 0 !important;
    }
    .dark .fi-sidebar-item-btn svg,
    .dark .fi-sidebar-item-button svg,
    .dark .fi-sidebar-item-icon {
        color: #94a3b8 !important;
    }

    /* Item Labels */
    .fi-sidebar-item-label,
    .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item-btn > span,
    .fi-sidebar-item-button > span {
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        flex: 1 !important;
    }

    /* Hover State (All Panels) */
    .fi-sidebar-item-btn:hover,
    .fi-sidebar-item-button:hover,
    a.fi-sidebar-item-btn:hover,
    button.fi-sidebar-item-btn:hover {
        background-color: rgba(241, 245, 249, 0.9) !important;
        color: #0f172a !important;
    }
    .dark .fi-sidebar-item-btn:hover,
    .dark .fi-sidebar-item-button:hover,
    .dark a.fi-sidebar-item-btn:hover,
    .dark button.fi-sidebar-item-btn:hover {
        background-color: rgba(30, 41, 59, 0.7) !important;
        color: #ffffff !important;
    }
    .fi-sidebar-item-btn:hover svg,
    .fi-sidebar-item-button:hover svg,
    .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: #0f172a !important;
    }
    .dark .fi-sidebar-item-btn:hover svg,
    .dark .fi-sidebar-item-button:hover svg,
    .dark .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* 6. Active State Across Panels (Refined Modern Glassmorphic Pill) */

    /* INSTRUCTOR PANEL ACTIVE */
    .fi-panel-instructor .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .fi-panel-instructor .fi-sidebar-item-active .fi-sidebar-item-btn,
    .fi-panel-instructor .fi-sidebar-item-button.fi-active,
    .fi-panel-instructor .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(13, 148, 136, 0.09) !important;
        color: #0d9488 !important;
        font-weight: 600 !important;
        border: 1px solid rgba(13, 148, 136, 0.22) !important;
        box-shadow: 0 1px 3px 0 rgba(13, 148, 136, 0.08) !important;
    }
    .dark .fi-panel-instructor .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .dark .fi-panel-instructor .fi-sidebar-item-active .fi-sidebar-item-btn,
    .dark .fi-panel-instructor .fi-sidebar-item-button.fi-active,
    .dark .fi-panel-instructor .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(20, 184, 166, 0.16) !important;
        color: #2dd4bf !important;
        border: 1px solid rgba(45, 212, 191, 0.25) !important;
    }
    .fi-panel-instructor .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .fi-panel-instructor .fi-sidebar-item-button.fi-active svg,
    .fi-panel-instructor .fi-sidebar-item-button[aria-current="page"] svg {
        color: #0d9488 !important;
    }
    .dark .fi-panel-instructor .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .dark .fi-panel-instructor .fi-sidebar-item-button.fi-active svg,
    .dark .fi-panel-instructor .fi-sidebar-item-button[aria-current="page"] svg {
        color: #2dd4bf !important;
    }

    /* STUDENT PANEL ACTIVE */
    .fi-panel-student .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .fi-panel-student .fi-sidebar-item-active .fi-sidebar-item-btn,
    .fi-panel-student .fi-sidebar-item-button.fi-active,
    .fi-panel-student .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(2, 132, 199, 0.09) !important;
        color: #0284c7 !important;
        font-weight: 600 !important;
        border: 1px solid rgba(2, 132, 199, 0.22) !important;
        box-shadow: 0 1px 3px 0 rgba(2, 132, 199, 0.08) !important;
    }
    .dark .fi-panel-student .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .dark .fi-panel-student .fi-sidebar-item-active .fi-sidebar-item-btn,
    .dark .fi-panel-student .fi-sidebar-item-button.fi-active,
    .dark .fi-panel-student .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(56, 189, 248, 0.16) !important;
        color: #38bdf8 !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
    }
    .fi-panel-student .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .fi-panel-student .fi-sidebar-item-button.fi-active svg,
    .fi-panel-student .fi-sidebar-item-button[aria-current="page"] svg {
        color: #0284c7 !important;
    }
    .dark .fi-panel-student .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .dark .fi-panel-student .fi-sidebar-item-button.fi-active svg,
    .dark .fi-panel-student .fi-sidebar-item-button[aria-current="page"] svg {
        color: #38bdf8 !important;
    }

    /* ADMIN PANEL ACTIVE */
    .fi-panel-admin .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .fi-panel-admin .fi-sidebar-item-active .fi-sidebar-item-btn,
    .fi-panel-admin .fi-sidebar-item-button.fi-active,
    .fi-panel-admin .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(15, 118, 110, 0.09) !important;
        color: #0f766e !important;
        font-weight: 600 !important;
        border: 1px solid rgba(15, 118, 110, 0.22) !important;
        box-shadow: 0 1px 3px 0 rgba(15, 118, 110, 0.08) !important;
    }
    .dark .fi-panel-admin .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .dark .fi-panel-admin .fi-sidebar-item-active .fi-sidebar-item-btn,
    .dark .fi-panel-admin .fi-sidebar-item-button.fi-active,
    .dark .fi-panel-admin .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(45, 212, 191, 0.16) !important;
        color: #2dd4bf !important;
        border: 1px solid rgba(45, 212, 191, 0.25) !important;
    }
    .fi-panel-admin .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .fi-panel-admin .fi-sidebar-item-button.fi-active svg,
    .fi-panel-admin .fi-sidebar-item-button[aria-current="page"] svg {
        color: #0f766e !important;
    }
    .dark .fi-panel-admin .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .dark .fi-panel-admin .fi-sidebar-item-button.fi-active svg,
    .dark .fi-panel-admin .fi-sidebar-item-button[aria-current="page"] svg {
        color: #2dd4bf !important;
    }

    /* CONTRIBUTOR PANEL ACTIVE */
    .fi-panel-contributor .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .fi-panel-contributor .fi-sidebar-item-active .fi-sidebar-item-btn,
    .fi-panel-contributor .fi-sidebar-item-button.fi-active,
    .fi-panel-contributor .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(124, 58, 237, 0.09) !important;
        color: #7c3aed !important;
        font-weight: 600 !important;
        border: 1px solid rgba(124, 58, 237, 0.22) !important;
        box-shadow: 0 1px 3px 0 rgba(124, 58, 237, 0.08) !important;
    }
    .dark .fi-panel-contributor .fi-sidebar-item-active > .fi-sidebar-item-btn,
    .dark .fi-panel-contributor .fi-sidebar-item-active .fi-sidebar-item-btn,
    .dark .fi-panel-contributor .fi-sidebar-item-button.fi-active,
    .dark .fi-panel-contributor .fi-sidebar-item-button[aria-current="page"] {
        background: rgba(167, 139, 250, 0.16) !important;
        color: #c084fc !important;
        border: 1px solid rgba(167, 139, 250, 0.25) !important;
    }
    .fi-panel-contributor .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .fi-panel-contributor .fi-sidebar-item-button.fi-active svg,
    .fi-panel-contributor .fi-sidebar-item-button[aria-current="page"] svg {
        color: #7c3aed !important;
    }
    .dark .fi-panel-contributor .fi-sidebar-item-active .fi-sidebar-item-btn svg,
    .dark .fi-panel-contributor .fi-sidebar-item-button.fi-active svg,
    .dark .fi-panel-contributor .fi-sidebar-item-button[aria-current="page"] svg {
        color: #c084fc !important;
    }

    /* Brand Logo Container */
    a.fi-logo,
    a.fi-topbar-brand,
    .fi-topbar-brand,
    .fi-topbar-logo {
        display: inline-flex !important;
        align-items: center !important;
        text-decoration: none !important;
    }

    /* Light Theme: strictly show light logo, hide dark logo */
    .fi-logo-light,
    img.fi-logo-light,
    .fi-topbar-start img.fi-logo-light,
    .fi-topbar img.fi-logo-light,
    img.fi-logo.dark\:hidden,
    .fi-logo img.dark\:hidden,
    img.fi-logo:first-child:not(.dark\:block) {
        display: inline-block !important;
        max-height: 38px !important;
        height: 38px !important;
        width: auto !important;
        object-fit: contain !important;
        transition: transform 0.2s ease;
    }

    .fi-logo-dark,
    img.fi-logo-dark,
    .fi-topbar-start img.fi-logo-dark,
    .fi-topbar img.fi-logo-dark,
    img.fi-logo.hidden,
    img.fi-logo.dark\:block,
    .fi-logo img.hidden,
    .fi-logo img.dark\:block {
        display: none !important;
    }

    /* Dark Theme: strictly hide light logo, show dark logo */
    .dark .fi-logo-light,
    .dark img.fi-logo-light,
    .dark .fi-topbar-start img.fi-logo-light,
    .dark .fi-topbar img.fi-logo-light,
    .dark img.fi-logo.dark\:hidden,
    .dark .fi-logo img.dark\:hidden,
    .dark img.fi-logo:first-child:not(.dark\:block) {
        display: none !important;
    }

    .dark .fi-logo-dark,
    .dark img.fi-logo-dark,
    .dark .fi-topbar-start img.fi-logo-dark,
    .dark .fi-topbar img.fi-logo-dark,
    .dark img.fi-logo.hidden.dark\:block,
    .dark img.fi-logo.dark\:block,
    .dark .fi-logo img.hidden.dark\:block,
    .dark .fi-logo img.dark\:block {
        display: inline-block !important;
        max-height: 38px !important;
        height: 38px !important;
        width: auto !important;
        object-fit: contain !important;
        transition: transform 0.2s ease;
    }

    @media (max-width: 640px) {
        .fi-logo,
        .fi-sidebar-header img,
        .fi-topbar-brand img,
        .fi-topbar img {
            max-height: 32px !important;
            height: 32px !important;
        }

        /* Prevent auto-zoom on iOS input focus */
        .fi-input,
        .fi-select-input,
        .fi-textarea,
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            font-size: 16px !important;
        }

        /* Touch target improvements */
        .fi-btn,
        .hub-btn,
        .hub-chip,
        button {
            touch-action: manipulation;
        }

        /* Mobile Modal Bottom Sheet style */
        .fi-modal-window {
            margin-bottom: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            max-height: 88vh !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile compact table container */
        .fi-ta-ctn {
            border-radius: 14px !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Spacing optimizations for small screens */
        .fi-page-header {
            margin-bottom: 1rem !important;
        }

        .fi-section {
            padding: 0.85rem !important;
            border-radius: 14px !important;
        }
    }

    /* Topbar positioning */
    .fi-topbar {
        overflow: visible !important;
        z-index: 40 !important;
    }

    .fi-topbar-ctn,
    .fi-topbar nav,
    .fi-topbar header {
        overflow: visible !important;
    }

    /* ==========================================================================
       FILAMENT DROPDOWNS & ACTION POPUPS (MATERIAL 3 / MODERN DESIGN)
       ========================================================================== */
    .fi-dropdown {
        position: relative;
        display: inline-block;
    }

    .fi-dropdown-panel,
    [x-ref="panel"].fi-dropdown-panel,
    div[x-ref="panel"]:not(.fi-modal-window) {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 14px 34px -4px rgba(15, 23, 42, 0.15), 0 4px 14px -2px rgba(15, 23, 42, 0.08) !important;
        padding: 6px !important;
        min-width: 210px !important;
        z-index: 99999 !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        overflow: hidden !important;
        color: #1e293b !important;
    }

    .dark .fi-dropdown-panel,
    .dark [x-ref="panel"].fi-dropdown-panel,
    .dark div[x-ref="panel"]:not(.fi-modal-window) {
        background: #11222c !important;
        border: 1px solid #243c49 !important;
        box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.5) !important;
        color: #f1f5f9 !important;
    }

    .fi-dropdown-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
        padding: 2px !important;
        margin: 0 !important;
        list-style: none !important;
    }

    .fi-dropdown-list-item,
    button.fi-dropdown-list-item,
    a.fi-dropdown-list-item,
    .fi-dropdown-list-item-btn {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        gap: 10px !important;
        padding: 8px 12px !important;
        border-radius: 10px !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
        background: transparent !important;
        border: none !important;
        text-align: left !important;
        cursor: pointer !important;
        transition: all 0.15s ease-in-out !important;
        text-decoration: none !important;
        line-height: 1.35 !important;
    }

    .dark .fi-dropdown-list-item,
    .dark button.fi-dropdown-list-item,
    .dark a.fi-dropdown-list-item,
    .dark .fi-dropdown-list-item-btn {
        color: #cbd5e1 !important;
    }

    .fi-dropdown-list-item:hover,
    button.fi-dropdown-list-item:hover,
    a.fi-dropdown-list-item:hover,
    .fi-dropdown-list-item-btn:hover {
        background: rgba(13, 148, 136, 0.1) !important;
        color: #0d9488 !important;
        transform: translateX(2px) !important;
    }

    .dark .fi-dropdown-list-item:hover,
    .dark button.fi-dropdown-list-item:hover,
    .dark a.fi-dropdown-list-item:hover,
    .dark .fi-dropdown-list-item-btn:hover {
        background: rgba(13, 148, 136, 0.22) !important;
        color: #2dd4bf !important;
    }

    .fi-dropdown-list-item svg,
    .fi-dropdown-list-item .fi-icon,
    button.fi-dropdown-list-item svg,
    a.fi-dropdown-list-item svg {
        width: 1.15rem !important;
        height: 1.15rem !important;
        flex-shrink: 0 !important;
        color: #0d9488 !important;
        transition: transform 0.15s ease !important;
    }

    .dark .fi-dropdown-list-item svg,
    .dark button.fi-dropdown-list-item svg,
    .dark a.fi-dropdown-list-item svg {
        color: #2dd4bf !important;
    }

    .fi-dropdown-list-item:hover svg,
    button.fi-dropdown-list-item:hover svg,
    a.fi-dropdown-list-item:hover svg {
        transform: scale(1.12) !important;
    }

    .fi-dropdown-list-item-label {
        flex-grow: 1 !important;
        white-space: nowrap !important;
    }

    /* Modals & Dialog Popups */
    .fi-modal-window {
        border-radius: 20px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        overflow: hidden !important;
    }

    .dark .fi-modal-window {
        border-color: #243c49 !important;
        background: #102028 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
    }

    .fi-modal-header {
        padding: 1.25rem 1.5rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .dark .fi-modal-header {
        border-bottom-color: #1e3542 !important;
    }

    .fi-modal-heading {
        font-family: 'Space Grotesk', sans-serif !important;
        font-weight: 700 !important;
        font-size: 1.15rem !important;
        color: #0f172a !important;
    }

    .dark .fi-modal-heading {
        color: #f1f5f9 !important;
    }

    .fi-modal-content {
        padding: 1.25rem 1.5rem !important;
    }

    .fi-modal-footer {
        padding: 1rem 1.5rem !important;
        background: #f8fafc !important;
        border-top: 1px solid #f1f5f9 !important;
    }

    .dark .fi-modal-footer {
        background: #0d1b22 !important;
        border-top-color: #1e3542 !important;
    }

    /* ==========================================================================
       MOBILE RESPONSIVE FILAMENT ENFORCEMENTS
       ========================================================================== */

    @media (max-width: 639px) {
        .fi-topbar {
            padding-left: max(0.5rem, env(safe-area-inset-left)) !important;
            padding-right: max(0.5rem, env(safe-area-inset-right)) !important;
            padding-top: env(safe-area-inset-top) !important;
        }

        .fi-topbar-item-btn,
        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn {
            min-height: 44px !important;
            min-width: 44px !important;
        }

        .fi-main-ctn {
            padding-left: max(0.75rem, env(safe-area-inset-left)) !important;
            padding-right: max(0.75rem, env(safe-area-inset-right)) !important;
            padding-bottom: max(1rem, env(safe-area-inset-bottom)) !important;
            max-width: 100vw !important;
            overflow-x: clip !important;
        }

        /* Mobile full-width / bottom-sheet modals */
        .fi-modal-window {
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            min-height: 100dvh !important;
            border-radius: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .fi-modal-content {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 1rem !important;
        }

        .fi-modal-footer {
            padding: 0.75rem 1rem max(0.75rem, env(safe-area-inset-bottom)) 1rem !important;
        }

        /* Table responsiveness */
        .fi-ta-ctn {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .fi-ta-table {
            min-width: 100% !important;
        }

        .fi-ta-header-toolbar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem !important;
        }

        .fi-ta-search-field,
        .fi-ta-search-field input {
            width: 100% !important;
        }

    /* ==========================================================================
       DESKTOP & ULTRA-WIDE CONTAINER CONSTRAINTS (1024px, 1440px, 1920px, 4K)
       ========================================================================== */
    @media (min-width: 1024px) {
        .fi-main-ctn {
            max-width: 1680px !important;
            margin-inline: auto !important;
            padding-inline: clamp(1.25rem, 2.5vw, 2.5rem) !important;
            padding-top: 1.5rem !important;
            padding-bottom: 2.5rem !important;
        }

        .fi-page {
            max-width: 100% !important;
        }

        /* Proportional Desktop Modals */
        .fi-modal-window {
            margin: auto !important;
            border-radius: 18px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }

        /* Ensure tables fill desktop space cleanly without forced horizontal scroll */
        .fi-ta-ctn {
            border-radius: 16px !important;
        }

        .fi-ta-table {
            width: 100% !important;
            table-layout: auto !important;
        }
    }

    /* ==========================================================================
       WCAG AA CONTRAST & DARK MODE TABLE HARMONIZATION
       ========================================================================== */

    /* 1. Table Action Links & Buttons */
    .fi-ta-action,
    .fi-ta-actions button,
    .fi-ta-actions a,
    [class*="fi-ta-action"] {
        font-weight: 600 !important;
        transition: color 0.15s ease, opacity 0.15s ease !important;
    }

    .dark .fi-ta-action,
    .dark .fi-ta-actions button,
    .dark .fi-ta-actions a,
    .dark [class*="fi-ta-action"] {
        color: #2dd4bf !important;
    }

    .dark .fi-ta-action:hover,
    .dark .fi-ta-actions button:hover,
    .dark .fi-ta-actions a:hover,
    .dark [class*="fi-ta-action"]:hover {
        color: #5eead4 !important;
    }

    .dark .fi-ta-actions [class*="text-gray"],
    .dark .fi-ta-actions [class*="text-slate"] {
        color: #cbd5e1 !important;
    }

    .dark .fi-ta-actions [class*="text-danger"],
    .dark .fi-ta-actions [class*="text-rose"] {
        color: #fb7185 !important;
    }

    .dark .fi-ta-actions [class*="text-warning"],
    .dark .fi-ta-actions [class*="text-amber"] {
        color: #fbbf24 !important;
    }

    /* 2. Table Headers & Rows */
    .fi-ta-header-cell,
    th.fi-ta-header-cell,
    [class*="fi-ta-header-cell"] {
        background: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 0.72rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .dark .fi-ta-header-cell,
    .dark th.fi-ta-header-cell,
    .dark [class*="fi-ta-header-cell"] {
        background: #11222c !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid #243c49 !important;
    }

    .dark .fi-ta-header-cell-label {
        color: #cbd5e1 !important;
    }

    .dark .fi-ta-cell,
    .dark td.fi-ta-cell,
    .dark [class*="fi-ta-cell"] {
        color: #f1f5f9 !important;
        border-bottom-color: #1e3542 !important;
    }

    .dark .fi-ta-row:hover {
        background: rgba(30, 58, 73, 0.3) !important;
    }

    /* 3. Search Bar, Dropdowns & Inputs in Dark Mode */
    .dark .fi-ta-search-field,
    .dark .fi-ta-search-field input,
    .dark .fi-input-wrp,
    .dark .fi-select-input-wrp,
    .dark .fi-input {
        background: #11222c !important;
        border-color: #243c49 !important;
        color: #f1f5f9 !important;
    }

    .dark .fi-ta-search-field input::placeholder,
    .dark .fi-input::placeholder {
        color: #64748b !important;
    }

    .dark .fi-ta-search-field input:focus,
    .dark .fi-input:focus {
        border-color: #0d9488 !important;
        box-shadow: 0 0 0 1px #0d9488 !important;
    }

    /* 4. Pagination Controls in Dark Mode */
    .dark .fi-pagination,
    .dark .fi-ta-pagination,
    .dark .fi-pagination-item-btn,
    .dark .fi-pagination-nav-btn {
        color: #cbd5e1 !important;
    }

    .dark .fi-pagination-item-btn,
    .dark .fi-pagination-nav-btn {
        background: #11222c !important;
        border: 1px solid #243c49 !important;
    }

    .dark .fi-pagination-item-btn:hover,
    .dark .fi-pagination-nav-btn:hover {
        background: #193240 !important;
        color: #ffffff !important;
    }

    .dark .fi-pagination-item-btn.fi-active,
    .dark [aria-current="page"].fi-pagination-item-btn {
        background: #0d9488 !important;
        color: #ffffff !important;
        border-color: #0d9488 !important;
    }

    .dark .fi-ta-pagination select,
    .dark .fi-pagination select {
        background: #11222c !important;
        border: 1px solid #243c49 !important;
        color: #f1f5f9 !important;
    }

    /* ==========================================================================
       MODERN HIGH-END UI DESIGN SYSTEM FOR ALL EDIT PAGES (INSTRUCTOR & ADMIN)
       ========================================================================== */
    .fi-resource-edit-record-page {
        max-width: 1280px !important;
        margin-inline: auto !important;
        width: 100% !important;
        padding-bottom: 5rem !important;
    }

    /* 1. Modern Command-Center Hero Header Card */
    .fi-resource-edit-record-page > .fi-page-header-main-ctn > .fi-header {
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.94) 100%) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        border-radius: 20px !important;
        padding: 1.4rem 1.75rem !important;
        box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02) !important;
        position: relative !important;
        overflow: hidden !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        margin-bottom: 1.5rem !important;
    }

    .dark .fi-resource-edit-record-page > .fi-page-header-main-ctn > .fi-header {
        background: linear-gradient(135deg, rgba(16, 32, 40, 0.98) 0%, rgba(11, 20, 26, 0.95) 100%) !important;
        border-color: rgba(35, 56, 66, 0.9) !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4) !important;
    }

    .fi-resource-edit-record-page > .fi-page-header-main-ctn > .fi-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #0d9488 0%, #14b8a6 50%, #6366f1 100%);
    }

    @media (min-width: 768px) {
        .fi-resource-edit-record-page > .fi-page-header-main-ctn > .fi-header {
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
    }

    /* Breadcrumbs inside Edit Page */
    .fi-resource-edit-record-page .fi-breadcrumbs {
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        margin-bottom: 0.45rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
    }

    .fi-resource-edit-record-page .fi-breadcrumbs-item-label {
        color: #64748b !important;
        padding: 0.15rem 0.45rem !important;
        border-radius: 6px !important;
        transition: all 0.15s ease !important;
    }

    .fi-resource-edit-record-page .fi-breadcrumbs-item-label:hover {
        background: #f1f5f9 !important;
        color: #0d9488 !important;
    }

    .dark .fi-resource-edit-record-page .fi-breadcrumbs-item-label {
        color: #94a3b8 !important;
    }

    .dark .fi-resource-edit-record-page .fi-breadcrumbs-item-label:hover {
        background: #1e293b !important;
        color: #14b8a6 !important;
    }

    /* Heading & Subheading */
    .fi-resource-edit-record-page .fi-header-heading {
        font-size: clamp(1.4rem, 2.2vw, 1.85rem) !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.025em !important;
        line-height: 1.25 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.6rem !important;
        flex-wrap: wrap !important;
    }

    .dark .fi-resource-edit-record-page .fi-header-heading {
        color: #ffffff !important;
    }

    .fi-resource-edit-record-page .fi-header-subheading {
        font-size: 0.825rem !important;
        color: #64748b !important;
        margin-top: 0.35rem !important;
        line-height: 1.5 !important;
        font-weight: 500 !important;
    }

    .dark .fi-resource-edit-record-page .fi-header-subheading {
        color: #94a3b8 !important;
    }

    /* Header Actions */
    .fi-resource-edit-record-page .fi-header-actions {
        display: flex !important;
        align-items: center !important;
        gap: 0.65rem !important;
        flex-wrap: wrap !important;
    }

    /* 2. Modern Form Layout & Sections */
    .fi-resource-edit-record-page form#form {
        display: grid !important;
        gap: 1.5rem !important;
    }

    .fi-resource-edit-record-page .fi-section {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
        box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02) !important;
        overflow: hidden !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .dark .fi-resource-edit-record-page .fi-section {
        background: #111b21 !important;
        border-color: #1f2c34 !important;
        box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.3) !important;
    }

    .fi-resource-edit-record-page .fi-section:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.07) !important;
    }

    .dark .fi-resource-edit-record-page .fi-section:hover {
        border-color: #2a3942 !important;
    }

    /* Section Header */
    .fi-resource-edit-record-page .fi-section-header {
        padding: 1.15rem 1.5rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%) !important;
    }

    .dark .fi-resource-edit-record-page .fi-section-header {
        border-bottom-color: #1a2730 !important;
        background: linear-gradient(180deg, #16232b 0%, #111b21 100%) !important;
    }

    .fi-resource-edit-record-page .fi-section-header-heading {
        font-size: 1.02rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        letter-spacing: -0.01em !important;
    }

    .dark .fi-resource-edit-record-page .fi-section-header-heading {
        color: #f1f5f9 !important;
    }

    .fi-resource-edit-record-page .fi-section-header-heading::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 16px;
        border-radius: 999px;
        background: linear-gradient(180deg, #0d9488, #14b8a6);
        flex-shrink: 0;
    }

    .fi-resource-edit-record-page .fi-section-header-description {
        font-size: 0.78rem !important;
        color: #64748b !important;
        margin-top: 0.25rem !important;
        line-height: 1.4 !important;
        font-weight: 400 !important;
    }

    .dark .fi-resource-edit-record-page .fi-section-header-description {
        color: #94a3b8 !important;
    }

    .fi-resource-edit-record-page .fi-section-content {
        padding: 1.5rem !important;
    }

    /* 3. Field Wrappers, Labels, and Validation */
    .fi-resource-edit-record-page .fi-fo-field-wrp {
        margin-bottom: 0.4rem !important;
    }

    .fi-resource-edit-record-page .fi-fo-field-wrp-label {
        font-size: 0.79rem !important;
        font-weight: 700 !important;
        color: #334155 !important;
        letter-spacing: 0.01em !important;
        margin-bottom: 0.35rem !important;
    }

    .dark .fi-resource-edit-record-page .fi-fo-field-wrp-label {
        color: #cbd5e1 !important;
    }

    .fi-resource-edit-record-page .fi-fo-field-wrp-label sup {
        color: #ef4444 !important;
        font-weight: 800 !important;
        font-size: 0.85rem !important;
        margin-left: 2px !important;
    }

    .fi-resource-edit-record-page .fi-fo-field-wrp-helper-text {
        font-size: 0.73rem !important;
        color: #64748b !important;
        line-height: 1.4 !important;
        margin-top: 0.35rem !important;
    }

    .dark .fi-resource-edit-record-page .fi-fo-field-wrp-helper-text {
        color: #94a3b8 !important;
    }

    .fi-resource-edit-record-page .fi-fo-field-wrp-error-message {
        font-size: 0.73rem !important;
        font-weight: 600 !important;
        color: #ef4444 !important;
        margin-top: 0.3rem !important;
    }

    /* 4. Modern Input Containers & Controls */
    .fi-resource-edit-record-page .fi-input-wrp {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02) inset !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .dark .fi-resource-edit-record-page .fi-input-wrp {
        background-color: #0b141a !important;
        border-color: #233842 !important;
    }

    .fi-resource-edit-record-page .fi-input-wrp:hover {
        border-color: #cbd5e1 !important;
    }

    .dark .fi-resource-edit-record-page .fi-input-wrp:hover {
        border-color: #2e4754 !important;
    }

    .fi-resource-edit-record-page .fi-input-wrp:focus-within {
        background-color: #ffffff !important;
        border-color: #0d9488 !important;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.18) !important;
    }

    .dark .fi-resource-edit-record-page .fi-input-wrp:focus-within {
        background-color: #111b21 !important;
        border-color: #0d9488 !important;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.25) !important;
    }

    .fi-resource-edit-record-page .fi-input,
    .fi-resource-edit-record-page .fi-textarea {
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        color: #0f172a !important;
        line-height: 1.45 !important;
        padding: 0.58rem 0.85rem !important;
    }

    .dark .fi-resource-edit-record-page .fi-input,
    .dark .fi-resource-edit-record-page .fi-textarea {
        color: #f8fafc !important;
    }

    .fi-resource-edit-record-page .fi-select-input {
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        color: #0f172a !important;
        cursor: pointer !important;
        padding: 0.58rem 0.85rem !important;
    }

    .dark .fi-resource-edit-record-page .fi-select-input {
        color: #f8fafc !important;
    }

    /* Modern File Uploads */
    .fi-resource-edit-record-page .fi-fo-file-upload {
        border-radius: 16px !important;
    }

    .fi-resource-edit-record-page .filepond--drop-label {
        border: 2px dashed #cbd5e1 !important;
        border-radius: 14px !important;
        background: #f8fafc !important;
        transition: all 0.2s ease !important;
    }

    .dark .fi-resource-edit-record-page .filepond--drop-label {
        border-color: #233842 !important;
        background: #0b141a !important;
    }

    .fi-resource-edit-record-page .filepond--drop-label:hover {
        border-color: #0d9488 !important;
        background: rgba(13, 148, 136, 0.04) !important;
    }

    /* Repeater & Builder Items */
    .fi-resource-edit-record-page .fi-fo-repeater-item {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0 !important;
        background: #fafbfc !important;
        margin-bottom: 0.85rem !important;
        overflow: hidden !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
    }

    .dark .fi-resource-edit-record-page .fi-fo-repeater-item {
        border-color: #1f2c34 !important;
        background: #0e171d !important;
    }

    .fi-resource-edit-record-page .fi-fo-repeater-item-header {
        background: #f1f5f9 !important;
        padding: 0.55rem 0.95rem !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        color: #475569 !important;
    }

    .dark .fi-resource-edit-record-page .fi-fo-repeater-item-header {
        background: #16232b !important;
        border-color: #1f2c34 !important;
        color: #cbd5e1 !important;
    }

    /* 5. Sticky Floating Action Dock (Form Footer) */
    .fi-resource-edit-record-page .fi-form-actions {
        position: sticky !important;
        bottom: 1.25rem !important;
        z-index: 35 !important;
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        border-radius: 9999px !important;
        box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.12), 0 4px 10px -2px rgba(15, 23, 42, 0.05) !important;
        padding: 0.65rem 1.35rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 0.75rem !important;
        margin-top: 1.5rem !important;
        width: fit-content !important;
        margin-left: auto !important;
    }

    .dark .fi-resource-edit-record-page .fi-form-actions {
        background: rgba(17, 27, 33, 0.9) !important;
        border-color: rgba(35, 56, 66, 0.95) !important;
        box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.5) !important;
    }

    /* Save Changes Button in Edit Pages */
    .fi-resource-edit-record-page .fi-form-actions button[type="submit"] {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
        color: #ffffff !important;
        border: 1px solid #0f766e !important;
        box-shadow: 0 4px 14px -2px rgba(13, 148, 136, 0.4) !important;
        border-radius: 9999px !important;
        padding: 0.55rem 1.4rem !important;
        font-weight: 700 !important;
        font-size: 0.825rem !important;
        transition: all 0.18s ease !important;
    }

    .fi-resource-edit-record-page .fi-form-actions button[type="submit"]:hover {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 20px -2px rgba(13, 148, 136, 0.5) !important;
    }

    /* Cancel / Back Button in Edit Pages */
    .fi-resource-edit-record-page .fi-form-actions button[type="button"],
    .fi-resource-edit-record-page .fi-form-actions a {
        background: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 9999px !important;
        padding: 0.55rem 1.2rem !important;
        font-weight: 600 !important;
        font-size: 0.825rem !important;
        transition: all 0.18s ease !important;
    }

    .dark .fi-resource-edit-record-page .fi-form-actions button[type="button"],
    .dark .fi-resource-edit-record-page .fi-form-actions a {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .fi-resource-edit-record-page .fi-form-actions button[type="button"]:hover,
    .fi-resource-edit-record-page .fi-form-actions a:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        transform: translateY(-1px) !important;
    }

    .dark .fi-resource-edit-record-page .fi-form-actions button[type="button"]:hover,
    .dark .fi-resource-edit-record-page .fi-form-actions a:hover {
        background: #334155 !important;
        color: #ffffff !important;
    }

    /* 6. Relation Managers Under Edit Pages */
    .fi-resource-edit-record-page .fi-resource-relation-managers {
        margin-top: 2rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 1.5rem !important;
    }

    .fi-resource-edit-record-page .fi-resource-relation-managers .fi-tabs {
        background: var(--hub-surface-soft, #f1f5f9) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 3px !important;
        gap: 4px !important;
    }

    .dark .fi-resource-edit-record-page .fi-resource-relation-managers .fi-tabs {
        background: #16232b !important;
        border-color: #233842 !important;
    }
</style>

<script>
    (() => {
        // Enforce viewport-fit=cover on panel loads
        const ensureViewportMeta = () => {
            let meta = document.querySelector('meta[name="viewport"]');
            if (meta) {
                if (!meta.content.includes('viewport-fit=cover')) {
                    meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover';
                }
            } else {
                meta = document.createElement('meta');
                meta.name = 'viewport';
                meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover';
                document.head.appendChild(meta);
            }
        };
        ensureViewportMeta();

        const closeMobileSidebar = () => {
            if (window.innerWidth < 1024) {
                try {
                    localStorage.setItem('isOpen', 'false');
                    localStorage.setItem('_x_isOpen', 'false');
                } catch (e) {}

                if (window.Alpine && window.Alpine.store('sidebar')) {
                    const store = window.Alpine.store('sidebar');
                    store.isOpen = false;
                    store.close();
                }

                document.querySelectorAll('.fi-sidebar, .fi-main-sidebar').forEach(el => {
                    el.classList.remove('fi-sidebar-open');
                });
            }
        };

        // Ensure sidebar starts closed on mobile
        document.addEventListener('alpine:init', () => {
            if (window.innerWidth < 1024 && window.Alpine && window.Alpine.store('sidebar')) {
                window.Alpine.store('sidebar').isOpen = false;
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            ensureViewportMeta();
            if (window.innerWidth < 1024) {
                closeMobileSidebar();
            }
        });

        document.addEventListener('livewire:navigated', () => {
            ensureViewportMeta();
            if (window.innerWidth < 1024) {
                closeMobileSidebar();
            }
        });

        // Close sidebar on mobile when tapping close button, overlay, or actual navigation link
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                const target = e.target;
                
                // Explicit close triggers
                const isCloseTrigger = target.closest('.hub-sidebar-mobile-close, .fi-sidebar-close-overlay, .fi-topbar-close-sidebar-btn');
                
                // Actual links (not accordion/collapse group headers)
                const isNavLink = target.closest('.fi-sidebar-nav a, a.fi-sidebar-item-btn, .fi-sidebar-item-has-url > .fi-sidebar-item-btn');

                if (isCloseTrigger || isNavLink) {
                    closeMobileSidebar();
                }
            }
        });
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

