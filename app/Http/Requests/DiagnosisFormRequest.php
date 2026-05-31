<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiagnosisFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'crop' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.required' => 'Debes subir una imagen del cultivo.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'image.max' => 'La imagen no puede superar los 5 MB.',
            'crop.required' => 'Selecciona el cultivo.',
            'crop.max' => 'El nombre del cultivo es demasiado largo.',
            'location.max' => 'La ubicación es demasiado larga.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'image' => 'imagen',
            'crop' => 'cultivo',
            'location' => 'ubicación',
        ];
    }
}
