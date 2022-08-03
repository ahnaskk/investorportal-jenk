<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 6/11/17
 * Time: 12:07 PM.
 */

namespace App\Library\Repository\Interfaces;

interface IParticipantPaymentRepository
{

    public function datatable($select);

    public function openItems($select);

    public function allPayments($select);

    public function generatePayment($merchant, $date);

    public function getAllByMerchantId($select, $id, $builder = null);

    public function getMerchantPayments($id, $company_id, $investor_id);
}
