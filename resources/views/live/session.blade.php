<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Session | think.er HUB</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #020617;
            color: #e2e8f0;
        }
        .live-shell {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
        }
        .live-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(2, 6, 23, 0.92);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .live-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #f8fafc;
        }
        .live-meta {
            margin: 0.2rem 0 0;
            font-size: 0.76rem;
            color: #94a3b8;
        }
        .live-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .live-breakouts {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.38rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            background: rgba(15, 23, 42, 0.8);
            color: #e2e8f0;
        }
        .btn-danger {
            border-color: rgba(239, 68, 68, 0.55);
            color: #fecaca;
        }
        #meet-stage {
            width: 100%;
            height: calc(100vh - 66px);
        }
    </style>
</head>
<body>
<div class="live-shell">
    <header class="live-topbar">
        <div>
            <p class="live-title">{{ $session->course?->code ?: ($session->course?->title ?: 'Live Session') }} • Live</p>
            <p class="live-meta">
                Room: {{ $roomCode }}
                @if ($isHost)
                    • Host controls enabled
                @endif
            </p>
        </div>

        <div class="live-actions">
            @if ($isHost)
                <div class="live-breakouts" id="breakout-host-controls">
                    <button type="button" class="btn" data-breakout-count="2">2 Breakouts</button>
                    <button type="button" class="btn" data-breakout-count="3">3 Breakouts</button>
                    <button type="button" class="btn" data-breakout-count="4">4 Breakouts</button>
                </div>
            @endif

            @if (count($breakoutRooms) > 0)
                <div class="live-breakouts" id="breakout-links">
                    @foreach ($breakoutRooms as $breakout)
                        @php($slug = \Illuminate\Support\Str::slug($breakout))
                        <a href="{{ route('live.sessions.show', ['session' => $session->id, 'breakout' => $slug]) }}" class="btn">{{ $breakout }}</a>
                    @endforeach
                    <a href="{{ route('live.sessions.show', ['session' => $session->id]) }}" class="btn">Main Room</a>
                </div>
            @endif

            <a href="{{ $backUrl }}" class="btn">Back to Schedule</a>
            @if ($isHost)
                <form method="POST" action="{{ route('live.sessions.end', ['session' => $session->id]) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">End Session</button>
                </form>
            @endif
        </div>
    </header>

    <main id="meet-stage" aria-label="Live meeting stage"></main>
</div>

<script src="https://{{ $jitsiDomain }}/external_api.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const sessionId = @json($session->id);
    const isHost = @json($isHost);
    const currentBreakout = @json($currentBreakout ?: null);

    const endpoints = {
        attendanceJoin: @json(route('live.sessions.attendance.join', ['session' => $session->id])),
        attendanceHeartbeat: @json(route('live.sessions.attendance.heartbeat', ['session' => $session->id])),
        attendanceLeave: @json(route('live.sessions.attendance.leave', ['session' => $session->id])),
        recordingUpdate: @json(route('live.sessions.recording.update', ['session' => $session->id])),
        breakoutsUpdate: @json(route('live.sessions.breakouts.update', ['session' => $session->id])),
    };

    async function postJson(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload || {}),
        });

        if (!res.ok) {
            throw new Error('Request failed');
        }

        return res.json();
    }

    const options = {
        roomName: @json($roomCode),
        parentNode: document.querySelector('#meet-stage'),
        userInfo: {
            displayName: @json($displayName),
        },
        configOverwrite: {
            prejoinPageEnabled: true,
            startWithAudioMuted: false,
            startWithVideoMuted: false,
            disableModeratorIndicator: false,
            enableNoisyMicDetection: true,
            enableClosePage: false,
            toolbarButtons: [
                'microphone',
                'camera',
                'closedcaptions',
                'desktop',
                'fullscreen',
                'fodeviceselection',
                'hangup',
                'profile',
                'chat',
                'recording',
                'livestreaming',
                'settings',
                'raisehand',
                'videoquality',
                'filmstrip',
                'tileview',
                'download',
                'help',
                'mute-everyone',
                'security'
            ]
        },
        interfaceConfigOverwrite: {
            MOBILE_APP_PROMO: false,
            SHOW_JITSI_WATERMARK: false,
            SHOW_WATERMARK_FOR_GUESTS: false,
            DEFAULT_BACKGROUND: '#020617',
            TOOLBAR_ALWAYS_VISIBLE: false,
        }
    };

    const api = new JitsiMeetExternalAPI(@json($jitsiDomain), options);

    let heartbeatTimer = null;
    let leaveSent = false;

    function sendLeaveBeacon() {
        if (leaveSent) {
            return;
        }

        leaveSent = true;

        fetch(endpoints.attendanceLeave, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            keepalive: true,
            body: JSON.stringify({
                reason: 'page_unload',
                breakout: currentBreakout,
            }),
        }).catch(() => {
            // Best-effort during unload.
        });
    }

    async function createBreakouts(count) {
        const names = Array.from({ length: count }, (_, index) => `Breakout ${index + 1}`);

        try {
            const response = await postJson(endpoints.breakoutsUpdate, { breakouts: names });

            if (Array.isArray(response.breakouts)) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to save breakouts', error);
        }
    }

    const hostControls = document.getElementById('breakout-host-controls');
    if (hostControls) {
        hostControls.querySelectorAll('[data-breakout-count]').forEach((button) => {
            button.addEventListener('click', () => {
                const count = Number(button.getAttribute('data-breakout-count'));
                if (count > 0) {
                    createBreakouts(count);
                }
            });
        });
    }

    window.addEventListener('beforeunload', sendLeaveBeacon);
    window.addEventListener('pagehide', sendLeaveBeacon);

    api.addEventListener('videoConferenceJoined', async () => {
        try {
            await postJson(endpoints.attendanceJoin, {
                device: 'browser',
                breakout: currentBreakout,
            });
        } catch (error) {
            console.error('Attendance join failed', error);
        }

        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
        }

        heartbeatTimer = setInterval(async () => {
            try {
                await postJson(endpoints.attendanceHeartbeat, {
                    breakout: currentBreakout,
                });
            } catch (error) {
                console.error('Attendance heartbeat failed', error);
            }
        }, 30000);

        if (isHost) {
            setTimeout(() => {
                try {
                    api.executeCommand('toggleLobby', true);
                } catch (error) {
                    console.warn('Unable to enforce lobby mode', error);
                }
            }, 1200);
        }
    });

    api.addEventListener('recordingStatusChanged', async (event) => {
        if (!isHost) {
            return;
        }

        try {
            await postJson(endpoints.recordingUpdate, {
                recording_status: event?.status || null,
                recording_url: event?.link || null,
            });
        } catch (error) {
            console.error('Recording metadata save failed', error);
        }
    });

    api.addEventListener('videoConferenceLeft', async () => {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }

        try {
            await postJson(endpoints.attendanceLeave, {
                reason: 'conference_left',
                breakout: currentBreakout,
            });
            leaveSent = true;
        } catch (error) {
            console.error('Attendance leave failed', error);
        }
    });

    api.addEventListener('readyToClose', () => {
        window.location.href = @json($backUrl);
    });
</script>
</body>
</html>
