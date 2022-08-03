<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:15 AM.
 */

namespace App\Library\Repository\Interfaces;

interface IMerchantBatchRepository
{
    public function getAll();

    public function datatable();

    public function find($id);

    public function delete($id);

    public function createRequest($request);

    public function updateRequest($request);
}
