<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'scheduled_date' => ['required', 'date'],
            'bank_account_id' => ['required', Rule::exists('bank_accounts', 'id')->where('user_id', $this->user()->id)],
            'memo' => ['nullable', 'string'],
        ];
    }
}
