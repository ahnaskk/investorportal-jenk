<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 10/11/17
 * Time: 4:21 PM.
 */

namespace App\Library\Transformer;

use Carbon\Carbon;
use FFM;

class ParticipantPaymentTransformer extends TransformerAbstract
{
    public function transformModel($model)
    {
        return $model;
    }

    public function general_report($model)
    {
        return [
            'participant'        => /*"<a target='blank' href='".\URL::to('/admin/investors/portfolio/'.$model->user_id)."''>".*/$model->Participant_name/*. "</a>"*/, //$model->Participant_name , //to improve performance showing only id now.
            'id'                 => $model->merchant_id, ///$model->merchant->id ,
            'ledger_date'        => $model->payment_date, //Carbon::createFromFormat('Y-m-d', $model->payment_date)->toFormattedDateString(),
            'debited'            => FFM::dollar($model->payment),
            'syndication_amount' => FFM::dollar($model->participant_share),
            'to_syndicate'       =>  FFM::dollar($model->participant_share - $model->mgmnt_fee - $model->syndication_fee),

            'mgmnt_fee'          => FFM::dollar($model->mgmnt_fee),
            'syndication_fee'    => FFM::dollar($model->syndication_fee),
            'rcode'              => isset($model->code) ? $model->code : '',
        ];
    }

    public function detailsCSVExport($payment)
    {
        return [
            'Merchant Name'           => $payment->Merchant_name,
            'Funded Date'             => Carbon::createFromFormat('Y-m-d', $payment->payment_date)->toFormattedDateString(),
            'Payment'                 => FFM::dollar($payment->payment),
            'Participant Share'       => FFM::dollar($payment->participant_share),
            'Mgmt Fee'                => FFM::dollar($payment->mgmnt_fee),
            'To Participant'          => FFM::dollar($payment->final_participant_share),
            'Ledger_TRANSACTION_TYPE' => isset($payment->transactionType->name) ? $payment->transactionType->name : '',

        ];
    }

    // public function general_report_export($model)
    // {
    //     return [

    //         'merchant'           => $model->Merchant_name,
    //         'id'                 => $model->merchant->id,
    //         'ledger_date'        => Carbon::createFromFormat('Y-m-d', $model->payment_date)->toFormattedDateString(),
    //         'debited'            => FFM::dollar($model->payment),
    //         'syndication_amount' => FFM::dollar($model->participant_share),
    //         'mgmnt_fee'          => FFM::dollar($model->mgmnt_fee),
    //         'syndication_fee'    => FFM::dollar($model->syndication_fee),
    //         'to_syndicate'       => FFM::dollar($model->final_participant_share),
    //     ];
    // }

    public function general_report_export($model)
    {
        return [

        'merchant'           => $model->Merchant_name,
        'id'                 => $model->merchant->id,
        'ledger_date'        => Carbon::createFromFormat('Y-m-d', $model->payment_date)->toFormattedDateString(),
        'debited'            => FFM::dollar($model->total_payment),
        'syndication_amount' => FFM::dollar($model->total_participant_share),
        'mgmnt_fee'          => FFM::dollar($model->total_mgmnt_fee),
        'syndication_fee'    => FFM::dollar($model->total_syndication_fee),
        'to_syndicate'       => FFM::dollar($model->total_final_participant_share),
        ];
    }

    public function general_report_export_admin($model)
    {
        return [
            'merchant'           => '',
            'participant'        => '',
            'id'                 => $model->merchant->id,
            'ledger_date'        => Carbon::createFromFormat('Y-m-d', $model->payment_date)->toFormattedDateString(),
            'debited'            => FFM::dollar($model->payment),
            'syndication_amount' => FFM::dollar($model->participant_share),
            'mgmnt_fee'          => FFM::dollar($model->mgmnt_fee),
            'syndication_fee'    => FFM::dollar($model->syndication_fee),
            'to_syndicate'       => FFM::dollar($model->final_participant_share),
        ];
    }
}
