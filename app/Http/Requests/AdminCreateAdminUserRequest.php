<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\User;
use Illuminate\Http\Request;

class AdminCreateAdminUserRequest extends FormRequest
{
    private $mail_exist;
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
    public function rules(Request $request)
    {
        $check_mail_exist =0;
        $trashed_mails=User::onlyTrashed()->pluck('email')->toArray();
        $mail=$request->email;
        if (in_array($mail, $trashed_mails)){
            $check_mail_exist =1;
        }
        $this->mail_exist=$check_mail_exist;

        return [
            'email'                 => [
                'required',
                'unique:users',
                'max:255',
            ],
            'name'                  => [
                'required',
            ],
            'password'              => [
                'required',
                'confirmed',
            ],
            'password_confirmation' => [
                'required',
                'min:6',
            ],
        ];
    }


    public function messages()
    {
        if($this->mail_exist ==1)
        {
            return [
                'email.unique' => "Once already used this email... Please contact the administrator!",
            ];
        }
        else{
            return [
                'email.unique' => "The email has already been taken.",
            ];
        }
    }
}
