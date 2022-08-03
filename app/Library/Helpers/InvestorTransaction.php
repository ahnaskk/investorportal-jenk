<?php

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\IRoleRepository;

class InvestorTransaction
{
    const INVESTOR_CREDITED     = 1;
    const FUNDED_MERCHANT       = 2;
    const INTEREST_CREATED      = 3;
    const INVESTOR_DEBITED      = 4;
    const BILLS_PAID            = 10;
    const CARRY                 = 11;
    const GIFT_EQUITY           = 13;
    const GIFT_EQUITY_D         = 14;
    const EQUITY_DISTRIBUTION   = 6;
    const EQUITY_DISTRIBUTION2  = 7;
    const EQUITY_DISTRIBUTION3  = 8;
    const SELECT_CATEGORIES     = 0;
    const RETURN_OF_PRINCIPAL   = 12;
    const V_PROFIT_DISTR        = 15;
    const V_INVESTOR_DISTR      = 16;
    const V_PACT_DISTR          = 17;
    const VELOCITY_CONTRIBUTION = 18;
    const CREDIT_CARD           = 42;
    const LENDER_FEE            = 44;
    const BANK_FEE              = 46;
    const LEGAL_FEE             = 48;

    public function getAllOptions()
    {
        return [
            self::SELECT_CATEGORIES     => 'Select Categories',
            self::INVESTOR_CREDITED     => 'Transfer To Velocity',
            self::RETURN_OF_PRINCIPAL   => 'Return of Principal',
            self::EQUITY_DISTRIBUTION   => 'Equity Distribution to investor',
            self::EQUITY_DISTRIBUTION2  => 'Equity Distribution to velocity',
            self::EQUITY_DISTRIBUTION3  => 'Equity Distribution to Pactolus',
            self::INTEREST_CREATED      => 'Interest generated',
            self::INVESTOR_DEBITED      => 'Transfer To Bank',
            self::BILLS_PAID            => 'Bills paid',
            self::CARRY                 => 'Carry',
            self::GIFT_EQUITY           => 'Allocation of equity',
            self::GIFT_EQUITY_D         => 'Allocation of equity (Debit)',
            self::V_PROFIT_DISTR        => 'Profit Distribution (velocity)',
            self::V_INVESTOR_DISTR      => 'Profit Distribution (Investor)',
            self::V_PACT_DISTR          => 'Profit Distribution (Pactolus)',
            self::VELOCITY_CONTRIBUTION => 'Velocity Contribution',
            self::CREDIT_CARD           => 'Credit Card Payment',
            self::LENDER_FEE            => 'Lender Fee Transaction',
            self::BANK_FEE              => 'Bank Fee Transaction',
            self::LEGAL_FEE             => 'Legal Fee Transaction',
        ];
    }

    public function getACHCreditOptions()
    {
        return [
            self::SELECT_CATEGORIES     => 'Select Categories',
            self::RETURN_OF_PRINCIPAL   => 'Return of Principal',
            self::INVESTOR_DEBITED      => 'Transfer To Bank',
        ];
    }

    public function getVelocityDistributionOptions()
    {
        return [self::EQUITY_DISTRIBUTION2 => 'Equity Distribution to velocity', self::EQUITY_DISTRIBUTION => 'Equity Distribution to investor'];
    }

    public function getLabel($id)
    {
        if ($id == self::INVESTOR_CREDITED) {
            return 'Transfer To Velocity';
        } elseif ($id == self::INTEREST_CREATED) {
            return 'Interest generated';
        } elseif ($id == self::RETURN_OF_PRINCIPAL) {
            return 'Return of Principal';
        } elseif ($id == self::EQUITY_DISTRIBUTION) {
            return 'Equity Distribution';
        } elseif ($id == self::EQUITY_DISTRIBUTION2) {
            return 'Equity Distribution to velocity';
        } elseif ($id == self::EQUITY_DISTRIBUTION3) {
            return 'Equity Distribution to pactolus';
        } elseif ($id == self::INVESTOR_DEBITED) {
            return 'Transfer To Bank';
        } elseif ($id == self::BILLS_PAID) {
            return 'Bills paid';
        } elseif ($id == self::CARRY) {
            return 'Carry';
        } elseif ($id == self::GIFT_EQUITY) {
            return 'Allocation of equity';
        } elseif ($id == self::GIFT_EQUITY_D) {
            return 'Allocation of equity (Debit)';
        } elseif ($id == self::V_PROFIT_DISTR) {
            return 'Profit Distribution (velocity)';
        } elseif ($id == self::V_INVESTOR_DISTR) {
            return 'Profit Distribution (Investor)';
        } elseif ($id == self::V_PACT_DISTR) {
            return 'Profit Distribution (Pactolus)';
        } elseif ($id == self::VELOCITY_CONTRIBUTION) {
            return 'Velocity Contribution';
        } elseif ($id == self::CREDIT_CARD) {
            return 'Credit Card Payment';
        } elseif ($id == self::LENDER_FEE) {
            return 'Lender Fee Transaction';
        } elseif ($id == self::BANK_FEE) {
            return 'Bank Fee Transaction';
        } elseif ($id == self::LEGAL_FEE) {
            return 'Legal Fee Transaction';
        }else {
            return '-';
        }
    }
    
    public static function getTransactionTypeEditEnabledList() {
        $list=[
            Self::V_PROFIT_DISTR,
            Self::V_INVESTOR_DISTR,
            Self::V_PACT_DISTR,
        ];
        return implode('","',$list);
    }
}
