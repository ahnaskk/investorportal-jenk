<?php
/**
* Created by Rahees.
* User: iocod
* Date: 20/10/21
*/
namespace App\Library\Repository\Interfaces;
use \Illuminate\Http\Request;
interface ILogRepository
{
    public function iIndex(Request $request);
    public function iDownload(Request $request);
    public function iDelete(Request $request);
    public function iDeleteAll(Request $request);
}
