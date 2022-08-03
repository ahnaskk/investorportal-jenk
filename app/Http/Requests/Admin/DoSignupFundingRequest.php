<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DoSignupFundingRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'       => ['required', 'max:55'],
            'email'      => ['required', 'string', 'email', 'unique:users'],
            'password'   => ['required', 'string'],
            'cell_phone' => ['required', 'string', 'min:6'],
        ];
    }
}
