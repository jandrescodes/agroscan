@extends('layouts.app')

@section('title', 'Diagnóstico — ' . $diagnosis->crop)

@php
    $risk = $diagnosis->risk_level ?? 'low';
    $hasProblem = (bool) $diagnosis->has_problem;

    $riskConfig = [
        'high'   => ['label' => 'Riesgo Alto',   'bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#ef4444', 'bar' => '#ef4444'],
        'medium' => ['label' => 'Riesgo Medio',  'bg' => '#fef3c7', 'text' => '#92400e', 'dot' => '#f59e0b', 'bar' => '#f59e0b'],
        'low'    => ['label' => 'Riesgo Bajo',   'bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#22c55e', 'bar' => '#22c55e'],
    ];
    $rc = $hasProblem ? ($riskConfig[$risk] ?? $riskConfig['low']) : null;

    $pct = $diagnosis->confidence !== null
        ? min(100, max(0, (int) round($diagnosis->confidence * 100)))
        : null;

    $barColor = match(true) {
        $pct === null      => '#9ca3af',
        $pct >= 75         => '#4e8a1e',
        $pct >= 50         => '#f59e0b',
        default            => '#ef4444',
    };
@endphp

@section('content')

<div class="mx-auto max-w-2xl pb-10" style="animation: fade-in 0.35s ease-out both;">

    {{-- ── Hero image ── --}}
    <div class="relative overflow-hidden" style="height: 280px;">
        <img
            src="{{ asset('storage/' . $diagnosis->image_path) }}"
            alt="Cultivo de {{ $diagnosis->crop }}"
            class="absolute inset-0 h-full w-full object-cover"
        >
        {{-- Gradient overlay --}}
        <div class="absolute inset-0"
             style="background: linear-gradient(to top, rgba(10,22,4,0.88) 0%, rgba(10,22,4,0.3) 50%, rgba(10,22,4,0.1) 100%);">
        </div>

        {{-- Risk badge (top right) --}}
        @if ($hasProblem && $rc)
            <div class="absolute right-4 top-4">
                <div class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5"
                     style="background: {{ $rc['bg'] }}; border: 1px solid {{ $rc['dot'] }}40;">
                    <div class="h-2 w-2 rounded-full" style="background: {{ $rc['dot'] }};"></div>
                    <span class="text-xs font-bold" style="color: {{ $rc['text'] }};">{{ $rc['label'] }}</span>
                </div>
            </div>
        @else
            <div class="absolute right-4 top-4">
                <div class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5"
                     style="background: #dcfce7; border: 1px solid #22c55e40;">
                    <div class="h-2 w-2 rounded-full" style="background: #22c55e;"></div>
                    <span class="text-xs font-bold" style="color: #166534;">Sin problema</span>
                </div>
            </div>
        @endif

        {{-- Crop name (bottom) --}}
        <div class="absolute bottom-0 left-0 right-0 px-6 pb-6">
            <p class="mb-1 text-xs font-bold uppercase tracking-widest" style="color: #6cb33e; letter-spacing: 0.14em;">
                Cultivo analizado
            </p>
            <h1 class="text-3xl font-bold leading-tight text-white"
                style="font-family: 'Fraunces', Georgia, serif;">
                {{ $diagnosis->crop }}
            </h1>
            @if ($diagnosis->location)
                <p class="mt-1 flex items-center gap-1.5 text-sm" style="color: rgba(255,255,255,0.7);">
                    <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M7 1C4.8 1 3 2.8 3 5c0 3.5 4 8 4 8s4-4.5 4-8c0-2.2-1.8-4-4-4z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7" cy="5" r="1.5"/>
                    </svg>
                    {{ $diagnosis->location }}
                </p>
            @endif
        </div>
    </div>

    {{-- ── Content card (overlaps hero) ── --}}
    <div class="relative -mt-5 rounded-3xl bg-white px-5 pb-10 pt-6 md:px-8"
         style="box-shadow: 0 -4px 24px rgba(10,22,4,0.12);"
    >

        {{-- Drag handle --}}
        <div class="mx-auto mb-6 h-1 w-10 rounded-full" style="background: #e4ddd1;"></div>

        {{-- ── Pest detected block ── --}}
        @if ($hasProblem)
            <div class="mb-5 flex items-start gap-4 rounded-2xl p-5"
                 style="background: #fff8f0; border: 1px solid #fed7aa; animation: slide-up 0.45s ease-out 0.1s both;">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                     style="background: #ffedd5;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#c2410c" stroke-width="1.8">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="mb-0.5 text-xs font-semibold uppercase tracking-wide" style="color: #c2410c;">
                        Plaga / enfermedad detectada
                    </p>
                    <p class="text-lg font-bold leading-snug" style="color: #1c1a12; font-family: 'Fraunces', Georgia, serif;">
                        {{ $diagnosis->pest_name ?? 'No identificada' }}
                    </p>
                </div>
            </div>
        @else
            <div class="mb-5 flex items-start gap-4 rounded-2xl p-5"
                 style="background: #f0fdf4; border: 1px solid #bbf7d0; animation: slide-up 0.45s ease-out 0.1s both;">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                     style="background: #dcfce7;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="mb-0.5 text-xs font-semibold uppercase tracking-wide" style="color: #16a34a;">
                        Resultado
                    </p>
                    <p class="text-lg font-bold" style="color: #14532d; font-family: 'Fraunces', Georgia, serif;">
                        ¡Cultivo saludable!
                    </p>
                    <p class="mt-1 text-sm" style="color: #166534;">
                        No se detectaron plagas ni enfermedades en la imagen.
                    </p>
                </div>
            </div>
        @endif

        {{-- ── Description ── --}}
        @if ($diagnosis->description)
            <div class="mb-5" style="animation: slide-up 0.45s ease-out 0.2s both;">
                <h2 class="mb-2.5 flex items-center gap-2 text-xs font-bold uppercase tracking-widest"
                    style="color: #8a8070; letter-spacing: 0.12em;">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M2 4h12M2 8h10M2 12h6" stroke-linecap="round"/>
                    </svg>
                    Descripción
                </h2>
                <p class="text-base leading-relaxed" style="color: #3d3a30;">
                    {{ $diagnosis->description }}
                </p>
            </div>
        @endif

        {{-- ── Action sections ── --}}
        @if ($diagnosis->immediate_action)
            <div class="mb-4 overflow-hidden rounded-xl"
                 style="border-left: 4px solid #ef4444; background: #fff1f0; animation: slide-up 0.45s ease-out 0.3s both;">
                <div class="px-5 py-4">
                    <h2 class="mb-1.5 flex items-center gap-2 text-sm font-bold" style="color: #991b1b;">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        Acción inmediata
                    </h2>
                    <p class="text-sm leading-relaxed" style="color: #7f1d1d;">
                        {{ $diagnosis->immediate_action }}
                    </p>
                </div>
            </div>
        @endif

        @if ($diagnosis->preventive_action)
            <div class="mb-5 overflow-hidden rounded-xl"
                 style="border-left: 4px solid #3b82f6; background: #eff6ff; animation: slide-up 0.45s ease-out 0.38s both;">
                <div class="px-5 py-4">
                    <h2 class="mb-1.5 flex items-center gap-2 text-sm font-bold" style="color: #1d4ed8;">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                        </svg>
                        Acción preventiva
                    </h2>
                    <p class="text-sm leading-relaxed" style="color: #1e3a8a;">
                        {{ $diagnosis->preventive_action }}
                    </p>
                </div>
            </div>
        @endif

        {{-- ── Confidence bar ── --}}
        @if ($pct !== null)
            <div class="mb-5" style="animation: slide-up 0.45s ease-out 0.46s both;">
                <div class="mb-2.5 flex items-center justify-between">
                    <h2 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest"
                        style="color: #8a8070; letter-spacing: 0.12em;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                            <circle cx="8" cy="8" r="6"/>
                            <path d="M8 5v3l2 2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Confianza del análisis
                    </h2>
                    <span class="text-sm font-bold tabular-nums" style="color: {{ $barColor }};">
                        {{ $pct }}%
                    </span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full" style="background: #ede8d8;">
                    <div class="h-full rounded-full"
                         style="width: {{ $pct }}%; background: {{ $barColor }}; animation: bar-grow 1.3s cubic-bezier(0.22, 1, 0.36, 1) 0.8s both;">
                    </div>
                </div>
                <p class="mt-1.5 text-xs" style="color: #a09080;">
                    @if ($pct >= 75) Alta confianza en el diagnóstico.
                    @elseif ($pct >= 50) Confianza moderada — se recomienda confirmación en campo.
                    @else Baja confianza — considera tomar otra foto con mejor iluminación.
                    @endif
                </p>
            </div>
        @endif

        {{-- ── Weather section ── --}}
        @if ($diagnosis->temperature !== null || $diagnosis->humidity !== null || $diagnosis->weather_condition)
            <div class="mb-6 rounded-2xl p-5"
                 style="background: #f0f9ff; border: 1px solid #bae6fd; animation: slide-up 0.45s ease-out 0.54s both;">
                <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest"
                    style="color: #0369a1; letter-spacing: 0.12em;">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="color: #0ea5e9;">
                        <path d="M5.5 16.5A4.5 4.5 0 015.5 7.5a6 6 0 1111 2 3.5 3.5 0 01-.5 7h-10.5z"/>
                    </svg>
                    Clima actual en la zona
                </h2>
                <div class="flex flex-wrap gap-x-6 gap-y-3">
                    @if ($diagnosis->temperature !== null)
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                                 style="background: #fff7ed; border: 1px solid #fed7aa;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="#ea580c" stroke-width="1.5">
                                    <path d="M8 1v8.5" stroke-linecap="round"/>
                                    <circle cx="8" cy="11.5" r="2.5" fill="#ea580c" stroke="none"/>
                                    <path d="M5.5 4h-1M5.5 7h-1" stroke-linecap="round" opacity="0.6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs" style="color: #7a7264;">Temperatura</p>
                                <p class="text-sm font-bold" style="color: #1c1a12;">
                                    {{ number_format($diagnosis->temperature, 1) }} °C
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($diagnosis->humidity !== null)
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                                 style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2C8 2 3 8 3 11a5 5 0 0010 0C13 8 8 2 8 2Z" fill="#3b82f6" opacity="0.3" stroke="#2563eb" stroke-width="1.4" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs" style="color: #7a7264;">Humedad</p>
                                <p class="text-sm font-bold" style="color: #1c1a12;">
                                    {{ number_format($diagnosis->humidity, 0) }}%
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($diagnosis->weather_condition)
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                                 style="background: #fefce8; border: 1px solid #fde68a;">
                                <svg width="15" height="15" viewBox="0 0 20 20" fill="#eab308">
                                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 1.78a1 1 0 011.42 1.42l-.7.7a1 1 0 01-1.42-1.42l.7-.7zm1.78 5.22a1 1 0 110 2h-1a1 1 0 110-2h1zm-1.78 5.22l.7.7a1 1 0 01-1.42 1.42l-.7-.7a1 1 0 011.42-1.42zM10 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-5.22-1.78a1 1 0 011.42 1.42l-.7.7a1 1 0 01-1.42-1.42l.7-.7zm-1.78-4.22H2a1 1 0 110-2h1a1 1 0 110 2zm1.78-5.22l-.7-.7a1 1 0 011.42-1.42l.7.7a1 1 0 01-1.42 1.42zM10 6a4 4 0 100 8 4 4 0 000-8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs" style="color: #7a7264;">Cielo</p>
                                <p class="text-sm font-bold" style="color: #1c1a12;">
                                    {{ $diagnosis->weather_condition }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── CTA ── --}}
        <a
            href="{{ route('diagnosis.create') }}"
            class="flex items-center justify-center gap-2.5 rounded-xl px-6 py-4 text-base font-semibold text-white transition-all duration-150 hover:brightness-110 active:scale-[0.985]"
            style="background: linear-gradient(135deg, #2a5c0f 0%, #1e4309 100%); box-shadow: 0 2px 12px rgba(25,45,11,0.35); animation: slide-up 0.45s ease-out 0.62s both;"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5l-7 5 7 5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Nuevo diagnóstico
        </a>

        {{-- Timestamp --}}
        <p class="mt-4 text-center text-xs" style="color: #b0a898;">
            Diagnóstico realizado el {{ $diagnosis->created_at->format('d/m/Y \a \l\a\s H:i') }}
        </p>

    </div>
</div>

@endsection
