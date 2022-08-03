<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminSettings extends FormRequest
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
    /*@todo temp

            'rate' => ['required','numeric','between:0,15'],
            'payments' => ['required'],
            'date_start' => ['required','date','before:tomorrow'] */
                 ];
        } else {
            return [];
        }
    }
}
