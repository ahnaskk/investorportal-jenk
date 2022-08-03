<?php

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\IMessageRepository;
use App\Models\Message;
use App\User;
use DataTables;
use FFM;
use Form;
use Illuminate\Support\Facades\Auth;
use Permissions;

class MessageTableBuilder
{
    protected $messages;

    public function __construct(IMessageRepository $messages)
    {
        $this->messages = $messages;
        $this->loggedUser = Auth::user();
    }

    public function getMessageList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [['width' => '5%', 'data' => 'checkbox', 'type' => 'checkbox', 'name' => 'checkbox', 'title' => '<label class="chc" title=""><input type="checkbox" id="checkAllButtont"><span class="checkmark checkk"></span></label>', 'orderable' => false, 'searchable' => false, 'className' => 'checkbox11'], ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => '#', 'data' => 'id', 'name' => 'id', 'className' => 'details-control'], ['orderable' => true, 'visible' => false, 'searchable' => false, 'title' => 'Model', 'data' => 'model_name', 'name' => 'model_name'], ['orderable' => true, 'visible' => true, 'title' => 'Merchant', 'data' => 'merchants.name', 'name' => 'merchants.name'], ['orderable' => true, 'visible' => true, 'title' => 'Date', 'data' => 'messages.date', 'name' => 'messages.date'], ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Mobile', 'data' => 'mobile', 'name' => 'mobile'], ['orderable' => true, 'visible' => false, 'searchable' => true, 'title' => 'Message', 'data' => 'message', 'name' => 'message'], ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Remarks', 'data' => 'remark', 'name' => 'remark'], ['orderable' => true, 'visible' => true, 'title' => 'Status', 'data' => 'status', 'name' => 'status'], ['orderable' => false, 'visible' => true, 'searchable' => false, 'title' => 'Action', 'data' => 'action', 'name' => 'action']];
        }
        $requestData = [];
        if (isset($data['status'])) {
            $requestData['status'] = $data['status'];
        }
        if (isset($data['from_date'])) {
            $requestData['from_date'] = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $requestData['to_date'] = $data['to_date'];
        }
        $data = $this->messages->getAll($requestData);
        $count = $data['count'];
        $data = $data['data'];
        $sendor_id = config('settings.communication_portal_sendor_id');
        $website = config('settings.communication_portal_website');
        $sendButton = '';
        if ($count) {
            $sendButton = '<button type="button" id="sendButton" class="btn btn-info">Send All</button>';
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)->setTotalRecords($count)->editColumn('merchants.name', function ($row) {
            $url = \URL::to('/admin/merchants/view', $row->model_id);

            return isset($row->Model->deleted_at) ? $row->Model->name.'<span style="display:none;">'.$row->name.'</span>' : "<a target='blank' href='".$url."'>".$row->Model->name.'</a>'.'<span style="display:none;">'.$row->name.'</span>';
        })->editColumn('messages.date', function ($row) {
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by system';

            return "<a title='$created_date'>".FFM::date($row->date).'</a>';
        })->editColumn('status', function ($row) {
            return $row->statusName.'<span style="display:none;">'.$row->statusName.'</span>';
        })->editColumn('message', function ($row) {
            return rawurldecode(htmlspecialchars_decode($row->message));
        })->editColumn('action', function ($row) {
            if ($row->status != Message::COMPLETED) {
                return '<i table_id="'.$row->id.'" modal_name="'.$row->Model->name.'" class="glyphicon glyphicon-send singleSend pointer_cursor"></i>';
            }
        })->editColumn('mobile', function ($row) use ($website, $sendor_id) {
            $mobile = $row->mobile;
            if (! $mobile) {
                $mobile = $row->Model->cell_phone;
            }
            $mobile = Message::TrimMobileNo($mobile);
            $mobile = Message::InternationalizeNo($mobile);
            $return = '<span>'.$mobile.'</span>';
            if ($row->status == Message::COMPLETED) {
                $message_panel_link = $website."/user/contact/$mobile/$sendor_id/hi ".$row->Model->name;
                $return .= '<span class="pull-right"><a href="'.$message_panel_link.'" target="_blank"><i class="glyphicon glyphicon-send"></i></a></span>';
            }

            return $return;
        })->addColumn('checkbox', function ($row) {
            return "<label class='chc'><input type='checkbox' name='".$row->Model->name."' class='single_check' value='".$row->id."' onclick='uncheckMain();'><span class='checkmark checkk0'></span></label>";
        })->filterColumn('Merchant', function ($query, $keyword) {
            $sql = 'merchants.name like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('messages.date', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(messages.date,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->filterColumn('status', function ($query, $keyword) {
            if ($keyword == 'PENDING') {
                $keyword = 1;
            } elseif ($keyword == 'COMPLETED') {
                $keyword = 2;
            }
            $sql = 'status like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('mobile', function ($query, $keyword) {
            $sql = 'mobile like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('remark', function ($query, $keyword) {
            $sql = 'remark like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('message', function ($query, $keyword) {
            $sql = 'message like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->rawColumns(['merchants.name', 'mobile', 'action', 'checkbox', 'status', 'messages.date'])->with('sendButton', $sendButton)->addIndexColumn()->make(true);
    }
}
