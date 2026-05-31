@extends('layouts.app')

@section('title', 'Historial de diagnósticos')

@section('content')

<div class="mx-auto max-w-2xl px-4 pb-12 pt-8">

    {{-- Encabezado --}}
    <div class="mb-7" style="animation: slide-up 0.4s ease-out both;">
        <p class="mb-1 text-xs font-bold uppercase tracking-widest" style="color: #6cb33e; letter-spacing: 0.14em;">
            Tus análisis
        </p>
        <h1 class="text-3xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif; color: #192d0b;">
            Historial
        </h1>
    </div>

    @if ($diagnoses->isEmpty())
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 text-center"
             style="border: 1px solid #e4ddd1;">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl" style="background: #eef7e4;">
                <svg width="32" height="32" viewBox="0 0 34 34" fill="none">
                    <path d="M6 28C6 28 10 14 26 8C26 8 28 23 17 27C12 29 7.5 28 6 28Z" fill="#90c84a"/>
                    <path d="M6 28C11 21 16 15.5 26 8" stroke="#4e8a1e" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <p class="mb-1 text-lg font-semibold" style="font-family: 'Fraunces', Georgia, serif; color: #192d0b;">
                Sin diagnósticos aún
            </p>
            <p class="mb-6 text-sm" style="color: #7a7264;">
                Sube una foto de tu cultivo para comenzar.
            </p>
            <a href="{{ route('diagnosis.create') }}"
               class="rounded-xl px-6 py-3 text-sm font-semibold text-white transition-all hover:brightness-110"
               style="background: linear-gradient(135deg, #2a5c0f 0%, #1e4309 100%);">
                Nuevo diagnóstico
            </a>
        </div>

    @else
        {{-- Lista --}}
        <div class="space-y-3" style="animation: slide-up 0.45s ease-out 0.05s both;">
            @foreach ($diagnoses as $diagnosis)
                @php
                    $riskConfig = [
                        'high'   => ['label' => 'Riesgo Alto',  'bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#ef4444'],
                        'medium' => ['label' => 'Riesgo Medio', 'bg' => '#fef3c7', 'text' => '#92400e', 'dot' => '#f59e0b'],
                        'low'    => ['label' => 'Riesgo Bajo',  'bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#22c55e'],
                    ];
                    $rc = $diagnosis->has_problem
                        ? ($riskConfig[$diagnosis->risk_level] ?? $riskConfig['low'])
                        : null;
                @endphp

                <a href="{{ route('diagnosis.show', $diagnosis) }}"
                   class="flex items-center gap-4 rounded-2xl bg-white px-4 py-4 transition-all hover:shadow-md"
                   style="border: 1px solid #e4ddd1;">

                    {{-- Miniatura --}}
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl" style="background: #e8e2d4;">
                        <img
                            src="{{ asset('storage/' . $diagnosis->image_path) }}"
                            alt="Cultivo de {{ $diagnosis->crop }}"
                            class="h-full w-full object-cover"
                        >
                    </div>

                    {{-- Info --}}
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex items-center gap-2">
                            <span class="text-base font-bold leading-none" style="color: #192d0b; font-family: 'Fraunces', Georgia, serif;">
                                {{ $diagnosis->crop }}
                            </span>

                            {{-- Badge riesgo --}}
                            @if ($rc)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                      style="background: {{ $rc['bg'] }}; color: {{ $rc['text'] }};">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $rc['dot'] }};"></span>
                                    {{ $rc['label'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                      style="background: #dcfce7; color: #166534;">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: #22c55e;"></span>
                                    Saludable
                                </span>
                            @endif
                        </div>

                        <p class="truncate text-sm" style="color: #7a7264;">
                            {{ $diagnosis->has_problem ? ($diagnosis->pest_name ?? 'Plaga no identificada') : 'Sin plagas detectadas' }}
                        </p>
                    </div>

                    {{-- Fecha + flecha --}}
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span class="text-xs tabular-nums" style="color: #a09080;">
                            {{ $diagnosis->created_at->format('d/m/Y') }}
                        </span>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#c6bcad" stroke-width="1.5">
                            <path d="M6 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if ($diagnoses->hasPages())
            <div class="mt-6" style="animation: slide-up 0.45s ease-out 0.1s both;">
                {{ $diagnoses->links() }}
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-6 text-center" style="animation: slide-up 0.45s ease-out 0.15s both;">
            <a href="{{ route('diagnosis.create') }}"
               class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold text-white transition-all hover:brightness-110"
               style="background: linear-gradient(135deg, #2a5c0f 0%, #1e4309 100%); box-shadow: 0 2px 12px rgba(25,45,11,0.3);">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 4v12M4 10h12" stroke-linecap="round"/>
                </svg>
                Nuevo diagnóstico
            </a>
        </div>
    @endif

</div>

@endsection
