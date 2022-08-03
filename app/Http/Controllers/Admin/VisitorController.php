<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Repository\Interfaces\IVisitorRepository;
class VisitorController extends Controller
{
    protected $request;
    public function __construct(IVisitorRepository $visitor) {
		$this->Visitor = $visitor;
        $this->request = app('request');
    }
    public function index(Request $request) {
        $page_title = 'Visitor';
        $returnData=$this->Visitor->iIndex($request);
        return view('admin.visitor')
        ->with('page_title',$page_title)
        ->with('onlineUsers',$returnData['onlineUsers'])
        ->with('Visitors',$returnData['Visitors'])
        ;
    }
}
