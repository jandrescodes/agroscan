<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosis extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'image_path',
        'crop',
        'location',
        'has_problem',
        'pest_name',
        'risk_level',
        'description',
        'immediate_action',
        'preventive_action',
        'confidence',
        'temperature',
        'humidity',
        'weather_condition',
    ];

    /** @var array<string, string> */
    public const RISK_LABELS = [
        'low' => 'Bajo',
        'medium' => 'Medio',
        'high' => 'Alto',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'has_problem' => 'boolean',
            'confidence' => 'float',
            'temperature' => 'float',
            'humidity' => 'float',
        ];
    }

    /**
     * Etiqueta en español del nivel de riesgo para las vistas.
     * Uso en Blade: {{ $diagnosis->risk_label }}
     */
    public function getRiskLabelAttribute(): ?string
    {
        if ($this->risk_level === null) {
            return null;
        }

        return self::RISK_LABELS[$this->risk_level] ?? $this->risk_level;
    }

    /** Uso: Diagnosis::ofRiskLevel('high')->get(); */
    public function scopeOfRiskLevel(Builder $query, string $level): Builder
    {
        return $query->where('risk_level', $level);
    }

    /** Uso: Diagnosis::withProblem()->get(); */
    public function scopeWithProblem(Builder $query): Builder
    {
        return $query->where('has_problem', true);
    }
}
