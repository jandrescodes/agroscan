<?php

namespace Tests\Unit;

use App\Models\Diagnosis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosisTest extends TestCase
{
    use RefreshDatabase;

    private function makeDiagnosis(array $overrides = []): Diagnosis
    {
        return Diagnosis::create(array_merge([
            'image_path'        => 'diagnoses/test.jpg',
            'crop'              => 'maíz',
            'location'          => 'Santa Cruz',
            'has_problem'       => 1,
            'pest_name'         => 'Gusano cogollero',
            'risk_level'        => 'high',
            'description'       => 'Daño en cogollo.',
            'immediate_action'  => 'Aplicar control.',
            'preventive_action' => 'Monitoreo semanal.',
            'confidence'        => 0.923,
            'temperature'       => 28.5,
            'humidity'          => 65.0,
            'weather_condition' => 'Despejado',
        ], $overrides));
    }

    public function test_has_problem_is_cast_to_boolean(): void
    {
        $fresh = $this->makeDiagnosis(['has_problem' => 1])->fresh();

        $this->assertIsBool($fresh->has_problem);
        $this->assertTrue($fresh->has_problem);
    }

    public function test_confidence_is_cast_to_float(): void
    {
        $fresh = $this->makeDiagnosis(['confidence' => 0.923])->fresh();

        $this->assertIsFloat($fresh->confidence);
        $this->assertEqualsWithDelta(0.923, $fresh->confidence, 0.0005);
    }

    public function test_risk_label_returns_spanish_label(): void
    {
        $this->assertSame('Bajo',  $this->makeDiagnosis(['risk_level' => 'low'])->risk_label);
        $this->assertSame('Medio', $this->makeDiagnosis(['risk_level' => 'medium'])->risk_label);
        $this->assertSame('Alto',  $this->makeDiagnosis(['risk_level' => 'high'])->risk_label);
    }

    public function test_risk_label_is_null_when_no_risk(): void
    {
        $diagnosis = $this->makeDiagnosis([
            'has_problem' => false,
            'pest_name'   => null,
            'risk_level'  => null,
        ]);

        $this->assertNull($diagnosis->risk_label);
    }

    public function test_of_risk_level_scope_filters_records(): void
    {
        $this->makeDiagnosis(['risk_level' => 'high']);
        $this->makeDiagnosis(['risk_level' => 'low']);

        $this->assertCount(1, Diagnosis::ofRiskLevel('high')->get());
        $this->assertSame('high', Diagnosis::ofRiskLevel('high')->first()->risk_level);
    }

    public function test_fillable_contains_all_editable_fields(): void
    {
        $diagnosis = new Diagnosis();

        foreach ([
            'image_path', 'crop', 'location', 'has_problem', 'pest_name',
            'risk_level', 'description', 'immediate_action', 'preventive_action',
            'confidence', 'temperature', 'humidity', 'weather_condition',
        ] as $field) {
            $this->assertContains($field, $diagnosis->getFillable(), "Falta '$field' en \$fillable.");
        }
    }

    public function test_soft_deletes_keeps_row(): void
    {
        $diagnosis = $this->makeDiagnosis();
        $id = $diagnosis->id;
        $diagnosis->delete();

        $this->assertSoftDeleted('diagnoses', ['id' => $id]);
        $this->assertNotNull(Diagnosis::withTrashed()->find($id));
    }
}
