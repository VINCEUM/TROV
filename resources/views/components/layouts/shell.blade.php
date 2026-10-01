@props(['wide' => false])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TROV' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="desk">
        <div class="window {{ $wide ? 'wide' : '' }}" id="win">
            <div class="titlebar">
                <span class="brand">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.6c.9 4.4 2.6 6.2 7 7.1-3.1.6-4.9 1.8-6 3.7-1.1 1.9-1 4.2-1 6.4-.5-3-1.3-5.1-2.9-6.5C7.5 10.9 5.2 10.2 2 9.7c3.5-.7 5.5-1.6 6.9-3.1C10.2 5.2 11 3.7 12 1.6z" fill="currentColor"/><circle cx="17.6" cy="17.8" r="2.6" fill="currentColor" opacity=".45"/></svg>
                    trov
                </span>
                <span class="win-ctl">
                    <span><svg viewBox="0 0 14 14" stroke="currentColor" stroke-width="1.4"><path d="M2 7h10"/></svg></span>
                    <span><svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2.5" y="2.5" width="9" height="9" rx="1.5"/></svg></span>
                    <span><svg viewBox="0 0 14 14" stroke="currentColor" stroke-width="1.4"><path d="M3 3l8 8M11 3l-8 8"/></svg></span>
                </span>
            </div>
            {{ $slot }}
        </div>
        <p class="desk-note">TROV · Kingdom Production Company workplace console.</p>
    </div>

    @include('partials.icons')
    @stack('scripts')
</body>
</html>
