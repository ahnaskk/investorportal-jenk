<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProfileFundingRequest extends FormRequest
{
    public function rules()
    {
        if ($this->isMethod('post')) {
            return [
                'name'       => ['required', 'max:55'],
                'cell_phone' => ['required', 'string', 'min:6'], ];
        }

        return [];
    }
}
