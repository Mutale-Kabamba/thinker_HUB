<div style="padding:0.85rem 1rem;background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
        <h4 style="margin:0;font-size:0.88rem;font-weight:700;color:var(--hub-ink);">{{ $assessment['name'] }}</h4>
        <button @click="expanded = null; panel = null;" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
    </div>

    @if (!empty($assessment['description']))
        <div style="padding:0.55rem 0.75rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;margin-bottom:0.5rem;">
            <p style="margin:0;font-size:0.76rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;letter-spacing:0.03em;">Description</p>
            <p style="margin:0.25rem 0 0;font-size:0.82rem;color:var(--hub-muted);white-space:pre-line;">{{ $assessment['description'] }}</p>
        </div>
    @endif

    {{-- Score & Feedback --}}
    @if (in_array($assessment['submission_status'], ['Graded', 'Checked']))
        <div style="padding:0.55rem 0.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:0.5rem;">
            <div style="display:flex;gap:1.2rem;align-items:center;flex-wrap:wrap;">
                @if ($assessment['score'] !== null && $assessment['score'] !== '-')
                    <div>
                        <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Score</p>
                        <p style="margin:0;font-size:1.15rem;font-weight:800;color:#15803d;">{{ $assessment['score'] }}%</p>
                    </div>
                @endif
                <div>
                    <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Status</p>
                    <p style="margin:0;font-size:0.84rem;font-weight:600;color:#15803d;">{{ $assessment['submission_status'] }}</p>
                </div>
            </div>
            @if (!empty($assessment['feedback']))
                <div style="margin-top:0.45rem;border-top:1px solid #bbf7d0;padding-top:0.45rem;">
                    <p style="margin:0;font-size:0.7rem;font-weight:600;color:#166534;text-transform:uppercase;">Feedback</p>
                    <p style="margin:0.2rem 0 0;font-size:0.82rem;color:#14532d;white-space:pre-line;">{{ $assessment['feedback'] }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Submitted Attachments --}}
    @if ($assessment['submission_status'] !== 'Not submitted')
        @if (!empty($assessment['submission']['file']) || !empty($assessment['submission']['link']) || !empty($assessment['submission']['video']))
            <div style="padding:0.55rem 0.75rem;background:#fff;border:1px solid var(--hub-border);border-radius:8px;margin-bottom:0.5rem;">
                <p style="margin:0 0 0.3rem;font-size:0.76rem;font-weight:600;color:var(--hub-ink);text-transform:uppercase;letter-spacing:0.03em;">Your Submission</p>
                @if (!empty($assessment['submission']['file']))
                    <p style="margin:0.15rem 0;font-size:0.8rem;">📄 <a href="#" @click.prevent="openViewer(@js(route('file.view', ['type' => 'assessment-submission', 'id' => $assessment['submission']['id']])), @js('Submission.' . pathinfo($assessment['submission']['file'], PATHINFO_EXTENSION)))" style="color:#0e7490;text-decoration:underline;cursor:pointer;">View uploaded file</a></p>
                @endif
                @if (!empty($assessment['submission']['link']))
                    <p style="margin:0.15rem 0;font-size:0.8rem;">🔗 <a href="{{ $assessment['submission']['link'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assessment['submission']['link'], 60) }}</a></p>
                @endif
                @if (!empty($assessment['submission']['video']))
                    <p style="margin:0.15rem 0;font-size:0.8rem;">🎬 <a href="{{ $assessment['submission']['video'] }}" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">{{ Str::limit($assessment['submission']['video'], 60) }}</a></p>
                @endif
            </div>
        @endif
    @endif
</div>
