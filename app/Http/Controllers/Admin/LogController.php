<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Repository\Interfaces\ILogRepository;
class LogController extends Controller
{
    protected $request;
    public function __construct(ILogRepository $Log) {
        $this->Log     = $Log;
        $this->request = app('request');
    }
    public function index(Request $request) {
        $page_title = 'Log';
        $returnData=$this->Log->iIndex($request);
        return view('admin.log')
        ->with('page_title',$page_title)
        ->with('logs',$returnData['logs'])
        ->with('files',$returnData['files'])
        ->with('current_file',$returnData['current_file'])
        ;
    }
    public function download(Request $request) {
        $returnData=$this->Log->iDownload($request);
        return response()->download($returnData);
    }
    public function delete(Request $request) {
        $this->Log->iDelete($request);
        return redirect(route('Log::page'))->with([
            'message'    => 'Successfully deleted log '.base64_decode($this->request->input('del')),
            'alert-type' => 'success',
        ]);
    }
    public function deleteAll(Request $request) {
        $this->Log->iDeleteAll($request);
        return redirect(route('Log::page'))->with([
            'message'    => 'Successfully deleted all log files',
            'alert-type' => 'success',
        ]);
    }
}
