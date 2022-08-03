<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\ISubAdminRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SubadminController extends Controller
{
    protected $role;
    protected $user;
    public function __construct(ISubAdminRepository $SubAdmin)
    {
        $this->SubAdmin = $SubAdmin;
    }
    public function index()
    {
        $page_title   = 'Investor Companies';
        $returnData   = $this->SubAdmin->index();
        $tableBuilder = $returnData['tableBuilder'];
        return view('admin.sub_admins.index', compact('page_title', 'tableBuilder'));
    }
    public function create()
    {
        $page_title = 'Create Companies';
        $action = 'create';
        return view('admin.sub_admins.create', compact('page_title', 'action'));
    }
    public function rowData()
    {
        return  $this->SubAdmin->rowData();
    }
    public function storeCreate(Requests\AdminCreateSubAdminRequest $request)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->SubAdmin->store($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'New Company created!');
            DB::commit();
            return redirect()->route('admin::sub_admins::index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }
    public function edit($id,Request $request)
    {
        try {
            $page_title = 'Edit Companies';
            $action     = 'edit';
            $return_result=$this->SubAdmin->find($id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $sub_admin = $return_result['subAdmin'];
            $im        = $sub_admin->logo;
            return view('admin.sub_admins.create', compact('page_title', 'sub_admin', 'im', 'action'));
        } catch (\Exception $e) {
            $request->session()->flash('error',$e->getMessage());
            return redirect(route("admin::sub_admins::index"));
        }
    }
    public function update(Requests\AdminUpdateSubAdminRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->SubAdmin->update($request,$id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Company Updated');
            DB::commit();
            return redirect()->route('admin::sub_admins::index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }
    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->SubAdmin->delete($id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Company Deleted!');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
        return redirect()->back();
    }
}
