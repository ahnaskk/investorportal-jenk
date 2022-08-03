<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminManageBankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = Request::get('edit');

        if ($id) {
            return [
            'name'                 => [
                'required',
                'max:255',
                'unique:bank_account_details,bank_name,'.$id,
            ],
            'acc_number'                  => [
                'required',
                'regex:/^[0-9]+$/',
                'unique:bank_account_details,account_no,'.$id,
            ],
        ];
        } else {
            return [
        'name'                 => [
            'required',
            'max:255',
         'unique:bank_account_details,bank_name',
        ],
        'acc_number'                  => [
            'required',
           'unique:bank_account_details,account_no',
            'regex:/^[0-9]+$/',
        ],
    ];
        }
    }
}
