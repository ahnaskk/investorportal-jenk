<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:14 AM.
 */

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IMerchantBatchRepository;
use App\Mbatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MerchantBatchRepository implements IMerchantBatchRepository
{
    public function __construct()
    {
        $this->table = new Mbatch();
    }

    public function getAll()
    {
        return $this->table->get();
    }

    public function datatable($fields = null)
    {
        $userId = Auth::user()->id;
        DB::statement(DB::raw('set @rownum=0'));
        $fields['row'] = DB::raw('@rownum  := @rownum  + 1 AS rownum');

        if ($fields != null) {
            $return = $this->table->select($fields);
        }
        // if (empty($user)) {
        //     $return = $return->where('creator_id', $userId);
        // }

        return $return;
    }

    public function find($id)
    {
        return $this->table->find($id);
    }

    public function delete($id)
    {
        if ($merchant = $this->find($id)) {
            return $merchant->delete();
        }

        return false;
    }

    public function createRequest($request)
    {
        $userId = Auth::user()->id;
        $SubStatus = $this->table->create(['name'=>$request->name, 'creator_id'=>$userId]);

        // $investor = \App\User::find($request->user_id);
        foreach ($request->merchants as $key => $value) {
            // code...

            DB::table('mbatch_merchant')->insert(['merchant_id'=>$value, 'mbatch_id'=>$SubStatus->id]);
        }

        return $SubStatus;
    }

    public function updateRequest($request)
    {
        $SubStatus = $this->table->find($request->id);
        $SubStatus->update(['name'=>$request->name]);
        DB::table('mbatch_merchant')->where('mbatch_id', $SubStatus->id)->delete();

        foreach ($request->merchants as $key => $value) {
            // code...

            DB::table('mbatch_merchant')->insert(['merchant_id'=>$value, 'mbatch_id'=>$SubStatus->id]);
        }

        return $SubStatus;
    }
}
