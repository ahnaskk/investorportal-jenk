<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DynamicReport extends Model
{
    protected $guarded = [];
    protected $connection = 'mongodb';
    protected $collection = 'dynamic_reports';

	public static function merchant_fields()
	{
		$arr = [
			'name' => changeCase('name'),
			'rtr' => changeCase('rtr'),
			'last_rcode' => changeCase('last_rcode'),
			//'substatus_name' => changeCase('substatus_name'),
			'debited' => changeCase('debited'),
			'profit' => changeCase('profit'),
			'actual_participant_share' => 'Total Payments',
			'principal' => changeCase('principal'),
			'mgmnt_fee' => changeCase('mgmnt_fee'),
			'overpayment' => changeCase('overpayment'),
			//'carry_profit' => changeCase('carry_profit'),
			'participant_share' => changeCase('participant_share'),
			'amount' => "Funded Amount",
			'paid_participant_ishare' => changeCase('paid_participant_ishare'),
			'mgmnt_fee_amount' => changeCase('mgmnt_fee_amount'),
			'invest_rtr' => changeCase('invest_rtr'),
			'invested_amount' => changeCase('invested_amount'),
			'net_balance' => changeCase('net_balance'),
			'last_payment_amount' => changeCase('last_payment_amount'),
			'settled_rtr' => changeCase('settled_rtr'),
			'payment_date' => changeCase('payment_date'),
			//'payments' => 'Total Payments',
			'syndicate' => 'Net Amount',
			'date_funded' => changeCase('date_funded'),
		];

		return $arr;
	}

    public static function investor_fields()
    {
        $arr = [
            'name' => changeCase('name'),
            'credit_amount' => changeCase('liquidity'),
            'commission_amount' => changeCase('commission_amount'),
            'total_funded' => changeCase('total_funded'),
            'under_writing_fee' => changeCase('under_writing_fee'),
            'rtr' => changeCase('rtr'),
            'ctd' => changeCase('ctd'),
            'syndication_fee' => changeCase('syndication_fee'),
            'underwriting_fee_earned' => changeCase('underwriting_fee_earned'),
            'origination_fee' => changeCase('origination_fee'),
            'up_sell_commission' => changeCase('up_sell_commission'),
            'management_fee_earned' => 'Management Fee',
            'profit' => 'Profit',
            'principal' => 'Principal',
            'payment_date' => changeCase('Date'),
        ];

        return $arr;
    }

    public static function all_fields()
    {
        $arr = [
            'merchant_id' => changeCase('merchant_id'),
            'crm_id' =>changeCase( 'crm_id'),
            'exact_legal_company_name' => changeCase('exact_legal_company_name'),
            'physical_address' => changeCase('physical_address'),
            'physical_address2' => changeCase('physical_address2'),
            'work_phone' => changeCase('work_phone'),
            'fax' => changeCase('fax'),
            'federal_tax_id' => changeCase('federal_tax_id'),
            'date_business_started' => changeCase('date_business_started'),
            'ownership_length' => changeCase('ownership_length'),
            'website' => changeCase('website'),
            'use_of_proceeds' => changeCase('use_of_proceeds'),
            'lead_source' => changeCase('lead_source'),
            'campaign' => changeCase('campaign'),
            'owner_first_name' => changeCase('owner_first_name'),
            'owner_last_name' => changeCase('owner_last_name'),
            'owner_home' => changeCase('owner_home'),
            'owner_address2' => changeCase('owner_address2'),
            'ownership_percentage' => changeCase('ownership_percentage'),
            'owner_credit_score' => changeCase('owner_credit_score'),
            'owner_email' => changeCase('owner_email'),
            'home_address' => changeCase('home_address'),
            'owner_city' => changeCase('owner_city'),
            'owner_state_id' => changeCase('owner_state_id'),
            'owner_zip' => changeCase('owner_zip'),
            'owner_cell' => changeCase('owner_cell'),
            'owner_cell2' => changeCase('owner_cell2'),
            'ssn' => changeCase('ssn'),
            'dob' => changeCase('dob'),
            'partner_first_name' => changeCase('partner_first_name'),
            'partner_last_name' => changeCase('partner_last_name'),
            'partner_cell2' => changeCase('partner_cell2'),
            'partner_email' => changeCase('partner_email'),
            'partner_address2' => changeCase('partner_address2'),
            'partner_ownership_percentage' => changeCase('partner_ownership_percentage'),
            'partner_credit_score' => changeCase('partner_credit_score'),
            'partner_home_address' => changeCase('partner_home_address'),
            'partner_home_hash' => changeCase('partner_home_hash'),
            'partner_city' => changeCase('partner_city'),
            'partner_state_id' => changeCase('partner_state_id'),
            'partner_zip' => changeCase('partner_zip'),
            'partner_ssn' => changeCase('partner_ssn'),
            'partner_dob' => changeCase('partner_dob'),
            'partner_cell_hash' => changeCase('partner_cell_hash'),
            'product_sold' => changeCase('product_sold'),
            'disposition' => changeCase('disposition'),
            'marketing_notification' => changeCase('marketing_notification'),
            'buy_rate' => changeCase('buy_rate'),
            'payback_amount' => changeCase('payback_amount'),
            'lender_email' => changeCase('lender_email'),
            'no_of_deposit' => changeCase('no_of_deposit'),
            'negative_days' => changeCase('negative_days'),
            'nsf' => changeCase('nsf'),
            'fico_score_primary' => changeCase('fico_score_primary'),
            'fico_score_secondary' => changeCase('fico_score_secondary'),
            'deal_type' => changeCase('deal_type'),
            'entity_type' => changeCase('entity_type'),
            'agent_name' => changeCase('agent_name'),
            'under_writer' => changeCase('under_writer'),
            'position' => changeCase('position'),
            'iso_name' => changeCase('iso_name'),
            'annual_revenue' => changeCase('annual_revenue'),
            'monthly_revenue' => changeCase('monthly_revenue'),
            'withhold_percentage' => changeCase('withhold_percentage'),
            'broker_commission' => changeCase('broker_commission'),
            'terms_in_days' => changeCase('terms_in_days'),

            //new
            //'name' => changeCase('name'),
            //'status' => changeCase('status'),
            //'full_rtr' => changeCase('full_rtr'),
            //'our_rtr' => changeCase('our_rtr'),
            //'funded_amount' => changeCase('funded_amount'),
            //'our_funded_amount' => changeCase('our_funded_amount'),
            //'payments' => changeCase('payments'),
            //'payment_amount' => changeCase('payment_amount'),
            //'advance_type' => changeCase('advance_type'),
            //'our_payment_amount' => changeCase('our_payment_amount'),
            //'last_payment_date' => changeCase('last_payment_date'),
            //'complete' => changeCase('complete'),
            //'date_funded' => changeCase('date_funded'),
            //'net_zero_balance' => changeCase('net_zero_balance'),
            //'our_balance' => changeCase('our_balance'),
            //'our_balance_after_fee' => changeCase('our_balance_after_fee'),
            //'total_balance' => changeCase('total_balance'),
            //'factor_rate' => changeCase('factor_rate'),
            //'commission' => changeCase('commission'),
            //'syndication_fee' => changeCase('syndication_fee'),
            //'max_participant_fund' => changeCase('max_participant_fund'),
            //'annualized_rate' => changeCase('annualized_rate'),
            //'payment_left_rtr_payment' => changeCase('payment_left_rtr_payment'),
            //'lender_name' => changeCase('lender_name'),
            //'default_amount' => changeCase('default_amount'),
            //'industry' => changeCase('industry'),
            //'state' => changeCase('state'),
            //'anticipated_management_fee' => changeCase('anticipated_management_fee'),
            //'pace_payment' => changeCase('pace_payment'),
            //'our_pace_balance' => changeCase('our_pace_balance'),
            //'agent_iso_name' => changeCase('agent_iso_name'),
            //'business_started_date' => changeCase('business_started_date'),



            //'under_writer' => changeCase('under_writer'),
            //'entity_type' => changeCase('entity_type'),
            //'owner_credit_score' => changeCase('owner_credit_score'),
            //'partner_credit_score' => changeCase('partner_credit_score'),
            //'withhold_percentage' => changeCase('withhold_percentage'),
            //'deal_type' => changeCase('deal_type'),
            //'monthly_revenue' => changeCase('monthly_revenue'),
            //'phone' => changeCase('phone'),
            //'cell_phone' => changeCase('cell_phone'),
        ];

        return array_merge($arr, UserMetaHeading::pluck('heading', 'key')->toArray());
    }
}
