<?php

namespace App\Http\Requests\FundTransfer;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmFundTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'side' => ['required', 'in:from,to'],
        ];
    }
}
