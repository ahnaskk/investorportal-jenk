<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 13/11/20
* Time: 1:15 AM.
*/

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IMessageRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\MerchantUser;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageRepository implements IMessageRepository
{
    public function __construct(IRoleRepository $role)
    {
        $this->table = new Message();
        $this->role = $role;
        $this->loggedUser = Auth::user();
    }

    public function getAll($data = [])
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $totalCount = $this->table->count();
        $tableData = $this->table->join('merchants', 'merchants.id', 'messages.model_id');
        if (empty($permission)) {
            $userId = Auth::user()->id;
            $investor = $this->role->allInvestors();
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->pluck('id')->toArray();
                $merchant = MerchantUser::whereIn('user_id', $subadmininvestor)->groupBy('merchant_id')->pluck('merchant_id')->toArray();
                $tableData = $tableData->whereIn('merchants.id', $merchant);
            }
        }
        if (isset($data['status'])) {
            $tableData = $tableData->wherestatus($data['status']);
        }
        if (isset($data['from_date'])) {
            $tableData = $tableData->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $tableData = $tableData->where('date', '<=', $data['to_date']);
        }
        $totalCountfilterd = $tableData->count();
        $tableData = $tableData->orderByDesc('messages.created_at');
        $tableData = $tableData->select(
      'messages.id',
      'messages.model_id',
      'messages.model_name',
      'messages.date',
      'messages.mobile',
      'messages.message',
      'messages.type',
      'messages.remark',
      'messages.status',
      'merchants.name',
      'messages.created_at'
    );
        $return['count'] = $tableData->count();
        $return['data'] = $tableData;

        return $return;
    }
}
