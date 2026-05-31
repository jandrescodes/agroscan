<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherService
{
    // Santa Cruz de la Sierra, Bolivia (coordenadas por defecto).
    private const DEFAULT_LAT = -17.7833;

    private const DEFAULT_LNG = -63.1821;

    private const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Obtiene las condiciones climáticas actuales. Devuelve null en cualquier fallo
     * — el clima es enriquecimiento y nunca interrumpe el flujo de diagnóstico.
     *
     * @return array{temperature: ?float, humidity: ?float, weather_condition: ?string}|null
     */
    public function getConditions(float $lat = self::DEFAULT_LAT, float $lng = self::DEFAULT_LNG): ?array
    {
        try {
            $response = Http::timeout(8)->get(self::ENDPOINT, [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code',
                'timezone' => 'auto',
            ]);

            if ($response->failed()) {
                return null;
            }

            $current = $response->json('current');
            if (! is_array($current)) {
                return null;
            }

            return [
                'temperature' => isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : null,
                'humidity' => isset($current['relative_humidity_2m']) ? (float) $current['relative_humidity_2m'] : null,
                'weather_condition' => $this->describeWeatherCode($current['weather_code'] ?? null),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    private function describeWeatherCode(mixed $code): ?string
    {
        if (! is_numeric($code)) {
            return null;
        }

        return match ((int) $code) {
            0 => 'Despejado',
            1, 2, 3 => 'Parcialmente nublado',
            45, 48 => 'Niebla',
            51, 53, 55 => 'Llovizna',
            56, 57 => 'Llovizna helada',
            61, 63, 65 => 'Lluvia',
            66, 67 => 'Lluvia helada',
            71, 73, 75, 77 => 'Nieve',
            80, 81, 82 => 'Chubascos',
            85, 86 => 'Chubascos de nieve',
            95 => 'Tormenta eléctrica',
            96, 99 => 'Tormenta con granizo',
            default => 'Condición desconocida',
        };
    }
}
