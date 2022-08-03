<?php

namespace App\Helpers;

use App\Document;
use App\Jobs\CommonJobs;
use App\Library\Repository\InvestorTransactionRepository;
use App\Mailboxrow;
use App\Merchant;
use App\MerchantUser;
use App\Settings;
use App\Template;
use App\User;
use FFM;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDF;
use InvestorHelper;

class PdfDocumentHelper
{
    public static function fundMerchant(array $pdfData, string $signature)
    {   
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $toEmails = Settings::first()->pluck('email')->first();
        $toEmails = explode(',', $toEmails);
        $folderPath = public_path('signature/');
        $fileSystem = new Filesystem;
        $fileSystem->cleanDirectory($folderPath);
        $image_parts = explode(';base64,', $signature);
        $image_type_aux = explode('image/', $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath.uniqid().'.'.$image_type;
        $check_image = imagecreatefromstring($image_base64);
        if (! $check_image) {
            return $check_image;
        }
        $imgResized = imagescale($check_image, 348, 148);
        $path = imagepng($imgResized, $file);
        $merchant_id = $pdfData['merchant_id'];
        $userId = $pdfData['user_id'];
        $tot_inv_amnt_before_funding = MerchantUser::where('merchant_id', $pdfData['merchant_id'])->value('amount');
        $merchantUser = MerchantUser::create(['merchant_id' => $pdfData['merchant_id'], 'amount' => $pdfData['participant_funded_amount'], 'user_id' => $pdfData['user_id'], 'mgmnt_fee' => $pdfData['management_fee_per'], 'syndication_fee_percentage' => $pdfData['m_syndication_fee_per'], 'pre_paid' => $pdfData['m_syndication_fee'], 'invest_rtr' => $pdfData['participant_rtr'], 'share' => $pdfData['participant_percent'], 'status' => 1, 'commission_amount' => $pdfData['commission_amount'],'up_sell_commission' => $pdfData['up_sell_commission'],'up_sell_commission_per'=>$pdfData['upsell_commission_per'], 'under_writing_fee' => $pdfData['underwriting_fee'], 'under_writing_fee_per' => $pdfData['underwriting_fee_per'], 'commission_per' => $pdfData['upfront_commission_per'], 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
        if ($merchantUser) {
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            $MerchantUser = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $OverpaymentAccount->id)->first();
            if (! $MerchantUser) {
                $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0, 'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => (Auth::user()) ? Auth::user()->id : null];
                MerchantUser::create($item);
            }
            $message['content'] = '<a href='.url('admin/investors/portfolio/'.$pdfData['user_id']).'>'.$pdfData['participant'].'</a> Invested '.FFM::dollar($pdfData['participant_funded_amount']).' in the merchant  <a href='.url('admin/merchants/view/'.$pdfData['merchant_id']).'>'.$pdfData['merchant'].'</a>';
            $message['investor'] = $pdfData['participant'];
            $message['title'] = 'Investment request';
            $message['merchant_name'] = $pdfData['merchant'];
            $message['merchant_id'] = $pdfData['merchant_id'];
            $message['amount'] = $pdfData['participant_funded_amount'];
            $message['user_id'] = $pdfData['user_id'];
            $message['timestamp'] = time();
            $message['status'] = 'funding_request';
            $message['subject'] = 'Funding request | Velocitygroupusa';
            $message['unqID'] = unqID();
            $message['to_mail'] = $toEmails;
            $mailboxdb = new Mailboxrow();
            $mailboxdb->content = $message['content'];
            $mailboxdb->title = $message['title'];
            $mailboxdb->timestamp = $message['timestamp'];
            $mailboxdb->user_id = $pdfData['user_id'];
            $mailboxdb->permission_user = $pdfData['user_id'];
            $mailboxdb->save();
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'FUNDR'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mail = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mail = array_diff($role_mail, $toEmails);
                            $bcc_mails[] = $role_mail;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        self::createMerchantFundDetailTable();
        $merchantFund = DB::table('merchant_fund_details')->where(['merchant_id' => $pdfData['merchant_id']])->first();
        if ($merchantFund) {
            DB::table('merchant_fund_details')->where(['merchant_id' => $pdfData['merchant_id']])->update(['pmnts' => $pdfData['pmnts'], 'factor_rate' => $pdfData['factor_rate']]);
        } else {
            DB::table('merchant_fund_details')->insert(['merchant_id' => $pdfData['merchant_id'], 'pmnts' => $pdfData['pmnts'], 'factor_rate' => $pdfData['factor_rate']]);
        }
        $pdfData['signature'] = $file;
        InvestorHelper::update_liquidity($pdfData['user_id'], 'Assign Investor', $pdfData['merchant_id']);
        $filePathS3 = 'Agreement_'.uniqid().'.pdf';
        $fileName = 'marketplace/'.$pdfData['merchant_id'].'/92'.$filePathS3;
        $pdfURL = self::generatePdf($pdfData, $fileName);
        $to_mail = Auth::user()->email;
        $message['content'] = 'You have successfully invested '.FFM::dollar($pdfData['participant_funded_amount']).' in <a href='.url('admin/merchants/view/'.$pdfData['merchant_id']).'>'.$pdfData['merchant'].'</a> . Thank you for your participation.';
        $message['title'] = 'Funding Request Details';
        $message['merchant_name'] = $pdfData['merchant'];
        $message['merchant_id'] = $pdfData['merchant_id'];
        $message['document_url'] = Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2));
        $message['timestamp'] = time();
        $message['status'] = 'funding_request_details';
        $message['subject'] = 'Funding Request Details';
        $message['unqID'] = unqID();
        $message['to_mail'] = $to_mail;
        $message['investor'] = $pdfData['participant'];
        $message['amount'] = $pdfData['participant_funded_amount'];
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'FREDT'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mail = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $bcc_mails[] = $role_mail;
                    }
                    $message['to_mail'] = Arr::flatten($bcc_mails);
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $invest_data = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->where('merchants.id', $pdfData['merchant_id'])->select('max_participant_fund', DB::raw('sum(amount) as invested_amount'))->first();
        if ($invest_data) {
            if ($tot_inv_amnt_before_funding < $invest_data->max_participant_fund) {
                if ($invest_data->invested_amount >= $invest_data->max_participant_fund) {
                    $admin_mail = User::where('id', 1)->value('email');
                    if ($admin_mail != null) {
                        $message['title'] = '100% syndicated';
                        $message['merchant_name'] = $pdfData['merchant'];
                        $message['merchant_id'] = $pdfData['merchant_id'];
                        $message['timestamp'] = time();
                        $message['status'] = '100_percent_syndicated';
                        $message['subject'] = '100% syndicated';
                        $message['unqID'] = unqID();
                        $message['to_mail'] = $admin_mail;
                        $message['investor'] = $pdfData['participant'];
                        $message['content'] = 'Marketplace merchant  <a href='.url('admin/merchants/view/'.$pdfData['merchant_id']).'>'.$pdfData['merchant'].'</a> reaches 100% syndicated now';
                        try {
                            $email_template = Template::where([
                                ['temp_code', '=', 'MPSYF'], ['enable', '=', 1],
                            ])->first();
                            if ($email_template) {
                                $emailJob = (new CommonJobs($message));
                                dispatch($emailJob);
                                if ($email_template->assignees) {
                                    $template_assignee = explode(',', $email_template->assignees);
                                    $bcc_mails = [];
                                    foreach ($template_assignee as $assignee) {
                                        $role_mail = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                        $role_mail = array_diff($role_mail, [$admin_mail]);
                                        $bcc_mails[] = $role_mail;     
                                    }
                                    $message['to_mail'] = Arr::flatten($bcc_mails);
                                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                                    dispatch($emailJob);
                                }
                            }
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
            }
        }

        return $pdfURL;
    }

    public static function generatePdf(array $data, $fileName = '')
    {
        $user = \Auth::user();
        $pdf = PDF::loadView('investor.marketplace.fund_pdf', $data);
        $filePathS3 = 'Agreement_'.uniqid().'.pdf';
        $mid = $data['merchant_id'];
        $iid = $data['iid'];
        if ($fileName == '') {
            $fileName = 'marketplace/'.$mid.'/92'.$filePathS3;
        }
        $document = Document::create(['document_type_id' => 1, 'merchant_id' => $mid, 'investor_id' => $iid, 'title' => 'Participation Agreement', 'file_name' => $fileName, 'status' => 1]);
        $storage = Storage::disk('s3')->put($fileName, $pdf->output(), config('filesystems.disks.s3.privacy'));
        $url = url('api/investor/download/document/'.$document->id.'?token='.$user->getDownloadToken());

        return $url;
    }

    public static function createMerchantFundDetailTable()
    {
        if (! Schema::hasTable('merchant_fund_details')) {
            Schema::create('merchant_fund_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('merchant_id');
                $table->integer('pmnts')->nullable();
                $table->float('factor_rate', 12, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    public static function getMerchantDocuments(int $merchantId)
    {
        return Document::select('documents.id', 'documents.created_at', 'documents.title', 'documents.file_name', 'document_types.name as document_type')->leftJoin('document_types', 'documents.document_type_id', '=', 'document_types.id')->where('documents.merchant_id', $merchantId)->where('documents.global_status', 1)->get();
    }
}
