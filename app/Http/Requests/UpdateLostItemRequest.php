<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLostItemRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'location' => 'sometimes|required|string|max:255',
            'backstory' => 'nullable|string|max:2000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'status' => 'sometimes|required|in:active,found,expired',
            'lost_date' => 'nullable|date|before_or_equal:today',
        ];
    }
}
