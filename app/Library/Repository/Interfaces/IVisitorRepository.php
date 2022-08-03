<?php
/**
* Created by Rahees.
* User: iocod
* Date: 20/10/21
*/
namespace App\Library\Repository\Interfaces;
use \Illuminate\Http\Request;
interface IVisitorRepository
{
    public function iIndex(Request $request);
}
