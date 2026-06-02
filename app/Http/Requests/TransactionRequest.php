<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
        $categoryRule = [
            'sometimes',
            Rule::exists('categories', 'id')->where(function ($query){
                return $query->whereNull('user_id')->orWhere('user_id', $this->user()->id());
            })
        ];


        if ($this->routeIs('transaction.update')) {
            return [
                'id' => ['required', 'exists:transactions,id'],
                'category_id' => $categoryRule,
                'type' => ['required', 'in:income,expense'],
                'amount' => ['required', 'decimal:0,999,999,999,999'],
                'note' => ['sometimes', 'string'],
            ];
        }

        return [
            'category_id' => $categoryRule,
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'decimal:0,999,999,999,999'],
            'note' => ['sometimes', 'string'],
            'transaction_date' => ['required', 'date']
        ];
    }
}
