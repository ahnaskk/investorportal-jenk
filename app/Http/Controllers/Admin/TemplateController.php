<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ITemplateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;
use TemplateHelper;
class TemplateController extends Controller
{
    protected $template;

    public function __construct(ITemplateRepository $template, IRoleRepository $role)
    {
        $this->template = $template;
        $this->role = $role;
    }

    public function index(Builder $tableBuilder)
    {
        $response = TemplateHelper::getIndex($tableBuilder);
        return view('admin.template.index', $response);
    }

    public function selectTemplate(Request $request)
    {
        $templateId = $request->get('template_id');
        $template = $this->template->findTemplate($templateId);
        if ($template) {
            $template = $template->toArray();
            return response()->json(['msg' => 'success', 'status' => 1, 'content' => $template['content']]);
        }
    }

    public function selectType(Request $request)
    {
        $templateType = $request->get('template_type');
        if ($templateType) {
            $template = TemplateHelper::getType($templateType);
            if ($template) {
                return response()->json(['msg' => 'success', 'status' => 1, 'template' => $template]);
            } else {
                return response()->json(['msg' => 'success', 'status' => 0]);
            }
        }
    }

    public function create()
    {
        $response = TemplateHelper::createTemplate();
        return view('admin.template.create', $response);
    }

    public function storeCreate(Requests\AdminCreateTemplateRequest $request)
    {
        try {
            DB::beginTransaction();
            $return_result = $this->template->createTemplate($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Template Created.');
            DB::commit();
            return redirect()->route('admin::template::index');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowData(Request $request)
    {
        $template_type = $request->template_type;
        $data = $this->template->allTemplate($template_type);
        $data = $data->toArray();
        return TemplateHelper::getDatatableData($data);
    }

    public function update(Requests\AdminCreateTemplateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result = $this->template->updateTemplate($id, $request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $avoid_mail = ['INVTR', 'MERC'];
            if ($request->has('roles') && in_array($request->temp_code, $avoid_mail)) {
                $request->session()->flash('success', 'Roles will not be applied to this mail since it contains confidential data.');
            }
            $request->session()->flash('message', 'Template Updated.');
            DB::commit();

            return redirect()->route('admin::template::index');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $template = $this->template->findTemplate($id);
            if ($template) {
                $response = TemplateHelper::editTemplate($request, $template);
                return view('admin.template.create', $response);
            } else {
                $request->session()->flash('error','Invalid Template Id');
                return redirect(route("admin::template::index"));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            if ($this->template->deleteTemplate($id)) {
                $request->session()->flash('message', 'Template deleted');
                DB::commit();
            } else {
                DB::rollBack();
                return redirect()->to('admin/template/')->withErrors('Cannot Delete Template!');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getTheme(Request $request)
    {
        $des = $request->get('design');
        $data = ['name' => $des];

        return response()->json($data);
    }
    public function sendSample($id, Request $request) {
        $template = $this->template->findTemplate($id);
        $temp_code = null;
        if ($template && $template->enable) {
            $temp_code = $template->temp_code;
        }
        try {
            $status = $this->template->sendSample($temp_code, $id);
            if ($status) {
                $request->session()->flash('message', 'Sample email sent successfully.');
            } else {
                $request->session()->flash('error', 'Sample email not sent successfully.');
            }
            return redirect()->back();    
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
