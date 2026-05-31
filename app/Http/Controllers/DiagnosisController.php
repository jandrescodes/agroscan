<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosisFormRequest;
use App\Models\Diagnosis;
use App\Services\GeminiService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class DiagnosisController extends Controller
{
    private const CROPS = ['Maíz', 'Sorgo', 'Soya', 'Arroz', 'Tomate', 'Cacao', 'Café', 'Trigo', 'Hortalizas'];

    public function __construct(
        private readonly GeminiService $gemini,
        private readonly WeatherService $weather,
    ) {}

    public function index(): View
    {
        $diagnoses = Diagnosis::latest()->paginate(4);

        return view('diagnosis.index', ['diagnoses' => $diagnoses]);
    }

    public function create(): View
    {
        return view('diagnosis.create', ['crops' => self::CROPS]);
    }

    public function store(DiagnosisFormRequest $request)
    {
        // 1. Guardar imagen con nombre único en storage/app/public/diagnoses/
        $imagePath = $request->file('image')->store('diagnoses', 'public');

        // 2. Clima (antes de Gemini para enriquecer el prompt).
        $location = $request->string('location')->value() ?: null;
        $weather  = $this->weather->getConditions($location);

        // 3. Análisis con Gemini (obligatorio). Si falla, limpiamos y redirigimos.
        try {
            $result = $this->gemini->analyze(
                Storage::disk('public')->path($imagePath),
                $request->string('crop')->value(),
                $weather,
            );
        } catch (RuntimeException $e) {
            Storage::disk('public')->delete($imagePath);
            Log::warning('Fallo de diagnóstico Gemini', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'No pudimos analizar la imagen en este momento. Intenta nuevamente.');
        }

        // 4. Persistir.
        $diagnosis = Diagnosis::create([
            'image_path' => $imagePath,
            'crop' => $request->string('crop')->value(),
            'location' => $weather['resolved_location'] ?? ($location ? ucwords(mb_strtolower($location)) : null),
            'has_problem' => $result['has_problem'],
            'pest_name' => $result['pest_name'],
            'risk_level' => $result['risk_level'],
            'description' => $result['description'],
            'immediate_action' => $result['immediate_action'],
            'preventive_action' => $result['preventive_action'],
            'confidence' => $result['confidence'],
            'temperature' => $weather['temperature'] ?? null,
            'humidity' => $weather['humidity'] ?? null,
            'weather_condition' => $weather['weather_condition'] ?? null,
        ]);

        return redirect()->route('diagnosis.show', $diagnosis);
    }

    public function show(Diagnosis $diagnosis): View
    {
        return view('diagnosis.show', ['diagnosis' => $diagnosis]);
    }

    public function consulta(Request $request, Diagnosis $diagnosis): JsonResponse
    {
        $validated = $request->validate([
            'pregunta' => ['required', 'string', 'max:500'],
        ], [
            'pregunta.required' => 'Escribe una pregunta para consultar.',
            'pregunta.max'      => 'La pregunta no puede superar los 500 caracteres.',
        ]);

        try {
            $respuesta = $this->gemini->consultarSobreDiagnostico(
                [
                    'crop'              => $diagnosis->crop,
                    'has_problem'       => (bool) $diagnosis->has_problem,
                    'pest_name'         => $diagnosis->pest_name,
                    'risk_level'        => $diagnosis->risk_level,
                    'description'       => $diagnosis->description,
                    'immediate_action'  => $diagnosis->immediate_action,
                    'preventive_action' => $diagnosis->preventive_action,
                    'location'          => $diagnosis->location,
                    'temperature'       => $diagnosis->temperature,
                    'humidity'          => $diagnosis->humidity,
                    'weather_condition' => $diagnosis->weather_condition,
                ],
                $validated['pregunta'],
            );
        } catch (RuntimeException $e) {
            Log::warning('Fallo de consulta de seguimiento Gemini', [
                'diagnosis_id' => $diagnosis->id,
                'message'      => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No pudimos responder tu consulta en este momento. Intenta nuevamente.',
            ], 500);
        }

        return response()->json(['respuesta' => $respuesta]);
    }
}
