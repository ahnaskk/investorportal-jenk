<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateBranchManagerRequest extends FormRequest
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

        return [
            'email'                 => [
                'required',
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
    }
}
