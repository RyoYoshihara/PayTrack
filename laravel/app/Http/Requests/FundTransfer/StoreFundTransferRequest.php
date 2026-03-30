<?php

namespace App\Http\Requests\FundTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreFundTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'exists:bank_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:bank_accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'scheduled_date' => ['required', 'date'],
            'memo' => ['nullable', 'string'],
        ];
    }
}
