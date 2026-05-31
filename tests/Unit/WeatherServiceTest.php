<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    private function fakeWeather(float $temp = 28.5, int $humidity = 72, int $code = 0): void
    {
        Http::fake([
            '*open-meteo.com*' => Http::response([
                'current' => [
                    'temperature_2m'        => $temp,
                    'relative_humidity_2m'  => $humidity,
                    'weather_code'          => $code,
                ],
            ]),
        ]);
    }

    // ── Resolución de coordenadas ──────────────────────────────────────────

    public function test_null_location_returns_santa_cruz_default(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions(null);

        $this->assertNull($result['resolved_location']);
    }

    public function test_empty_location_returns_santa_cruz_default(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('');

        $this->assertNull($result['resolved_location']);
    }

    public function test_known_location_lowercase_resolves(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('montero');

        $this->assertSame('Montero', $result['resolved_location']);
    }

    public function test_known_location_uppercase_resolves(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('MONTERO');

        $this->assertSame('Montero', $result['resolved_location']);
    }

    public function test_known_location_mixed_case_resolves(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('Montero');

        $this->assertSame('Montero', $result['resolved_location']);
    }

    public function test_location_embedded_in_phrase_resolves(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('Comunidad El Torno, Santa Cruz');

        $this->assertSame('El Torno', $result['resolved_location']);
    }

    public function test_location_with_accent_input_resolves(): void
    {
        $this->fakeWeather();
        // usuario escribe "San Julián" con tilde
        $result = (new WeatherService)->getConditions('San Julián');

        $this->assertSame('San Julián', $result['resolved_location']);
    }

    public function test_santa_cruz_keyword_resolves(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('santa cruz');

        $this->assertSame('Santa Cruz de la Sierra', $result['resolved_location']);
    }

    public function test_unknown_location_returns_null_resolved(): void
    {
        $this->fakeWeather();
        $result = (new WeatherService)->getConditions('Finca La Esperanza');

        $this->assertNull($result['resolved_location']);
    }

    public function test_camiri_resolves_different_coordinates(): void
    {
        // Verificamos que Camiri usa coordenadas distintas al default de SCZ
        // capturando la URL de la petición HTTP
        $capturedUrl = null;
        Http::fake([
            '*open-meteo.com*' => function ($request) use (&$capturedUrl) {
                $capturedUrl = $request->url();
                return Http::response([
                    'current' => [
                        'temperature_2m'       => 26.0,
                        'relative_humidity_2m' => 55,
                        'weather_code'         => 1,
                    ],
                ]);
            },
        ]);

        $result = (new WeatherService)->getConditions('camiri');

        $this->assertSame('Camiri', $result['resolved_location']);
        // Camiri lat = -20.0333, distinta al default -17.7833
        $this->assertStringContainsString('-20.0333', $capturedUrl);
    }

    // ── Condiciones climáticas ─────────────────────────────────────────────

    public function test_returns_temperature_humidity_and_condition(): void
    {
        $this->fakeWeather(31.2, 85, 3);
        $result = (new WeatherService)->getConditions();

        $this->assertEqualsWithDelta(31.2, $result['temperature'], 0.01);
        $this->assertEqualsWithDelta(85.0, $result['humidity'], 0.01);
        $this->assertSame('Parcialmente nublado', $result['weather_condition']);
    }

    public function test_returns_null_on_api_failure(): void
    {
        Http::fake(['*open-meteo.com*' => Http::response([], 500)]);

        $result = (new WeatherService)->getConditions();

        $this->assertNull($result);
    }

    public function test_weather_code_despejado(): void
    {
        $this->fakeWeather(code: 0);
        $this->assertSame('Despejado', (new WeatherService)->getConditions()['weather_condition']);
    }

    public function test_weather_code_lluvia(): void
    {
        $this->fakeWeather(code: 63);
        $this->assertSame('Lluvia', (new WeatherService)->getConditions()['weather_condition']);
    }

    public function test_weather_code_tormenta(): void
    {
        $this->fakeWeather(code: 95);
        $this->assertSame('Tormenta eléctrica', (new WeatherService)->getConditions()['weather_condition']);
    }
}
