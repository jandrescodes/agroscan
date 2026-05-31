@extends('layouts.app')

@section('title', 'Resultado del diagnóstico')

@php
    $riskStyles = [
        'low'    => 'bg-green-100 text-green-800 ring-green-600/20',
        'medium' => 'bg-amber-100 text-amber-800 ring-amber-600/20',
        'high'   => 'bg-red-100 text-red-800 ring-red-600/20',
    ];
    $riskBadge = $riskStyles[$diagnosis->risk_level] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
@endphp

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

        {{-- Imagen --}}
        <img
            src="{{ Storage::disk('public')->url($diagnosis->image_path) }}"
            alt="Cultivo de {{ $diagnosis->crop }}"
            class="max-h-80 w-full bg-gray-100 object-contain"
        >

        <div class="space-y-6 p-6">

            {{-- Encabezado --}}
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Cultivo</p>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $diagnosis->crop }}</h1>
                    @if ($diagnosis->location)
                        <p class="mt-1 text-sm text-gray-500">{{ $diagnosis->location }}</p>
                    @endif
                </div>

                @if ($diagnosis->has_problem && $diagnosis->risk_label)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $riskBadge }}">
                        Riesgo {{ $diagnosis->risk_label }}
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 ring-1 ring-inset ring-green-600/20">
                        Sin problema
                    </span>
                @endif
            </div>

            {{-- Plaga --}}
            @if ($diagnosis->has_problem)
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-500">Plaga / enfermedad detectada</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $diagnosis->pest_name ?? 'No identificada' }}</p>
                </div>
            @else
                <div class="rounded-lg bg-green-50 p-4 text-green-800">
                    No se detectaron plagas ni enfermedades en la imagen.
                </div>
            @endif

            {{-- Descripción --}}
            @if ($diagnosis->description)
                <div>
                    <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-gray-500">Descripción</h2>
                    <p class="text-gray-700">{{ $diagnosis->description }}</p>
                </div>
            @endif

            {{-- Acciones --}}
            @if ($diagnosis->immediate_action)
                <div class="rounded-lg border-l-4 border-red-400 bg-red-50 p-4">
                    <h2 class="mb-1 text-sm font-semibold text-red-800">Acción inmediata</h2>
                    <p class="text-red-700">{{ $diagnosis->immediate_action }}</p>
                </div>
            @endif

            @if ($diagnosis->preventive_action)
                <div class="rounded-lg border-l-4 border-blue-400 bg-blue-50 p-4">
                    <h2 class="mb-1 text-sm font-semibold text-blue-800">Acción preventiva</h2>
                    <p class="text-blue-700">{{ $diagnosis->preventive_action }}</p>
                </div>
            @endif

            {{-- Confianza --}}
            @if ($diagnosis->confidence !== null)
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">Confianza del análisis</span>
                        <span class="text-gray-600">{{ number_format($diagnosis->confidence * 100, 0) }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                        <div
                            class="h-full rounded-full bg-green-500"
                            style="width: {{ min(100, max(0, $diagnosis->confidence * 100)) }}%"
                        ></div>
                    </div>
                </div>
            @endif

            {{-- Clima --}}
            @if ($diagnosis->temperature !== null || $diagnosis->humidity !== null || $diagnosis->weather_condition)
                <div class="rounded-lg bg-sky-50 p-4">
                    <h2 class="mb-2 text-sm font-semibold text-sky-800">Condiciones climáticas actuales</h2>
                    <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-sky-900">
                        @if ($diagnosis->temperature !== null)
                            <span>Temperatura: <strong>{{ number_format($diagnosis->temperature, 1) }} °C</strong></span>
                        @endif
                        @if ($diagnosis->humidity !== null)
                            <span>Humedad: <strong>{{ number_format($diagnosis->humidity, 0) }} %</strong></span>
                        @endif
                        @if ($diagnosis->weather_condition)
                            <span>Cielo: <strong>{{ $diagnosis->weather_condition }}</strong></span>
                        @endif
                    </div>
                </div>
            @endif

            <a
                href="{{ route('diagnosis.create') }}"
                class="block w-full rounded-lg bg-green-600 px-4 py-3 text-center font-semibold text-white transition hover:bg-green-700"
            >
                Nuevo diagnóstico
            </a>
        </div>
    </div>
</div>
@endsection
