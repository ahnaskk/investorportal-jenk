<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class LenderPayment extends FormRequest
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
        $id = Request::get('pay');
        if ($id) {
            return [
       'payment_date' => ['required'],
       'lenders' => ['required'],
        'companies' => ['required'],
         ];
        } else {
            return [];
        }
    }
}
