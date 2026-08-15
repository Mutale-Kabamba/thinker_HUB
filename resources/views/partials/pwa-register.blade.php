<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#006a67">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="think.er HUB">
<meta name="application-name" content="think.er HUB">
<meta name="msapplication-TileColor" content="#006a67">
<link rel="apple-touch-icon" href="{{ asset('images/logos/icon_green.png') }}">
<link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logos/icon_green.png') }}">
<link rel="apple-touch-icon" sizes="512x512" href="{{ asset('images/logos/icon_green.png') }}">

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
            .then(function (registration) {
                // Check for updates on page load
                registration.update().catch(function () {});
            })
            .catch(function () {});
    });
}
</script>

