<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Helpers\MessageTableBuilder;
use App\Library\Repository\Interfaces\IMessageRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Models\Message;
use App\Settings;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Html\Builder;

class MessageController extends Controller
{
    public function __construct(IRoleRepository $role, IMessageRepository $messages)
    {
        $this->role = $role;
        $this->messages = $messages;
    }

    public function index(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $page_title = 'Messages';
        $page_description = 'Messages';
        $MessageTableBuilder = new MessageTableBuilder($this->messages);
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->status) {
                $requestData['status'] = $request->status;
            }
            if ($request->from_date) {
                $requestData['from_date'] = $request->from_date;
            }
            if ($request->to_date) {
                $requestData['to_date'] = $request->to_date;
            }
            $data = $MessageTableBuilder->getMessageList($requestData);

            return $data;
        }
        $tableBuilder->ajax(['url' => route('admin::messages::tableData'), 'type' => 'post', 'data' => 'function(data){
                data._token    = "'.csrf_token().'";
                data.from_date = $("#from_date").val();
                data.to_date   = $("#to_date").val();
                data.status    = $("#status").val();
            }']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                var info = this.dataTable().api().page.info();\n                var page = info.page;\n                var length = info.length;\n                var index = (page * length + (iDataIndex + 1));\n                $('td:eq(1)', nRow).html(index).addClass('txt-center');\n            }", 'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $(n.column(9).footer()).html(o.sendButton);
            }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($MessageTableBuilder->getMessageList($requestData));
        $investors = $role->allInvestors()->pluck('name', 'id');

        return view('admin.messages', compact('tableBuilder', 'page_title', 'investors', 'page_description'));
    }

    public function send(Request $request, $id = null)
    {
        DB::beginTransaction();
        $return['success_response'] = '';
        $return['success_count'] = 0;
        $return['failed_response'] = '';
        $return['failed_count'] = 0;
        if ($id) {
            $Self = Message::where('id', $id);
        } else {
            $data = $request->all();
            if (empty($data['selectedId'])) {
                throw new \Exception('Empty Selection', 1);
            }
            $Self = Message::where('status', Message::PENDING);
            if ($data['from_date']) {
                $Self->where('date', '>=', $data['from_date']);
            }
            if ($data['to_date']) {
                $Self->where('date', '<=', $data['to_date']);
            }
            $Self->whereIn('id', $data['selectedId']);
        }
        $Self = $Self->get();
        if ($Self->count()) {
            foreach ($Self as $key => $value) {
                try {
                    $return_function = $value->sendMessage();
                    if ($return_function['result'] != 'success') {
                        throw new \Exception($return_function['result'], 1);
                    }
                    $value->status = Message::COMPLETED;
                    $return['result'] = 'success';
                    $return['success_count']++;
                    $return['result'] = $return_function['result'];
                    $return['success_response'] .= 'Successfully Sended For '.$value->Model->name.' on '.$value->mobile.'<br>';
                } catch (\Exception $e) {
                    $value->status = Message::PENDING;
                    $return['result'] = $e->getMessage();
                    $return['failed_count']++;
                    $return['failed_response'] .= 'Failed For '.$value->Model->name.' - '.$value->mobile.'- Reason : '.$e->getMessage().'<br>';
                    DB::rollback();
                }
                $value->remark = $return['result'];
                $value->save();
                DB::commit();
            }
        } else {
            $return['failed_response'] = 'No Pending Messages';
            $return['failed_count'] = '';
        }

        return response()->json($return);
    }

    public function sendSample($data = null)
    {
        try {
            if (! isset($data)) {
                $data['mobile'] = '919633155669';
                $data['message'] = 'Dear 9397merchant,  ACH amount of $2,333.33 at velocity group USA has been declined due to Insufficient fund( PreAuth - CheckAuth:090702446 ) on 10-29-2020. Please use the following link to make the payment vgusa.com/payment';
            }
            if (! $data['mobile']) {
                throw new \Exception('Empty Mobile No', 1);
            }
            if (! $data['message']) {
                throw new \Exception('Empty Message', 1);
            }
            $api_key = 'bmlraGlsQGlvY29kLmNvbTpuaWtoaWxAaW9jb2QuY29t';
            $sendor_id = '16314072649';
            $mobile = $data['mobile'];
            $sms = $data['message'];
            $url = 'https://sms4.ionqu.com/sms/api?action=send-sms';
            $url .= "&api_key=$api_key";
            $url .= "&to=$mobile";
            $url .= "&from=$sendor_id";
            $url .= "&sms=$sms";
            $request = Http::asForm()->get($url);
            $response = $request->body();
            $response = json_decode($response);
            if ($response->code != 'ok') {
                throw new \Exception($response->message, 1);
            }
            $response->result = 'success';
        } catch (\Exception $e) {
            $response->result = $e->getMessage();
        }

        return json_encode($response);

        return $response;
    }
}
