<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 4/01/21
* Time: 1:15 AM.
*/

namespace App\Library\Repository\Interfaces;

interface IPennyAdjustmentRepository
{
    public function getLiquidityDifference($data = []);

    public function getMerchantValueDifference($data = []);

    public function getCompanyAmountDifference($data = []);

    public function getZeroParticipantAmount($data = []);

    public function getFinalParticipantShare($data = []);

    public function getMerchantInvestorShareDifference($data = []);

    public function getMerchantsFundAmountCheck($data = []);
}
