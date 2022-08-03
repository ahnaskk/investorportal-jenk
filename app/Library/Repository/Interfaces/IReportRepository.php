<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 02/02/2021
* Time: 7:15 PM.
*/

namespace App\Library\Repository\Interfaces;

interface IReportRepository
{
    public function getFeesReport($data = []);
}
