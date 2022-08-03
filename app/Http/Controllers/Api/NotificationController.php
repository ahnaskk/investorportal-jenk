<?php

namespace App\Http\Controllers\Api;

use App\Helpers\MailBoxHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Mailboxrow;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $user = false;

    public function __construct()
    {
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
    }

    public function postList(Request $request)
    {
        $errors = [];
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $startDate = $request->input('sDate', null);
        $endDate = $request->input('eDate', null);
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        $mailBoxQuery = MailBoxHelper::getNotificationListQuery($this->user->id, true, true, $startDate, $endDate);
        try {
            $totalRecords = $mailBoxQuery->count();
            $data = $mailBoxQuery->limit($limit)->offset($offset)->get();
            $total_page = $totalRecords;
            $from = ($total_page != 0) ? $offset + 1 : 0;
            $to = ($total_page != 0) ? ($offset + count($data)) : 0;
            $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
            $no_of_pages = (int) ($total_page / $limit);
            if (($total_page % $limit) > 0) {
                $no_of_pages = $no_of_pages + 1;
            }
            $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];

            return new SuccessResource(['data' => $data, 'count' => $totalRecords, 'total_records' => $totalRecords, 'pagination' => $pagination]);
        } catch (QueryException $e) {
            $errors = 'No data found.';
        } catch (\ErrorException $e) {
            $errors = $e->getMessage();
        }

        return response()->json(['status' => false, 'errors' => ['message' => $errors]]);
    }

    public function postReadUpdate(Request $request)
    {
        $id = $request->input('id', 0);
        $update = Mailboxrow::where('id', $id)->update(['read_status' => 1]);

        return ($update) ? new SuccessResource(['message' => 'read update successfully']) : new ErrorResource(['message' => 'No record found.']);
    }

    public function postCount(Request $request)
    {
        $count = MailBoxHelper::getUnreadCount($this->user->id);

        return new SuccessResource(['count' => $count]);
    }

    public function postClearNotifications(Request $request)
    {
        $userId = $this->user->id;
        $delete = Mailboxrow::where(function ($q) use ($userId) {
            $q->where('user_ids', 'LIKE', '['.$userId.']')->orWhere('user_ids', 'LIKE', '['.$userId.',%]')->orWhere('user_ids', 'LIKE', '[%,'.$userId.']')->orWhere('user_ids', 'LIKE', '[%,'.$userId.',%]');
        })->delete();

        return ($delete) ? new SuccessResource(['message' => 'Cleared Successfully']) : new ErrorResource(['message' => 'Clear Failed']);
    }
}
