<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class AdminBills extends FormRequest
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
            'amount' => ['required', 'regex:/^[\d\s,-.]*$/'],

              ];
        } else {
            return [
            'amount' => ['required'],
            'investor_id' => ['required'],
            'date' => ['required'],
            'account_no' => ['required'],

              ];
        }
    }
}
