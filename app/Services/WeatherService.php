<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherService
{
    private const DEFAULT_LAT = -17.7833;
    private const DEFAULT_LNG = -63.1821;
    private const ENDPOINT    = 'https://api.open-meteo.com/v1/forecast';

    // Municipios y zonas conocidas del departamento de Santa Cruz, Bolivia.
    // Formato: 'keyword_normalizado' => [lat, lng, 'nombre_canónico']
    private const KNOWN_LOCATIONS = [
        'el torno'          => [-17.9333, -63.4500, 'El Torno'],
        'cotoca'            => [-17.7833, -63.0500, 'Cotoca'],
        'la guardia'        => [-17.9167, -63.3333, 'La Guardia'],
        'warnes'            => [-17.5000, -63.1667, 'Warnes'],
        'portachuelo'       => [-17.3500, -63.3833, 'Portachuelo'],
        'montero'           => [-17.3333, -63.2500, 'Montero'],
        'mineros'           => [-17.1500, -63.1000, 'Mineros'],
        'buena vista'       => [-17.4500, -63.6667, 'Buena Vista'],
        'san carlos'        => [-17.4000, -63.7500, 'San Carlos'],
        'yapacani'          => [-17.0000, -64.0167, 'Yapacaní'],
        'yapacan'           => [-17.0000, -64.0167, 'Yapacaní'],
        'san julian'        => [-17.4500, -62.9167, 'San Julián'],
        'cuatro canadas'    => [-17.0000, -62.9333, 'Cuatro Cañadas'],
        'pailon'            => [-18.1167, -62.7333, 'Pailón'],
        'concepcion'        => [-16.1333, -62.0333, 'Concepción'],
        'san ignacio'       => [-16.3667, -60.9667, 'San Ignacio de Velasco'],
        'san jose'          => [-17.8500, -60.7500, 'San José de Chiquitos'],
        'robore'            => [-18.3333, -59.7500, 'Roboré'],
        'puerto suarez'     => [-18.9500, -57.7833, 'Puerto Suárez'],
        'puerto quijarro'   => [-19.0000, -57.7833, 'Puerto Quijarro'],
        'camiri'            => [-20.0333, -63.5167, 'Camiri'],
        'vallegrande'       => [-18.4833, -64.1000, 'Vallegrande'],
        'samaipata'         => [-18.1833, -63.8667, 'Samaipata'],
        'mairana'           => [-18.1167, -63.9500, 'Mairana'],
        'comarapa'          => [-17.9167, -64.5333, 'Comarapa'],
        'santa cruz'        => [-17.7833, -63.1821, 'Santa Cruz de la Sierra'],
    ];

    /**
     * @return array{temperature: ?float, humidity: ?float, weather_condition: ?string, resolved_location: ?string}|null
     */
    public function getConditions(?string $location = null): ?array
    {
        [$lat, $lng, $resolvedLocation] = $this->resolveCoordinates($location);

        try {
            $response = Http::timeout(8)->get(self::ENDPOINT, [
                'latitude'  => $lat,
                'longitude' => $lng,
                'current'   => 'temperature_2m,relative_humidity_2m,weather_code',
                'timezone'  => 'auto',
            ]);

            if ($response->failed()) {
                return null;
            }

            $current = $response->json('current');
            if (! is_array($current)) {
                return null;
            }

            return [
                'temperature'       => isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : null,
                'humidity'          => isset($current['relative_humidity_2m']) ? (float) $current['relative_humidity_2m'] : null,
                'weather_condition' => $this->describeWeatherCode($current['weather_code'] ?? null),
                'resolved_location' => $resolvedLocation,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{float, float, ?string} [lat, lng, display_name|null] */
    private function resolveCoordinates(?string $location): array
    {
        if (! $location || trim($location) === '') {
            return [self::DEFAULT_LAT, self::DEFAULT_LNG, null];
        }

        $normalized = mb_strtolower($location);
        // Normalizar caracteres con tilde básicos
        $normalized = strtr($normalized, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n',
        ]);

        foreach (self::KNOWN_LOCATIONS as $keyword => [$lat, $lng, $name]) {
            if (str_contains($normalized, $keyword)) {
                return [$lat, $lng, $name];
            }
        }

        return [self::DEFAULT_LAT, self::DEFAULT_LNG, null];
    }

    private function describeWeatherCode(mixed $code): ?string
    {
        if (! is_numeric($code)) {
            return null;
        }

        return match ((int) $code) {
            0           => 'Despejado',
            1, 2, 3     => 'Parcialmente nublado',
            45, 48      => 'Niebla',
            51, 53, 55  => 'Llovizna',
            56, 57      => 'Llovizna helada',
            61, 63, 65  => 'Lluvia',
            66, 67      => 'Lluvia helada',
            71, 73, 75, 77 => 'Nieve',
            80, 81, 82  => 'Chubascos',
            85, 86      => 'Chubascos de nieve',
            95          => 'Tormenta eléctrica',
            96, 99      => 'Tormenta con granizo',
            default     => 'Condición desconocida',
        };
    }
}
