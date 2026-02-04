<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'destination_id' => 'required|exists:destinations,id',
            'description' => 'nullable|string',
            'departure_datetime' => 'required|date|after:now',
            'return_datetime' => 'required|date|after:departure_datetime',
        ];

        // Admin can specify traveler_id
        if ($this->user()->is_admin) {
            $rules['traveler_id'] = 'sometimes|exists:travelers,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'traveler_id.exists' => 'O viajante selecionado não existe.',
            'destination_id.required' => 'O destino é obrigatório.',
            'destination_id.exists' => 'O destino selecionado não existe.',
            'departure_datetime.required' => 'A data de ida é obrigatória.',
            'departure_datetime.after' => 'A data de ida deve ser no futuro.',
            'return_datetime.required' => 'A data de retorno é obrigatória.',
            'return_datetime.after' => 'A data de retorno deve ser após a data de ida.',
        ];
    }
}
