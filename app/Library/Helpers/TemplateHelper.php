<?php

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\ITemplateRepository;
use App\Template;
use Illuminate\Support\Facades\DB;
use Form;
use Permissions;

class TemplateHelper
{
    public function __construct(ITemplateRepository $template)
    {
        $this->template = $template;
    }
    public static function getIndex($tableBuilder)
    {
        $page_title = 'Templates';
        $tableBuilder->ajax(['url' => route('admin::template::data'), 'data' => 'function(d) {
            d.template_type = $("#template_type").val()
        }']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['data' => 'id', 'name' => 'id', 'title' => '#', 'searchable' => false, 'orderable' => false], ['data' => 'title', 'name' => 'title', 'title' => 'Name'], ['data' => 'type', 'name' => 'type', 'title' => 'Type'], ['data' => 'assignees', 'name' => 'assignees', 'title' => 'Assigned to'], ['data' => 'subject', 'name' => 'subject', 'title' => 'Subject'], ['data' => 'status', 'name' => 'status', 'title' => 'Status'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);
        $template_type = Template::getTypes();
        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'template_type' => $template_type
        ];
    }
    public static function getType($type)
    {
        return Template::where('type', $type)->whereNull('temp_code')->pluck('title', 'id')->toArray();
    }
    public function createTemplate()
    {
        $page_title = 'Create Template';
        $action = 'Create';
        $template_types = Template::getTypes();
        $enable = [1 => 'Yes', 2 => 'No'];
        $template_codes = $this->template->getTemplateCodes();
        $roles = DB::table('roles')->pluck('name', 'id')->toArray();
        return [
            'page_title' => $page_title,
            'action' => $action,
            'template_types' => $template_types,
            'template_codes' => $template_codes,
            'enable' => $enable,
            'roles' => $roles
        ];
    }
    public function getDatatableData($data)
    {
        return \DataTables::collection($data)->editColumn('title', function ($data) {
            return $data['title'];
        })->editColumn('type', function ($data) {
            $types = Template::getTypes();

            return $types[$data['type']];
        })->editColumn('assignees', function ($data) {
            $templatename = $this->template->getTemplateName($data['temp_code']);

            return $templatename;
        })->editColumn('subject', function ($data) {
            return $data['subject'];
        })->editColumn('status', function ($data) {
            return ($data['enable'] == 1) ? 'Enabled' : 'Disabled';
        })->addColumn('action', function ($data) {
            $return = '';
            if (@Permissions::isAllow('Template Management', 'Edit')) {
                $return .= '<a href="'.route('admin::template::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (@Permissions::isAllow('Template Management', 'Delete')) {
                $return .= Form::open(['route' => ['admin::template::delete', 'id' => $data['id']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->make(true);
    }
    public function checkTemplateExist($id)
    {
        $Temp=Template::find($id);
        if (!$Temp){
            return false;
        } else {
            return $Temp;
        }
    }
    public function editTemplate($request, $template)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $template_types = Template::getTypes();
        $enable = [1 => 'Yes', 0 => 'No'];
        $template_codes = $this->template->getTemplateCodes();
        $roles = DB::table('roles')->pluck('name', 'id')->toArray();
        $page_title = 'Edit Template';
        $action = 'edit';
        $template->temp_code_name = $this->template->getTemplateName($template->temp_code);
        $template->assignees = explode(',', $template->assignees);
        return [
            'page_title' => $page_title,
            'template' => $template,
            'action' => $action,
            'template_types' => $template_types,
            'template_codes' => $template_codes,
            'enable' => $enable,
            'roles' => $roles
        ];
    }
}