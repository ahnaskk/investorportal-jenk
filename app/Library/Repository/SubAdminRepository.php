<?php
/**
* Created by Rahees.
* User: raheesiocod
* Date: 4/10/21
*/
namespace App\Library\Repository;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\ISubAdminRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Template;
use App\Settings;
use Permissions;
use App\User;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Arr;
use DataTables;
use Form;
use FFM;
use Illuminate\Support\Facades\Schema;

class SubAdminRepository implements ISubAdminRepository
{
    protected $role;
    protected $user;
    public function __construct(IRoleRepository $role, IUserRepository $user,Builder $tableBuilder)
    {
        $this->role         = $role;
        $this->user         = $user;
        $this->id           = 1;
        $this->tableBuilder = $tableBuilder;
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }
    public function index()
    {
        $this->tableBuilder->ajax(route('admin::sub_admins::data'));
        $this->tableBuilder->parameters([
            'responsive' => true,
            'autoWidth'  => false,
            'processing' => true,
            'aaSorting'  => [],
            'pagingType' => 'input'
        ]);
        $this->tableBuilder = $this->tableBuilder->columns([
            ['className' => 'details-control', 'orderable' => false, 'searchable'       => false, 'data'         => 'DT_RowIndex', 'name'       => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
            ['data'      => 'name', 'name'                 => 'name', 'title'           => 'Name'],
            ['data'      => 'email', 'name'                => 'email', 'title'          => 'Email'],
            ['data'      => 'company_status', 'name'       => 'company_status', 'title' => 'Status'],['data'     => 'syndicate_company', 'name' => 'syndicate_company', 'title'    => 'Syndicate'],
            ['data'      => 'created_at', 'name'           => 'created_at', 'title'     => 'Created At'],
            ['data'      => 'updated_at', 'name'           => 'updated_at', 'title'     => 'Updated At'],
            ['data'      => 'action', 'name'               => 'action', 'title'         => 'Action', 'orderable' => false, 'searchable'         => false]
        ]);
        return [
            'tableBuilder' => $this->tableBuilder
        ];
    }
    public function rowData()
    {
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        $datas=$this->role->allSubAdmins();
        return DataTables::of($datas)
        ->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Companies', 'Edit')) {
                $return .= '<a href="'.route('admin::sub_admins::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Companies', 'Delete')) {
                $return .= Form::open(['route' => ['admin::sub_admins::delete', 'id' => $data['id']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }
            return $return;
        })
        ->editColumn('created_at', function ($data) {
            $creator_ = (isset($data['creator_id'])) ? get_user_name_with_session($data['creator_id']) : 'system';
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.$creator_;
            return "<a title='$created_date'>".FFM::datetime($data['created_at']).'</a>';
        })
        ->editColumn('company_status', function ($data) {
            if($data['company_status'] == 0){
                return "Disabled";
            } else {
                return "Enabled";
            }
        })
        ->editColumn('syndicate_company', function ($data) {
            if($data['syndicate'] == 1){
                return "Yes";
            } else {
                return "No";
            }
        })
        ->editColumn('updated_at', function ($data) { return FFM::datetime($data['updated_at']); })
        ->addIndexColumn()
        ->rawColumns(['action', 'created_at', 'updated_at'])
        ->make(true);
    }
    public function store($request)
    {
        try {
            if (!$this->user->createSubAdmin($request)) throw new \Exception("Smothing Went Wrong", 1);
            $settings = Settings::select('email')->first();
            $email_id_arr = explode(',', $settings->email);
            $message['title']   = $request->name.' Details';
            $message['subject'] = 'Company Details';
            $message['content'] = $request->name.' Company has been created successfully in the portal!';
            $message['company'] = $request->name;
            $message['to_mail'] = $email_id_arr;
            $message['status']  = 'company';
            $message['unqID']   = unqID();
            try {
                $email_template = Template::where([ ['temp_code', '=', 'COMPC'], ['enable', '=', 1], ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails  = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails  = array_diff($role_mails, $email_id_arr);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc']     = [];
                    $message['to_mail'] = $this->admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;            
    }
    public function find($id)
    {
        try {
            $subAdmin = $this->user->findSubAdmin($id);
            if(!$subAdmin){
                throw new \Exception("Invalid Company Id", 1);
            }
            $return['result']   = 'success';
            $return['subAdmin'] = $subAdmin;
        } catch (\Exception $e) {
            $return['result']   = $e->getMessage();
        }
        return $return;    
    }
    public function update($request,$id)
    {
        try {
            if (!$this->user->updateSubAdmin($id, $request)) {
                throw new \Exception('Something went Wrong', 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;    
    }
    public function delete($id)
    {
        try {
            if (!User::find($id)->delete()) throw new \Exception("Something Went Wrong", 1);
            if(!DB::table('user_has_roles')->where('model_id', $id)->delete()) throw new \Exception("Something Went Wrong", 1);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return; 
    }
}
