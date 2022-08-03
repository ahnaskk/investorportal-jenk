<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminCreateTemplateRequest extends FormRequest
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
        $id = Request::get('id');

        return [
            'title'               => [
                'required',
                'max:255',
            ],
            'type'                  => [
                'required',
            ],
             'subject'                  => [
                'required',
            ],
            'template'  =>  [
                'required',
            ],
            //  'temp_code'              => [
            //     'required',
            //     'unique:template,temp_code,'.$id,
            // ],
        ];
    }

    public function messages()
    {
        return [
            'temp_code.required'    => 'The Template code field is required ',
            'template.required'     =>  'The template body field is required',
        ];
    }
}
