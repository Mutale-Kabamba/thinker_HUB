<?php

namespace App\Http\Controllers;

use App\Models\CourseSession;
use App\Models\LiveSessionAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    public function show(Request $request, CourseSession $session): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->isUserParticipant($user)) {
            abort(403);
        }

        $isHost = $session->canUserStartLive($user);

        if ($isHost && $request->boolean('host')) {
            if (! $session->live_started_at) {
                $session->forceFill(['live_started_at' => now(), 'live_ended_at' => null])->save();
            }
        }

        if (! $session->canUserJoinLive($user)) {
            return redirect()->back()->with('warning', 'This live session is not available yet. Ask the instructor to start it first.');
        }

        $baseRoomCode = $session->ensureLiveRoomCode();
        $breakout = Str::slug((string) $request->query('breakout', ''));
        $roomCode = $breakout !== '' ? $baseRoomCode . '-bo-' . $breakout : $baseRoomCode;
        $jitsiDomain = rtrim((string) config('services.jitsi.domain', 'meet.jit.si'), '/');

        $this->touchAttendance($session, $user->id, $isHost ? 'host' : 'participant', [
            'event' => 'show',
            'breakout' => $breakout !== '' ? $breakout : null,
        ]);

        $preferExternalOnPublic = (bool) config('services.jitsi.prefer_external_on_public', true);

        if ($preferExternalOnPublic && strcasecmp($jitsiDomain, 'meet.jit.si') === 0) {
            $hashParams = http_build_query([
                'userInfo.displayName' => (string) $user->name,
                'config.prejoinPageEnabled' => 'true',
                'interfaceConfig.SHOW_JITSI_WATERMARK' => 'false',
                'interfaceConfig.SHOW_WATERMARK_FOR_GUESTS' => 'false',
                'interfaceConfig.SHOW_BRAND_WATERMARK' => 'false',
                'interfaceConfig.JITSI_WATERMARK_LINK' => '',
                'interfaceConfig.DEFAULT_LOGO_URL' => '',
            ]);

            $externalUrl = sprintf(
                'https://%s/%s#%s',
                $jitsiDomain,
                rawurlencode($roomCode),
                $hashParams,
            );

            return redirect()->away($externalUrl);
        }

        return view('live.session', [
            'session' => $session->fresh(['course', 'instructor', 'student']),
            'roomCode' => $roomCode,
            'baseRoomCode' => $baseRoomCode,
            'currentBreakout' => $breakout,
            'breakoutRooms' => $session->breakoutRooms(),
            'jitsiDomain' => $jitsiDomain,
            'jitsiAppId' => trim((string) config('services.jitsi.app_id', '')),
            'jitsiJwt' => trim((string) config('services.jitsi.jwt', '')),
            'displayName' => (string) $user->name,
            'isHost' => $isHost,
            'backUrl' => $user->role === 'instructor'
                ? '/teach/schedule'
                : '/learn/schedule',
        ]);
    }

    public function end(CourseSession $session): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->canUserStartLive($user)) {
            abort(403);
        }

        $session->forceFill([
            'live_ended_at' => now(),
        ])->save();

        return redirect()->back()->with('status', 'Live session ended.');
    }

    public function attendanceJoin(Request $request, CourseSession $session): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->canUserJoinLive($user)) {
            abort(403);
        }

        $attendance = $this->touchAttendance(
            $session,
            $user->id,
            $session->canUserStartLive($user) ? 'host' : 'participant',
            ['event' => 'join', 'payload' => $request->only(['device', 'breakout'])]
        );

        return response()->json([
            'ok' => true,
            'attendance_id' => $attendance->id,
            'joined_at' => optional($attendance->joined_at)->toIso8601String(),
        ]);
    }

    public function attendanceHeartbeat(Request $request, CourseSession $session): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->canUserJoinLive($user)) {
            abort(403);
        }

        $this->touchAttendance(
            $session,
            $user->id,
            $session->canUserStartLive($user) ? 'host' : 'participant',
            ['event' => 'heartbeat', 'payload' => $request->only(['breakout'])]
        );

        return response()->json(['ok' => true]);
    }

    public function attendanceLeave(Request $request, CourseSession $session): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->isUserParticipant($user)) {
            abort(403);
        }

        $active = LiveSessionAttendance::query()
            ->where('course_session_id', $session->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->latest('joined_at')
            ->first();

        if ($active) {
            $meta = is_array($active->meta) ? $active->meta : [];
            $meta['last_event'] = 'leave';
            $meta['leave_payload'] = $request->only(['reason', 'breakout']);

            $active->forceFill([
                'left_at' => now(),
                'last_heartbeat_at' => now(),
                'meta' => $meta,
            ])->save();
        }

        return response()->json(['ok' => true]);
    }

    public function updateRecording(Request $request, CourseSession $session): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->canUserStartLive($user)) {
            abort(403);
        }

        $data = $request->validate([
            'recording_url' => ['nullable', 'url', 'max:2000'],
            'recording_status' => ['nullable', 'string', 'max:100'],
        ]);

        $meta = is_array($session->live_metadata) ? $session->live_metadata : [];
        $meta['recording_url'] = $data['recording_url'] ?? ($meta['recording_url'] ?? null);
        $meta['recording_status'] = $data['recording_status'] ?? ($meta['recording_status'] ?? null);
        $meta['recording_updated_at'] = now()->toIso8601String();

        $session->forceFill(['live_metadata' => $meta])->save();

        return response()->json([
            'ok' => true,
            'recording_url' => $meta['recording_url'] ?? null,
        ]);
    }

    public function updateBreakouts(Request $request, CourseSession $session): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $session->canUserStartLive($user)) {
            abort(403);
        }

        $data = $request->validate([
            'breakouts' => ['required', 'array', 'max:8'],
            'breakouts.*' => ['required', 'string', 'max:50'],
        ]);

        $breakouts = collect($data['breakouts'])
            ->map(fn (string $name): string => trim($name))
            ->filter(fn (string $name): bool => $name !== '')
            ->unique()
            ->values()
            ->all();

        $meta = is_array($session->live_metadata) ? $session->live_metadata : [];
        $meta['breakouts'] = $breakouts;
        $meta['breakouts_updated_at'] = now()->toIso8601String();

        $session->forceFill(['live_metadata' => $meta])->save();

        return response()->json([
            'ok' => true,
            'breakouts' => $breakouts,
        ]);
    }

    private function touchAttendance(CourseSession $session, int $userId, string $role, array $meta = []): LiveSessionAttendance
    {
        $attendance = LiveSessionAttendance::query()
            ->where('course_session_id', $session->id)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->latest('joined_at')
            ->first();

        if (! $attendance) {
            $attendance = LiveSessionAttendance::create([
                'course_session_id' => $session->id,
                'user_id' => $userId,
                'role' => $role,
                'joined_at' => now(),
                'last_heartbeat_at' => now(),
                'meta' => $meta,
            ]);

            return $attendance;
        }

        $existingMeta = is_array($attendance->meta) ? $attendance->meta : [];

        $attendance->forceFill([
            'role' => $role,
            'last_heartbeat_at' => now(),
            'meta' => array_merge($existingMeta, $meta),
        ])->save();

        return $attendance;
    }
}
