<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminFirewall extends FormRequest
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
        $flag = Request::get('add');
        if ($flag == 'yes') {
            return [
                'user_id' => ['required', 'exists:users,id'],
                'ip_address' => ['required', 'ip'],
            ];
        } else if ($flag == 'roles') {
            return [
                'user_id' => ['required', 'exists:roles,id'],
                'ip_address' => ['required', 'ip'],
            ];
        } else if ($flag == 'delete_role') {
            return  [
                'role_id' => ['required'],
                'ip_id' => ['required']
            ];
        } else {
            return  [
                'user_id' => ['required', 'exists:users,id'],
                'ip_address' => ['required', 'ip', 'exists:firewalls,ip_address'],
            ];
        }
    }
}
