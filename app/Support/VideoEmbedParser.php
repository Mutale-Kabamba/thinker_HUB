<?php

namespace App\Support;

class VideoEmbedParser
{
    /**
     * Parse any video URL or raw ID into platform, embed URL, and thumbnail metadata.
     * Supports YouTube, Vimeo, Loom, Dailymotion, Wistia, Google Drive, direct video files,
     * and generic iframe/embed URLs.
     *
     * @return array{
     *     platform: ?string,
     *     video_id: ?string,
     *     youtube_id: ?string,
     *     embed_url: ?string,
     *     thumbnail_url: ?string,
     *     is_direct_file: bool
     * }
     */
    public static function parse(?string $url): array
    {
        $empty = [
            'platform' => null,
            'video_id' => null,
            'youtube_id' => null,
            'embed_url' => null,
            'thumbnail_url' => null,
            'is_direct_file' => false,
        ];

        if (! $url) {
            return $empty;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return $empty;
        }

        // If user pasted a full <iframe ... src="..."> snippet, extract the src URL
        if (stripos($trimmed, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $trimmed, $iframeMatch)) {
            $trimmed = html_entity_decode(trim($iframeMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 1. YouTube (watch, youtu.be, shorts, live, embed, or raw 11-char ID)
        $youtubeId = self::extractYoutubeId($trimmed);
        if ($youtubeId !== null) {
            return [
                'platform' => 'youtube',
                'video_id' => $youtubeId,
                'youtube_id' => $youtubeId,
                'embed_url' => "https://www.youtube.com/embed/{$youtubeId}",
                'thumbnail_url' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
                'is_direct_file' => false,
            ];
        }

        // 2. Vimeo (vimeo.com/123456, vimeo.com/123456/hash, vimeo.com/channels/.../123456, player.vimeo.com/video/123456)
        if (preg_match('~(?:player\.vimeo\.com/video/|vimeo\.com/(?:channels/[^/]+/|groups/[^/]+/videos/|album/\d+/video/|video/|manage/videos/)?)(\d+)(?:/([a-zA-Z0-9]+))?(?:\?.*)?$~i', $trimmed, $vimeoMatches)) {
            $vimeoId = $vimeoMatches[1];
            $hash = $vimeoMatches[2] ?? null;
            if (! $hash && preg_match('/[?&]h=([a-zA-Z0-9]+)/', $trimmed, $hMatch)) {
                $hash = $hMatch[1];
            }
            $embedUrl = "https://player.vimeo.com/video/{$vimeoId}" . ($hash ? "?h={$hash}" : '');

            return [
                'platform' => 'vimeo',
                'video_id' => $vimeoId,
                'youtube_id' => null,
                'embed_url' => $embedUrl,
                'thumbnail_url' => "https://vumbnail.com/{$vimeoId}.jpg",
                'is_direct_file' => false,
            ];
        }

        // 3. Loom (loom.com/share/{id} or loom.com/embed/{id})
        if (preg_match('~loom\.com/(?:share|embed)/([a-zA-Z0-9]+)~i', $trimmed, $loomMatches)) {
            $loomId = $loomMatches[1];

            return [
                'platform' => 'loom',
                'video_id' => $loomId,
                'youtube_id' => null,
                'embed_url' => "https://www.loom.com/embed/{$loomId}",
                'thumbnail_url' => null,
                'is_direct_file' => false,
            ];
        }

        // 4. Dailymotion (dailymotion.com/video/{id}, dai.ly/{id}, dailymotion.com/embed/video/{id})
        if (preg_match('~(?:dailymotion\.com/(?:embed/)?video/|dai\.ly/)([a-zA-Z0-9]+)~i', $trimmed, $dmMatches)) {
            $dmId = strtok($dmMatches[1], '_');

            return [
                'platform' => 'dailymotion',
                'video_id' => $dmId,
                'youtube_id' => null,
                'embed_url' => "https://www.dailymotion.com/embed/video/{$dmId}",
                'thumbnail_url' => "https://www.dailymotion.com/thumbnail/video/{$dmId}",
                'is_direct_file' => false,
            ];
        }

        // 5. Wistia (*.wistia.com/medias/{id}, fast.wistia.net/embed/iframe/{id})
        if (preg_match('~(?:wistia\.com/medias/|fast\.wistia\.(?:net|com)/embed/iframe/)([a-zA-Z0-9]+)~i', $trimmed, $wistiaMatches)) {
            $wistiaId = $wistiaMatches[1];

            return [
                'platform' => 'wistia',
                'video_id' => $wistiaId,
                'youtube_id' => null,
                'embed_url' => "https://fast.wistia.net/embed/iframe/{$wistiaId}",
                'thumbnail_url' => null,
                'is_direct_file' => false,
            ];
        }

        // 6. Google Drive (drive.google.com/file/d/{id}/view or /preview)
        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~i', $trimmed, $driveMatches)) {
            $driveId = $driveMatches[1];

            return [
                'platform' => 'google_drive',
                'video_id' => $driveId,
                'youtube_id' => null,
                'embed_url' => "https://drive.google.com/file/d/{$driveId}/preview",
                'thumbnail_url' => null,
                'is_direct_file' => false,
            ];
        }

        // 7. Direct video file URL (.mp4, .webm, .ogg, .mov, .m4v, .m3u8)
        $pathWithoutQuery = strtok($trimmed, '?#') ?: $trimmed;
        if (preg_match('~\.(mp4|webm|ogg|ogv|mov|m4v|m3u8)$~i', $pathWithoutQuery)) {
            return [
                'platform' => 'direct',
                'video_id' => null,
                'youtube_id' => null,
                'embed_url' => $trimmed,
                'thumbnail_url' => null,
                'is_direct_file' => true,
            ];
        }

        // 8. Generic valid http(s) embed/video URL from any other video platform
        if (filter_var($trimmed, FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $trimmed)) {
            return [
                'platform' => 'embed',
                'video_id' => null,
                'youtube_id' => null,
                'embed_url' => $trimmed,
                'thumbnail_url' => null,
                'is_direct_file' => false,
            ];
        }

        return $empty;
    }

    /**
     * Extract an 11-character YouTube video ID from a URL or raw ID.
     */
    public static function extractYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $trimmed = trim($url);

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $trimmed)) {
            return $trimmed;
        }

        // Only match against YouTube domains so Vimeo/Loom/etc. paths are never misidentified
        if (! preg_match('~(?:youtube\.com|youtu\.be|youtube-nocookie\.com)~i', $trimmed)) {
            return null;
        }

        $patterns = [
            '/(?:youtube(?:-nocookie)?\.com\/watch\?v=|youtu\.be\/|youtube(?:-nocookie)?\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/|youtube\.com\/live\/)([a-zA-Z0-9_-]{11})/',
            '/youtube(?:-nocookie)?\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $trimmed, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Append query parameters safely to an embed URL.
     *
     * @param  array<string, string|int>  $params
     */
    public static function appendQuery(?string $embedUrl, array $params): ?string
    {
        if (! $embedUrl) {
            return null;
        }

        if ($params === []) {
            return $embedUrl;
        }

        $separator = str_contains($embedUrl, '?') ? '&' : '?';

        return $embedUrl . $separator . http_build_query($params);
    }
}
