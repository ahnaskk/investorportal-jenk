<?php
/**
 * Created by Rahees.
 * User: raheesiocod
 * Date: 05/10/21
 */
namespace App\Library\Repository\Interfaces;
interface ITransactionsRepository
{
    public function IgetTransactionData($data);
    public function IgetTransactionDataTable($request);
    public function IPendingTransactions($request);
    public function IApproveTransactions($request,$id);
}
