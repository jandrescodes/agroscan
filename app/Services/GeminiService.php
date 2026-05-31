<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use RuntimeException;

class GeminiService
{
    private const VALID_RISK_LEVELS = ['low', 'medium', 'high'];
    private const SCOPES = ['https://www.googleapis.com/auth/cloud-platform'];

    /**
     * @return array{
     *     has_problem: bool, pest_name: ?string, risk_level: ?string,
     *     description: ?string, immediate_action: ?string,
     *     preventive_action: ?string, confidence: ?float
     * }
     *
     * @throws RuntimeException
     */
    public function analyze(string $imagePath, string $crop, ?array $weather = null): array
    {
        if (! is_readable($imagePath)) {
            throw new RuntimeException('No se pudo leer la imagen para el análisis.');
        }

        $projectId = (string) config('gemini.project_id');
        $model = (string) config('gemini.model');
        $location = (string) config('gemini.location');

        if ($projectId === '') {
            throw new RuntimeException('El proyecto de Google Cloud no está configurado.');
        }

        $accessToken = $this->getAccessToken();

        $base64 = base64_encode((string) file_get_contents($imagePath));
        $mime = mime_content_type($imagePath) ?: 'image/jpeg';

        $url = sprintf(
            'https://aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
            $projectId,
            $location,
            $model,
        );

        $body = json_encode([
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $this->buildPrompt($crop, $weather)],
                    ['inline_data' => ['mime_type' => $mime, 'data' => $base64]],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ]);

