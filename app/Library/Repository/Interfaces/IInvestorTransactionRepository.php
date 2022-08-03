<?php
/**
* Created by SREEJ32H.
* User: iocod
* Date: 11/01/18
* Time: 4:51 PM.
*/
namespace App\Library\Repository\Interfaces;
use Illuminate\Http\Request;
interface IInvestorTransactionRepository
{
    public function findTransaction($id);
    public function updateTransaction($id, Request $request);
    public function deleteTransaction($id);
    public function iIndexData($id,Request $request);
    public function iStore(Request $request,$id);
    public function iEdit($investorId,$tid);
    public function iUpdate(Request $request,$id,$tid);
    public function iexportData(Request $request,$id);
}
