<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 14/12/17
 * Time: 4:28 PM.
 */

namespace App\Library\Repository\Interfaces;

interface IMarketPlaceRepository
{
    public function update($request);

    public function datatable($fields = null);
}
