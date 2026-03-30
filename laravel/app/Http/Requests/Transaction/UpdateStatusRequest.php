<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:completed,carried_over,cancelled'],
            'actual_date' => ['nullable', 'date', 'required_if:status,completed'],
        ];
    }
}
