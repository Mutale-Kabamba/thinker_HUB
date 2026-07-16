<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#0e7490">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="ThinkerHUB">
<link rel="apple-touch-icon" href="{{ asset('images/logos/green_white.png') }}">
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js').catch(function () {});
    });
}
</script>
