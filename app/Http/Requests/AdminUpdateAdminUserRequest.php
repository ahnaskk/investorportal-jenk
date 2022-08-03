<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateAdminUserRequest extends FormRequest
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

        if ($id) {
            return [
            'email'                 => [
                'required',
                'max:255',
            'unique:users,email,'.$id,
            ],
            'name'                  => [
                'required',
          
            ],
            'password'              => [
                'confirmed',
            ],
            'password_confirmation' => [
                '',
            ],
        ];
        } else {
            return [
            'email'                 => [
                'required',
                'max:255',
            ],
            'name'                  => [
                'required',
            ],
            'password'              => [
                'confirmed',
            ],
            'password_confirmation' => [
                '',
            ],
        ];
        }
    }
}
