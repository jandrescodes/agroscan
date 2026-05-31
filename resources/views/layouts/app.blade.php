<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AgroScan') — AgroScan Bolivia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen antialiased" style="background-color: #f4efe3; color: #1c1a12;">

    <header style="background-color: #192d0b; border-bottom: 1px solid #243f10;">
        <div class="mx-auto flex max-w-2xl items-center gap-3 px-4 py-4">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true">
                <path d="M5 25C5 25 9 12 23 7C23 7 25 21 15 25C10 27 6 26 5 25Z" fill="#6cb33e"/>
                <path d="M5 25C9.5 18.5 14 13.5 23 7" stroke="#3d7a1a" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <a href="{{ route('diagnosis.create') }}"
               style="font-family: 'Fraunces', Georgia, serif; font-size: 1.2rem; font-weight: 700; color: #f0ead8; letter-spacing: -0.02em; text-decoration: none;">
                AgroScan
            </a>
            <span style="color: #6cb33e; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.14em; padding-top: 2px;">
                SCZ · BO
            </span>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
