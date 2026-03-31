<?php

namespace App\Http\Requests\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists('bank_accounts', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}
