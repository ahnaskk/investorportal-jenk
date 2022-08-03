<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:14 AM.
 */

namespace App\Library\Repository;

use App\Jobs\CommonJobs;
use App\Jobs\PaymentCreateCRM;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Merchant;
use App\MNotes;
use App\Settings;
use App\Template;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MNotesRepository implements IMNotesRepository
{
    public function __construct()
    {
        $this->table = new MNotes();
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    public function getAll()
    {
        return $this->table->get();
    }

    public function datatable($fields = null)
    {
        if ($fields != null) {
            return $this->table->select($fields);
        }

        return $this->table;
    }

    public function find($id)
    {
        return $this->table->find($id);
    }

    public function delete($id)
    {
        if ($note = $this->find($id)) {
            return $note->delete();
        }

        return false;
    }

    public function createRequest($request)
    {
        $MNotes = $this->table->create($request);

        // notes added to crm

        $form_params = [
                'method' => 'add_merchant_notes',
                'username' => config('app.crm_user_name'),
                'password' => config('app.crm_password'),
                'investor_merchant_id'=>$MNotes->merchant_id,
                'notes_id'=>$MNotes->id,
                'notes'=>$MNotes->note,
                'created'=>$MNotes->created_at,
           ];

        try {
            $crmJob = (new PaymentCreateCRM($form_params))->delay(now()->addMinutes(1));
            dispatch($crmJob);
            //already configured delay here
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        ////////////////////////////////

        $author = Auth::user()->name;

        $merchant_name = Merchant::where('id', $MNotes->merchant_id)->value('name');

        // header('Content-type: text/plain');

        $message['title'] = $merchant_name.' Notes';
        $message['subject'] = $merchant_name.' Notes';
        $message['content'] = 'A note has been added to the merchant <a href='.\URL::to('admin/merchants/view', $MNotes->merchant_id).'>'.$merchant_name.'</a> by '.
            $author."<BR><br> $MNotes->note .";

        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $message['to_mail'] = $emailArray;

        $message['status'] = 'merchant_note';
        $message['merchant_id'] = $MNotes->merchant_id;
        $message['merchant_name'] = $merchant_name;
        $message['note'] = $MNotes->note;
        $message['author'] = $author;
        $message['unqID'] = unqID();
        $message['date_time'] = \FFM::datetime(\Carbon\Carbon::now('UTC'));

        try {
            $email_template = Template::where([
                ['temp_code', '=', 'NOTES'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $emailArray);
                        $bcc_mails[] = $role_mails;
                    }
                    $message['bcc'] = Arr::flatten($bcc_mails);
                }
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $message['bcc'] = [];
                $message['to_mail'] = $this->admin_email;
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        //->all()

        // $investor = \App\User::find($request->user_id);

        /*  $MNotes->note = $request['note'];
          $MNotes->merchant_id = $request['merchant_id'];

          $MNotes->save();*/

        return $MNotes;
    }

    public function updateRequest($request)
    {
        $MNotes = $this->table->find($request->id);
        $MNotes->update($request->all());
        $MNotes->note = $request->note;
        $MNotes->merchant_id = $request->merchant_id;

        $MNotes->save();

        return $MNotes;
    }
}
