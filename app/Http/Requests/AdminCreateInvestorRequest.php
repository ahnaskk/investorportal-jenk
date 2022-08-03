<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminCreateInvestorRequest extends FormRequest
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
        $syndication_fee = Request::get('global_syndication');
        $mail = Request::get('email');
        $password = Request::get('password');
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            return [
            'email'                  => ($mail != '') ? 'unique:users' :'',
            'name'                  => [
                'required',
            ],
            'password'                  => ($password != '') ? 'confirmed|min:6' :'',
            'password_confirmation' => [
            ],
            's_prepaid_status'=>($syndication_fee != 0) ? 'required' : '',

            ];
        } else {
            return [
            'email'                  => ($mail != '') ? 'unique:users' :'',
            'name'                   => [
                'required',
            ],
            'password'                  => ($password != '') ? 'confirmed|min:6' :'',
            'password_confirmation'  => [
            ],
            // 'notification_recurence' => [
            //     'required',
            // ],
            's_prepaid_status'=>($syndication_fee != 0) ? 'required' : '',

            ];
        }
    }

    public function messages()
    {
        return [

        's_prepaid_status.required'       => 'Please select prepaid status (Amount/RTR)',

        ];
    }
}
