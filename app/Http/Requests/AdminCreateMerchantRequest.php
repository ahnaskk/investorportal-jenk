<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

//use Illuminate\Foundation\Http\FormRequest;

class AdminCreateMerchantRequest extends FormRequest
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
        $market_place_status = Request::get('marketplace_status');
        $date_funded = Request::get('date_funded');
        $email = Request::get('email');
        $user_id = Request::get('user_id');
        $syndication_fee = Request::get('m_syndication_fee');
        $zip_code = Request::get('zip_code');
        $centrex_advance_id = Request::get('centrex_advance_id');

        return [
            'name'             => [
                'required',
                'max:50',
            ],
            // 'business_en_name' => [
            //     'required',
            //     'max:50',
            // ],
            /*'password'               => [
                'confirmed',
            ],
            'password_confirmation'  => [
                '',
            ],*/
            //'email'            => !empty($email) ? 'unique:users' : '',
           // "id"              => "unique:merchants,id" ,
            'sub_status_id'                  => [
                'required',
                'numeric',
            ],
            'funded'                         => ($market_place_status == 0) ? 'required|between:0,99999999999|regex:/^[\\d\\s,.]*$/' : 'regex:/^[\d\s,.]*$/|nullable|between:0,99999999999|',

            'factor_rate'                    => ($market_place_status == 0) ? 'required|between:1,2|nullable|numeric' : 'nullable|numeric|between:1,2',
             'date_funded'                    => ($market_place_status == 0) ? 'required|date' : (($date_funded != '') ? 'date' : ''),
            'commission'                     => ($market_place_status == 0) ? 'nullable|numeric|required|between:0,50' : 'nullable|numeric|between:0,50',
            'syndication_fee'                => [
                'nullable',
                'numeric',
            ],
            'credit_score'                   => [
                'nullable',
                'numeric',
                'between:350,850',
            ],
            'max_participant_fund'           => [
                'nullable',
                'numeric',
                'between:0,99999999999',
            ],
            'max_participant_fund_per'       => [
                'between:0,100',
            ],
           // 'debit_ratio'                    => 'nullable|numeric|between:0,100',
            //"debit_ratio"       => "nullable|numeric" ,
            'pmnts'            => ($market_place_status == 0) ? 'required|nullable|numeric|between:1,999' : 'nullable|numeric|between:1,999', //*//*works only 5.4
            //"pmnts"            => $request->funded!=$request->max_participant_fund?'required':'',
            //'open_item'        => 'required',
            'lender_id'        => [
                'required',
            ],
            'm_s_prepaid_status'=>($syndication_fee != 0) ? 'required' : '',
            'zip_code' => ($zip_code != null) ? 'regex:/\b\d{5}\b/' : '',
            'centrex_advance_id'            =>  ! empty($centrex_advance_id) ? 'unique:merchants,centrex_advance_id' : '',
          //  'velocity1_per'    => 'between:0,100',
           // 'velocity2_per'    => 'between:0,100',

        ];
    }

    public function messages()
    {
        return [
        'id.unique'            => 'Merchant id (MID) already in the system.',
        'pmnts.required'       => 'Please enter number of payments',
        'commission.required'  => 'Please enter commission',
        'factor_rate.required' => 'Please enter Factor Rate',
        /*'email.unique' => 'Email already taken m8',*/
        ];
    }
}
