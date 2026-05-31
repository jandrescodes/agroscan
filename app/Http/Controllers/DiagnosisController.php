<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosisFormRequest;
use App\Models\Diagnosis;
use App\Services\GeminiService;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
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
        $diagnoses = Diagnosis::latest()->paginate(10);

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

        // 2. Análisis con Gemini (obligatorio). Si falla, limpiamos y redirigimos.
        try {
            $result = $this->gemini->analyze(
                Storage::disk('public')->path($imagePath),
                $request->string('crop')->value(),
            );
        } catch (RuntimeException $e) {
            Storage::disk('public')->delete($imagePath);
            Log::warning('Fallo de diagnóstico Gemini', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'No pudimos analizar la imagen en este momento. Intenta nuevamente.');
        }

        // 3. Clima (enriquecimiento, nunca bloquea).
        $weather = $this->weather->getConditions();

        // 4. Persistir.
        $diagnosis = Diagnosis::create([
            'image_path' => $imagePath,
            'crop' => $request->string('crop')->value(),
            'location' => $request->string('location')->value() ?: null,
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
}
