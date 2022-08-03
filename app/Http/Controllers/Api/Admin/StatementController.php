<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Jobs\CommonJobs;
use App\Statements;
use App\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Settings;
use Carbon\Carbon;

class StatementController extends AdminAuthController
{
    public function postDelete(Request $request)
    {
        $statementIds = $request->input('statement_id');
        if (is_array($statementIds) and count($statementIds) > 0) {
            foreach ($statementIds as $statementId) {
                $statement = Statements::where('id', $statementId)->first();
                if ($statement) {
                    Storage::disk('s3')->delete($statement->file_name);
                    $statement->delete();
                }
            }
        }

        return new SuccessResource(['message' => 'Statement deleted successfully']);
    }

    public function postInvestorMail(Request $request)
    {
        $statementIds = $request->input('statement_id');
        $msg = '';
        if (is_array($statementIds) and count($statementIds) > 0) {
            foreach ($statementIds as $statementId) {
                $investor = Statements::select('name', 'statements.id', 'email', 'file_name', 'notification_email')->where('statements.id', $statementId)->join('users', 'users.id', 'statements.user_id')->first();
                $statement = Statements::where('id', $statementId)->first();
                if ($investor && $statement) {
                    $fileName = $investor->file_name.'.pdf';
                    $fileUrl = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
                    $message = ['title' => 'Payment Report Statement'];
                    $message['content'] = 'Payment Statement Report ';
                    $message['to_mail'] = ($investor['notification_email'] != null) ? $investor['notification_email'] : $investor['email'];
                    $message['options'] = 'Weekly';
                    $message['investor_name'] = $investor['name'];
                    $message['attach'] = $fileUrl;
                    $message['status'] = 'pdf_mail';
                    $message['fileName'] = $fileName;
                    $message['heading'] = 'Payment Report Statement';
                    $message['unqID'] = unqID();
                    $message['template_type'] = 'pdf_normal';
                    $email_template = Template::where([
                        ['temp_code', '=', 'GPDF'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $bcc_mails[] = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['bcc'] = [];
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                        $msg .= ' Mail Sent Successfully for '.$investor->name.'<a class="btn btn-success" href='.$fileUrl.'>  Click here to view </a><br>';
                        $statement->update(['mail_status' => 1]);
                    } else {
                        $msg .= 'Please enable mail template.';
                    }
                }
            }
        }

        return new SuccessResource(['message' => $msg]);
    }
}
