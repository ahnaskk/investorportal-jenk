<?php

namespace App\Library\Repository\Interfaces;

interface ILiquidityLogRepository
{
    public function liquidiyLogReport($sdate, $edate, $merchants, $investors, $groupbypay, $owner, $description, $label, $search_key, $accountType);
}
