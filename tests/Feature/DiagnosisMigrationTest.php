<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DiagnosisMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_diagnoses_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('diagnoses'));
    }

    public function test_diagnoses_table_has_all_expected_columns(): void
    {
        $expected = [
            'id', 'image_path', 'crop', 'location',
            'has_problem', 'pest_name', 'risk_level', 'description',
            'immediate_action', 'preventive_action', 'confidence',
            'temperature', 'humidity', 'weather_condition',
            'created_at', 'updated_at', 'deleted_at',
        ];

        $this->assertTrue(
            Schema::hasColumns('diagnoses', $expected),
            'Faltan una o más columnas esperadas en la tabla diagnoses.'
        );
    }
}
