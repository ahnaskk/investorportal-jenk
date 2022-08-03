<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 8/11/17
 * Time: 4:37 PM.
 */

namespace App\Library\Transformer;

use App\ParticipentPayment;
use Carbon\Carbon;
use FFM;
use PayCalc;

class MerchantTransformer extends TransformerAbstract
{
    public function transformModel($merchant)
    {
        $final_participant_share = array_sum(array_column(($merchant->participantPayment)->toArray(), 'participant_share')) - array_sum(array_column(($merchant->participantPayment)->toArray(), 'syndication_fee')) - array_sum(array_column(($merchant->participantPayment)->toArray(), 'mgmnt_fee'));

        $funded = array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'amount'));
        $rtr = $funded * $merchant->factor_rate;

        $ctd = $final_participant_share;

        /*complete*/
        $final_participant_share = array_sum(array_column(($merchant->participantPayment)->toArray(), 'final_participant_share'));
        $funded = array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'amount'));

        $rtr1 = $funded * $merchant->factor_rate;
        if ($rtr1 != 0) {
            $rtr = $rtr1 - ($rtr1 * ($merchant->mgmnt_fee)) / 100;
        } else {
            $rtr = 0;
        }
        /*
        $complete=PayCalc::completedPercent2( $final_participant_share,$rtr);*/
        $complete = PayCalc::completedPercent2($merchant);

        return [
            'Merchant ID'                      => $merchant->id,
            'Merchant Name'                    => $merchant->name,
            'Funded date'                      => Carbon::createFromFormat('Y-m-d', $merchant->date_funded)->toFormattedDateString(),
            'Syndication Funded'               => FFM::dollar(array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'amount'))),
            'CMMSN'                            => FFM::percent($merchant->commission),
            'commission'                       => FFM::dollar(($merchant->commission * array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'amount'))) / 100), //10*400/100
            'RTR'                              => FFM::dollar(array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'rtr'))),
            'Master_Sheet_Factor_Rate'         => FFM::percent($merchant->factor_rate),
            'CTD'                              => FFM::dollar($ctd),
            '% Complete'                       => FFM::percent($complete),
            'Master_Sheet_Status_Details'      => $merchant->payStatus,
        ];
    }

    public function merchant_investors_report($investor)
    {
        return [
            'Investor Id'    => $investor->user_id,
            'Merchant Id'    => $investor->merchant->id,
            'Created At'     => $investor->merchant->created_at,
            'Funded Date'    => \FFM::date($investor->merchant->date_funded),
            'Amount'         => \FFM::dollar($investor->amount),
            'Management Fee' => \FFM::dollar($investor->merchant->mgmnt_fee),
            'Syndicate Fee'  => \FFM::dollar($investor->merchant->syndication_fee),
            'Share  '        => \FFM::percent($investor->share),

        ];
    }

    public function merchant_investment_report($merchant)
    {
        foreach ($merchant->investmentData as $investMent) {
            $data[] = [

                'investor_id'       => $investMent->user_id,
                'funded'            => \FFM::dollar($investMent->amount),
                'commission'        => \FFM::dollar($investMent->commission_amount),
                'share'             => \FFM::percent($investMent->share),
                's_prepaid_status'  => \FFM::dollar($investMent->pre_paid),
                'total'             => \FFM::dollar(($investMent->pre_paid + $investMent->amount + $investMent->commission_amount)),
                'created_at'        => $investMent->created_at,

            ];
        }

        return $data;
    }

    public function default_rate_report_rtr($merchant)
    {
        $ctd_defs = array_sum(array_column($merchant->participantPayment->toArray(), 'final_participant_share'));
        foreach ($merchant->investmentData as $investMent) {
            $ctd_defs = ParticipentPayment::where('merchant_id', $merchant->merchant_id)->where('user_id', $investMent->user_id)->sum('final_participant_share');
            $data[] = [
                    'investor_id' => $investMent->user_id,
                    'amount'      => \FFM::dollar($investMent->invest_rtr - $ctd_defs),
                    'date'        => Carbon::createFromFormat('Y-m-d H:i:s', $investMent->created_at)->format('M j, Y'),
                ];
        }

        return $data;
    }

    public function default_rate_report_investment($merchant)
    {
        $ctd_defs = array_sum(array_column($merchant->participantPayment->toArray(), 'final_participant_share'));
        foreach ($merchant->investmentData as $investMent) {
            $ctd_defs = ParticipentPayment::where('merchant_id', $merchant->merchant_id)->where('user_id', $investMent->user_id)->sum('final_participant_share');
            $data[] = [
                    'investor_id' => $investMent->user_id,
                    'amount'      => \FFM::dollar(($investMent->amount + $investMent->commission_amount + $investMent->pre_paid) - $ctd_defs),
                    'date'        => Carbon::createFromFormat('Y-m-d H:i:s', $investMent->created_at)->format('M j, Y'),
                ];
        }

        return $data;
    }

    public function interest_accured_report($merchant)
    {
        foreach ($merchant->investmentData as $investMent) {
            $data[] = [
                'investor_id'          => $investMent->user_id,
                'amount'               => \FFM::dollar($investMent->amount),
                'commission'           => \FFM::dollar($investMent->commission_amount),
                'prepaid'              => \FFM::dollar($merchant->m_s_prepaid_status ? $investMent->pre_paid : 0),
                'total'                => \FFM::dollar(($investMent->pre_paid + $investMent->amount + $investMent->commission_amount)),
                'interest_rate'        => \FFM::percent($investMent->interest_rate),
                'created_at'           => $investMent->created_at,

            ];
        }

        return $data;
    }
}
