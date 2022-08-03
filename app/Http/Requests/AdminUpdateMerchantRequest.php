<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

//use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateMerchantRequest extends FormRequest
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
        $user_id = Request::get('user_id');
        $merchant_id = Request::get('merchant_id');

        $email = Request::get('email');
        $centrex_advance_id = Request::get('centrex_advance_id');
        $syndication_fee = Request::get('m_syndication_fee');
        $zip_code = Request::get('zip_code');

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
            //'email'            =>  ! empty($email) ? 'unique:users,email,'.$user_id : '',
            //"id"              => "unique:merchants,id,".$this->id.",id" ,
            'sub_status_id'    => [
                'required',
                'numeric',
            ],
            //@todo  "funded"           => ($market_place_status==0)?"required|regex:/^[\d\s,]*$/" :'regex:/^[\d\s,]*$/' ,
              'funded'           => ($market_place_status == 0) ? 'required|between:0,99999999999|regex:/^[\\d\\s,.]*$/' : 'regex:/^[\d\s,.]*$/|nullable|between:0,99999999999|',

            'factor_rate'                    => ($market_place_status == 0) ? 'required|between:1,2|nullable|numeric' : 'nullable|numeric|between:1,2',
            'date_funded'                    => ($market_place_status == 0) ? 'required|date' : 'required|date',
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
                '',
            ],
            'm_s_prepaid_status'=>($syndication_fee != 0) ? 'required' : '',
            'zip_code' => ($zip_code != null) ? 'regex:/\b\d{5}\b/' : '',
            'centrex_advance_id'            =>  ! empty($centrex_advance_id) ? 'unique:merchants,centrex_advance_id,'.$merchant_id : '',

           // 'velocity1_per'    => 'between:0,100',
           // 'velocity2_per'    => 'between:0,100',

        ];
    }

    public function messages()
    {
        return [
        'id.required' => 'Merchant id (MID) already in the system.',
        /*'email.unique' => 'Email already taken m8',*/
        ];
    }
}
