<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 6/11/17
 * Time: 1:02 PM.
 */

namespace App\Library\Repository\Interfaces;

interface IMerchantPaymentRepository
{
    public function generatePayment($merchant);

    public function getPaymentByMerchantId($id, $date = null);

    //public function paymentUpdateRequest($merchant);
}
