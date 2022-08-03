<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminUpdateInvestorRequest extends FormRequest
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
        $id = $this->segment(4);
        $syndication_fee = Request::get('global_syndication');
        $mail = Request::get('email');
        $password = Request::get('password');

        return [
            'email'                  => ($mail != '') ? 'unique:users,email,'.$id :'',
            'name'                   => [
                'required',
            ],
            'password'                  => ($password != '') ? 'confirmed|min:6' :'',
            'password_confirmation'  => [
                '',
            ],//required
            's_prepaid_status'=>($syndication_fee != 0) ? 'required' : '',
        ];
    }

    public function messages()
    {
        return [

        's_prepaid_status.required'       => 'Please select prepaid status (Amount/RTR)',

        ];
    }
}
