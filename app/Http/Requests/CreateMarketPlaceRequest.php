<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMarketPlaceRequest extends FormRequest
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
        return [
            'name'                   => [
                'required',
            ],
            // 'business_en_name'       => [
            //     'required',
            // ],
            'id'                     => [
                'required',
                'unique:merchants,id',
            ],
            'funded'                 => [
                'required',
            ],
            'rtr'                    => [
                'required',
            ],
            'commission'             => [
                'required',
            ],
            'pmnts'                  => [
                'required',
            ],
            'max_participant_fund'   => [
                'required',
            ],
            'participant_rtr'        => [
                'required',
            ],
            'mgmnt_fee'              => [
                'required',
            ],
            'syndication_fee'        => [
                'required',
            ],
            'sub_status_id'          => [
                'required',
            ],
            'pmnt_amount'            => [
                'required',
            ],
            'total_payment'          => [
                'required',
            ],
        ];
    }
}
