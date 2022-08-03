<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 3/05/18
 * Time: 4:40 PM.
 */

namespace App\Library\Transformer;

class InvestorTransactionTransformer extends TransformerAbstract
{
    public function transformModel($transaction)
    {
        return [
            'Id'                   => $transaction->id,
            'Transaction Category' => \ITran::getLabel($transaction->transaction_category),
            'Transaction Type'     => $transaction->transaction_type == 1 ? 'Debited' : 'Credited',
            'Amount'               => \FFM::dollar($transaction->amount),
            'Investment Date'      => date('m-d-Y', strtotime($transaction->date)),
            'Maturity date '       => date('m-d-Y', strtotime($transaction->maturity_date)),
            'Last Updated At'      => $transaction->updated_at->toDateString(),

        ];
    }
}
