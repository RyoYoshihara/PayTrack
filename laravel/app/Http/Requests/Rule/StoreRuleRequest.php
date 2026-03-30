<?php

namespace App\Http\Requests\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:income,expense'],
            'recurrence' => ['required', 'in:once,monthly'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31', 'required_if:recurrence,monthly'],
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'memo' => ['nullable', 'string'],
        ];
    }
}
