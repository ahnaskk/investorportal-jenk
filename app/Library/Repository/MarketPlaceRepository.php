<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 14/12/17
 * Time: 4:29 PM.
 */

namespace App\Library\Repository;

use App\FundingRequests;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Marketplace;

class MarketPlaceRepository implements IMarketPlaceRepository
{
    private $table;

    public function __construct()
    {
        $this->table = new Marketplace();
        $this->FundingRequests = new FundingRequests();
    }

    public function datatable($fields = null)
    {
        if ($fields != null) {
            return $this->table->select($fields);
        }

        return $this->table;
    }

    public function update($request)
    {
        // TODO: Implement update() method.
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->table, $method], $args);
    }

    public function requests($fields = '', $mid = '')
    {
        if ($fields != null) {
            return $this->FundingRequests->select($fields)->where('mid', $mid);
        }

        return $this->FundingRequests;
    }
}
