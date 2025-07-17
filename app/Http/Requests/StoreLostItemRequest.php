<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostItemRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'backstory' => 'nullable|string|max:2000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'fee_paid' => 'required|numeric|min:0|max:999999.99',
            'lost_date' => 'nullable|date|before_or_equal:today',
        ];
    }
}
