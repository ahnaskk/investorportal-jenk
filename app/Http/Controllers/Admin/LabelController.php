<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Html\Builder;
use LabelHelper;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    public function __construct(ILabelRepository $label)
    {
        $this->label = $label;
    }

    public function index(Builder $tableBuilder)
    {
        
        $result = LabelHelper::allLabels($tableBuilder);
        return view('admin.label.index',$result);
    }

    public function rowData()
    {
        try {
            return LabelHelper::rowLabel();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result=LabelHelper::deleteLabel($request,$id);
            if($result['result']!='success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'label deleted');
            DB::commit();
            return redirect()->route('admin::label::index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $result = LabelHelper::createLabel();
        return view('admin.label.create',$result);
    }

    public function storeCreate(Requests\AdminCreateLabelRequest $request)
    {
        try {
            DB::beginTransaction();
            $result=LabelHelper::storeLabel($request);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'New Label created.');
            DB::commit();
            return redirect()->route('admin::label::index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Requests\AdminCreateLabelRequest $request)
    {
        try {
            DB::beginTransaction();
            $result=LabelHelper::updateLabel($request);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'Label Updated');
            DB::commit();
            return redirect()->route('admin::label::index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit($id,Request $request)
    {
        try {
            $Label = $this->label->find($id);
            if(!$Label){
                $request->session()->flash('error','Invalid Label Id');
                return redirect(route("admin::label::index"));
            }
            $result = LabelHelper::editLabel($id,$request);
            if ($result) {
                return view('admin.label.create',$result);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
