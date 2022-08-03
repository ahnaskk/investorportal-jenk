<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Guard $guard)
    {
        return ($guard->user()) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:255',
                'unique:bank_account_details,bank_name',
            ],
            'acc_number' => [
                'required',
                'unique:bank_account_details,account_no',
                'regex:/^[0-9]+$/',
            ],
        ];
    }
}
