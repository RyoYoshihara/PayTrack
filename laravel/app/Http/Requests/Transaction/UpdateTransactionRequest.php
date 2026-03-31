<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'required', 'integer', 'min:1'],
            'scheduled_date' => ['sometimes', 'required', 'date'],
            'bank_account_id' => ['sometimes', 'nullable', Rule::exists('bank_accounts', 'id')->where('user_id', $this->user()->id)],
            'memo' => ['nullable', 'string'],
        ];
    }
}