        [$httpCode, $responseBody] = $this->curlPost($url, $accessToken, (string) $body);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException(sprintf(
                'El servicio de diagnóstico respondió con un error. HTTP %d: %s',
                $httpCode,
                $responseBody,
            ));
        }

        $json = json_decode($responseBody, true);
        $text = data_get($json, 'candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('El servicio de diagnóstico devolvió una respuesta vacía.');
        }

        return $this->parseAndValidate($text);
    }

    /** @throws RuntimeException */
    public function consultarSobreDiagnostico(array $diagnostico, string $pregunta): string
    {
        $projectId = (string) config('gemini.project_id');
        $model     = (string) config('gemini.model');
        $location  = (string) config('gemini.location');

        if ($projectId === '') {
            throw new RuntimeException('El proyecto de Google Cloud no está configurado.');
        }

        $accessToken = $this->getAccessToken();

        $url = sprintf(
            'https://aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
            $projectId,
            $location,
            $model,
        );

        $body = json_encode([
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $this->buildConsultaPrompt($diagnostico, $pregunta)],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.4,
            ],
        ]);

        [$httpCode, $responseBody] = $this->curlPost($url, $accessToken, (string) $body);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException(sprintf(
                'El servicio de consultas respondió con un error. HTTP %d: %s',
                $httpCode,
                $responseBody,
            ));
        }

        $json = json_decode($responseBody, true);
        $text = data_get($json, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('El servicio de consultas devolvió una respuesta vacía.');
        }

        return trim($text);
    }

    private function buildConsultaPrompt(array $diagnostico, string $pregunta): string
    {
        $crop   = $diagnostico['crop'] ?? 'desconocido';
        $plaga  = ! empty($diagnostico['has_problem'])
            ? ($diagnostico['pest_name'] ?? 'plaga/enfermedad no identificada')
            : 'sin plaga detectada (cultivo sano)';
        $riesgo = match ($diagnostico['risk_level'] ?? null) {
            'high'   => 'alto',
            'medium' => 'medio',
            'low'    => 'bajo',
            default  => 'no aplica',
        };
        $descripcion      = $diagnostico['description']       ?? 'sin descripción';
        $accionInmediata  = $diagnostico['immediate_action']  ?? 'sin acción inmediata registrada';
        $accionPreventiva = $diagnostico['preventive_action'] ?? 'sin acción preventiva registrada';

        $weatherLines = [];
        if (! empty($diagnostico['location'])) {
            $weatherLines[] = '- Zona del cultivo: ' . $diagnostico['location'];
        }
        if (isset($diagnostico['temperature'])) {
            $weatherLines[] = '- Temperatura: ' . number_format((float) $diagnostico['temperature'], 1) . ' °C';
        }
        if (isset($diagnostico['humidity'])) {
            $weatherLines[] = '- Humedad relativa: ' . (int) $diagnostico['humidity'] . '%';
        }
        if (! empty($diagnostico['weather_condition'])) {
            $weatherLines[] = '- Condición del cielo: ' . $diagnostico['weather_condition'];
        }
        $weatherBlock = $weatherLines
            ? "\n        CONDICIONES CLIMÁTICAS AL MOMENTO DEL DIAGNÓSTICO:\n        " . implode("\n        ", $weatherLines) . "\n"
            : '';

        return <<<PROMPT
        Eres un ingeniero agrónomo experto en sanidad vegetal de la región de Santa Cruz de la Sierra, Bolivia.
        Un agricultor ya recibió el siguiente diagnóstico sobre su cultivo y ahora tiene una consulta de seguimiento.

        CONTEXTO DEL DIAGNÓSTICO:
        - Cultivo: {$crop}
        - Plaga/enfermedad detectada: {$plaga}
        - Nivel de riesgo: {$riesgo}
        - Descripción: {$descripcion}
        - Acción inmediata recomendada: {$accionInmediata}
        - Acción preventiva recomendada: {$accionPreventiva}
        {$weatherBlock}
        CONSULTA DEL AGRICULTOR:
        "{$pregunta}"

        Instrucciones para tu respuesta:
        - Responde en español, de forma clara, breve y práctica (máximo 3 párrafos cortos).
        - Usa un tono cercano y profesional, apropiado para un agricultor.
        - Basa tu respuesta en el contexto del diagnóstico anterior.
        - Si la consulta no tiene relación con el cultivo o la sanidad vegetal, indícalo con amabilidad y reorienta hacia el diagnóstico.
        - No inventes datos que no puedas sustentar. No uses formato markdown ni listas con asteriscos.
        PROMPT;
    }

    /** @throws RuntimeException */
    private function getAccessToken(): string
    {
        try {
            $credentials = ApplicationDefaultCredentials::getCredentials(self::SCOPES);
            $token = $credentials->fetchAuthToken();

            if (! isset($token['access_token']) || ! is_string($token['access_token'])) {
                throw new RuntimeException('No se pudo obtener el token de acceso de Google Cloud.');
            }

            return $token['access_token'];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException('Error al autenticarse con Google Cloud: ' . $e->getMessage());
        }
    }

    /** @throws RuntimeException */
    private function parseAndValidate(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?|```$/m', '', $clean) ?? $clean;

        $data = json_decode(trim($clean), true);
        if (! is_array($data) || ! array_key_exists('has_problem', $data)) {
            throw new RuntimeException('El diagnóstico recibido no tiene el formato esperado.');
        }

        $hasProblem = filter_var($data['has_problem'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($hasProblem === null) {
            throw new RuntimeException('El diagnóstico recibido es inconsistente.');
        }

        $riskLevel = $data['risk_level'] ?? null;
        if ($riskLevel !== null && ! in_array($riskLevel, self::VALID_RISK_LEVELS, true)) {
            $riskLevel = null;
        }

        $confidence = isset($data['confidence']) && is_numeric($data['confidence'])
            ? max(0.0, min(1.0, (float) $data['confidence']))
            : null;

        return [
            'has_problem' => $hasProblem,
            'pest_name' => $hasProblem ? $this->nullableString($data['pest_name'] ?? null) : null,
            'risk_level' => $hasProblem ? $riskLevel : null,
            'description' => $this->nullableString($data['description'] ?? null),
            'immediate_action' => $hasProblem ? $this->nullableString($data['immediate_action'] ?? null) : null,
            'preventive_action' => $this->nullableString($data['preventive_action'] ?? null),
            'confidence' => $confidence,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /** @return array{int, string} [httpCode, body] */
    private function curlPost(string $url, string $token, string $body): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('No se pudo inicializar la conexión con el servicio de diagnóstico.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => (int) config('gemini.timeout', 60),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Expect:',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        unset($ch);

        if ($response === false) {
            throw new RuntimeException('No se pudo conectar con el servicio de diagnóstico: ' . $error);
        }

        return [$httpCode, (string) $response];
    }

    private function buildPrompt(string $crop, ?array $weather = null): string
    {
        $weatherBlock = '';
        if ($weather !== null) {
            $parts = [];
            if (isset($weather['temperature'])) {
                $parts[] = '- Temperatura: ' . number_format((float) $weather['temperature'], 1) . ' °C';
            }
            if (isset($weather['humidity'])) {
                $parts[] = '- Humedad relativa: ' . (int) $weather['humidity'] . '%';
            }
            if (isset($weather['weather_condition'])) {
                $parts[] = '- Condición del cielo: ' . $weather['weather_condition'];
            }
            if ($parts) {
                $locationLine = isset($weather['resolved_location'])
                    ? "\n        - Zona específica: " . $weather['resolved_location']
                    : '';
                $weatherBlock = "\n        CONDICIONES CLIMÁTICAS ACTUALES EN SANTA CRUZ, BOLIVIA:{$locationLine}\n        " . implode("\n        ", $parts) . "\n\n        Ten en cuenta estas condiciones al evaluar el riesgo: alta humedad (>80%) favorece enfermedades fúngicas y bacterianas; altas temperaturas (>32 °C) aceleran el ciclo de insectos plaga; las lluvias recientes facilitan la dispersión de patógenos. Menciona el impacto climático en las acciones recomendadas cuando sea relevante.\n";
            }
        }

        return <<<PROMPT
        Eres un ingeniero agrónomo experto en sanidad vegetal de la región de Santa Cruz de la Sierra, Bolivia.
        Analiza la imagen de la planta del cultivo de "{$crop}" y determina si presenta una plaga o enfermedad.
        {$weatherBlock}
        Concéntrate en estas plagas/enfermedades objetivo de la región:
        - Gusano cogollero (maíz, sorgo)
        - Nematodos (soya, hortalizas)
        - Bacteriosis (arroz, tomate)
        - Monilia (cacao)
        - Roya (soya, café, trigo)

        Si detectas otra plaga relevante, repórtala igualmente.

        Responde ÚNICAMENTE con un objeto JSON válido, sin texto adicional, sin explicaciones y sin markdown.
        El JSON debe tener EXACTAMENTE esta estructura:
        {
          "has_problem": true,
          "pest_name": "Gusano cogollero",
          "risk_level": "high",
          "description": "Descripción breve del problema observado, en español.",
          "immediate_action": "Acción inmediata recomendada, en español.",
          "preventive_action": "Acción preventiva recomendada, en español.",
          "confidence": 0.92
        }

        Reglas:
        - "risk_level" debe ser exactamente uno de: "low", "medium", "high".
        - "confidence" es un número decimal entre 0 y 1.
        - Si la planta está sana o no detectas ningún problema:
          "has_problem" = false, "pest_name" = null, "risk_level" = null,
          "immediate_action" = null, "preventive_action" = null, y describe el estado sano en "description".
        - Todos los textos deben estar en español.
        PROMPT;
    }
}
