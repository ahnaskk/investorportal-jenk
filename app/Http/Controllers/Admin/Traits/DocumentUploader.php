<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Document;
use App\InvestorDocuments;
use App\Merchant;
use App\User;
use DataTables;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use MTB;
use Yajra\DataTables\Html\Builder;
use Carbon\Carbon;
trait DocumentUploader
{
    public function investorDocuments($mid, $iid, Request $request, Builder $tableBuilder)
    {
        $valid_merchant = Merchant::where('id', $mid)->count();
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::adminDocumentsView($iid, $mid);
        }
        $merchant = Merchant::where('id', $mid)->first();
        $page_title = 'Merchant Documents';
        $tableBuilder = $tableBuilder->columns(MTB::adminDocumentsView($iid, $mid, true));
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);

        return view('admin.merchants.documents', compact('page_title', 'tableBuilder', 'iid', 'mid', 'valid_merchant','merchant'));
    }

    public function investorAgreementDocs(Request $request)
    {
        $mid = $request['mid'];
        $folderPath = public_path('merchant_documents/'.$mid);
        $docs = [];
        if (file_exists($folderPath)) {
            $filesInFolder = File::files($folderPath);
            foreach ($filesInFolder as $path) {
                $docs[] = pathinfo($path);
            }
        }
        print_r($docs[0]);
        exit;
        $qry = Merchant::select(['name', 'id'])->get();

        return Datatables::of($docs)->make(true);
    }

    public function uploadInvestorDocument($iid, Request $request, Builder $tableBuilder)
    {
        $Investor=User::find($iid);
        if(!$Investor){
            $request->session()->flash('error','Invalid User Id');
            return redirect(route("admin::investors::index"));
        }
        $investor_valid = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')->join('roles', 'roles.id', '=', 'user_has_roles.role_id')->where('users.id', $iid)->where('roles.id', User::INVESTOR_ROLE)->first();
        $valid = 0;
        if ($investor_valid) {
            $valid = 1;
        }
        if ($request->user()->hasRole(['company'])) {
            $id1 = $request->user()->id;
            $subinvestors = [];
            $inv = $this->role->allInvestors();
            $subadmininvestor = $inv->where('company', $id1);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
            if (! in_array($iid, $subinvestors)) {
                return redirect()->to('admin/investors/')->withErrors('This Investor not a company based');
            }
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorDocumentsView($iid);
        }
        $page_title = 'Investor Document';
        $tableBuilder = $tableBuilder->columns(MTB::investorDocumentsView($iid, true));
        if (empty($permission)) {
            if (! $request->user()->hasRole(['company'])) {
                $investorAccess = User::where('id', $iid)->where('creator_id', $userId)->first();
                if (empty($investorAccess)) {
                    return view('admin.permission_denied');
                }
            }
        }

        return view('admin.merchants.upload_documents', compact('page_title', 'tableBuilder', 'iid', 'valid'));
    }

    public function investorDocumentUpload($mid, $iid, Request $request)
    {
        $fileName = "marketplace/$mid/$iid".$this->generateFileName($request->file->getClientOriginalExtension());
        $storge = Storage::disk('s3')->put($fileName, file_get_contents($request->file), config('filesystems.disks.s3.privacy'));
        $extension = $request->file->getClientOriginalExtension();
        $url = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
        $merchants = Merchant::select('name', 'funded')->where('id', $mid)->first()->toArray();
        if ($storge) {
            $data = ['document_type_id' => 1, 'merchant_id' => $mid, 'investor_id' => $iid, 'title' => $request->file->getClientOriginalName(), 'file_name' => $fileName, 'status' => 1];
            $message['title'] = ' Uploaded New Document ';
            $message['header'] = 'Document uploaded';
            $message['merchant_name'] = $merchants['name'];
            $message['funded'] = $merchants['funded'];
            $message['url'] = $url;
            $message['filename'] = $fileName;
            $message['extension'] = $extension;
            $message['type'] = 'documents';
            \EventHistory::pushNotifyAdmin($message, $iid);
            $result = Document::create($data);
            if ($result) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'error']);
            }
        }
    }

    public function investorDocumentUpdate($mid,$iid, $documentId, Request $request)
    {
        if ($document = Document::find($documentId)) {
            $upload_status = $document->update(['title' => $request->title, 'document_type_id' => $request->type]);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function investorDocumentDelete($merchantId, $investorId, $documentId)
    {
        if ($document = Document::find($documentId)) {
            Storage::disk('s3')->delete($document->file_name);
            $document->delete();

            return response()->json(['message' => 'Delete successfully', 'status' => 'success']);
        } else {
            return response()->json(['message' => 'Can not delete this item, please contact administrator', 'status' => 'error']);
        }
    }

    public function investorDocumentDeleteByAdmin($investorId, $documentId)
    {
        if ($document = InvestorDocuments::find($documentId)) {
            Storage::disk('s3')->delete($document->file_name);
            $document->delete();
        }
    }

    public function DocumentDeleteByAdmin(Request $request)
    {
        $documentId = $request->doc_id;
        if ($document = InvestorDocuments::find($documentId)) {
            Storage::disk('s3')->delete($document->file_name);
            $document->delete();

            return response()->json(['message' => 'Delete successfully', 'status' => 'success']);
        }
    }

    public function viewInvestorDoc($mid, $iid, $docid)
    {
        if ($document = Document::find($docid)) {
            return $this->viewFiles($document->file_name);
        }
    }

    public function viewInvestorDocument($iid, $docid)
    {
        if ($document = InvestorDocuments::find($docid)) {
            return $this->viewFiles($document->file_name);
        }
    }

    public function documentInvestmentUpdate($iid, $docid, Request $request)
    {
        $type = $request->type;
        if ($request->type == 9) {
            $type = DB::table('document_types')->insertGetId(['name' => $request->other_type]);
        }
        if ($document = InvestorDocuments::find($docid)) {
            $status = $document->update(['title' => $request->title, 'document_type_id' => $type]);

            return response()->json(['status' => 'success', 'message' => 'document updated', 'other_type_id' => $type, 'other_type' => $request->other_type, 'doc_id' => $docid, 'type' => $request->type]);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function viewWeeklyReportDocument($docid)
    {
        if ($document = DB::table('statements')->find($docid)) {
            return $this->viewFiles($document->file_name);
        }
    }

    public function marketplaceDocuments($mid, Request $request, Builder $tableBuilder)
    {
        $valid_merchant = Merchant::where('id', $mid)->count();
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::adminMarketPlaceDocumentsView($mid);
        }
        $page_title = 'Deal Documents';
        $tableBuilder = $tableBuilder->columns(MTB::adminMarketPlaceDocumentsView($mid, true));
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n           var info = this.dataTable().api().page.info();\n           var page = info.page;\n           var length = info.length;\n           var index = (page * length + (iDataIndex + 1));\n           $('td:eq(0)', nRow).html(index).addClass('txt-center');\n         }"]);
        $merchant = Merchant::where('id', $mid)->first();
        if (empty($permission)) {
            $merchantAccess = Merchant::where('id', $mid)->where('creator_id', $userId)->first();
            if (empty($merchantAccess)) {
                return view('admin.permission_denied');
            }
        }

        return view('admin.merchants.marketplace_documents', compact('page_title', 'tableBuilder', 'mid', 'valid_merchant', 'merchant'));
    }

    public function marketplaceDocumentUpload($mid, Request $request)
    {
        $fileName = "marketplace/$mid/".$this->generateFileName($request->file->getClientOriginalExtension());
        $user = $request->user();
        $upload = Storage::disk('s3')->put($fileName, file_get_contents($request->file), config('filesystems.disks.s3.privacy'));
        $extension = $request->file->getClientOriginalExtension();
        $url = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
        $merchant = Merchant::select('name', 'funded')->where('id', $mid)->first()->toArray();
        if ($upload) {
            $data = ['document_type_id' => 1, 'merchant_id' => $mid, 'admin_id' => $user->id, 'title' => $request->file->getClientOriginalName(), 'file_name' => $fileName, 'global_status' => 1, 'investor_id' => 0, 'status' => 1, 'creator_id' => $request->user()->id];
            $message['content'] = $user->name.' Uploaded New Document ( '.$mid.' )';
            $message['title'] = 'Document uploaded';
            $message['merchant_name'] = $merchant['name'];
            $message['funded'] = $merchant['funded'];
            $message['url'] = $url;
            $message['url'] = 'documents';
            $message['filename'] = $fileName;
            $message['extension'] = $extension;
            \EventHistory::pushNotifyAdmin($message);
            $result = Document::create($data);
            if ($result) {
                return response()->json(['message' => 'Uploaded successfully', 'status' => 'success']);
            } else {
                return response()->json(['message' => 'Not upload successfully', 'status' => 'error']);
            }
        } else {
            return response()->json(['message' => 'Not upload successfully', 'status' => 'error']);
        }
    }

    public function investorDocumentUploadByAdmin($iid, Request $request)
    {
        $fileName = "documents/$iid/".$this->generateFileName($request->file->getClientOriginalExtension());
        $upload = Storage::disk('s3')->put($fileName, file_get_contents($request->file), config('filesystems.disks.s3.privacy'));
        if ($upload) {
            $data = ['document_type_id' => 1, 'investor_id' => $iid, 'title' => $request->file->getClientOriginalName(), 'file_name' => $fileName, 'status' => 1];
            $result = InvestorDocuments::create($data);
            if ($result) {
                return response()->json(['message' => 'success', 'status' => 'success']);
            } else {
                return response()->json(['message' => 'error', 'status' => 'error']);
            }
        } else {
            return response()->json(['message' => 'error', 'status' => 'error']);
        }
    }

    public function marketplaceDocumentUpdate($mid, $documentId, Request $request)
    {
        $type = $request->type;
        
        if ($request->type == 9) {
            $type = DB::table('document_types')->insertGetId(['name' => $request->other_type]);
        }
        if ($document = Document::find($documentId)) {
            $document->update(['title' => $request->title, 'document_type_id' => $type]);

            return response()->json(['status' => 'success', 'message' => 'document updated', 'other_type_id' => $type, 'other_type' => $request->other_type, 'doc_id' => $documentId, 'type' => $request->type]);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function marketplaceDocumentDelete($merchantId, $documentId)
    {
        if ($document = Document::find($documentId)) {
            Storage::disk('s3')->delete($document->file_name);
            $document->delete();
        }
    }

    public function viewMarketplaceDoc($mid, $docid)
    {
        if ($document = Document::find($docid)) {
            return $this->viewFiles($document->file_name);
        }
    }

    private function viewFiles($fileName)
    {
        try {
            $file_contents = Storage::disk('s3')->get('/'.$fileName);

            return $response = response($file_contents, 200, ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="'.$fileName.'"']);
        } catch (FileNotFoundException $e) {
            abort(404);

            return new ErrorResource(['message' => 'Oops! Sorry file has not found!']);
        } catch (\ErrorException $e) {
            abort(404);

            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    private function generateFileName($extension)
    {   
        $n_digit_random = $this->n_digit_random(64);
        return base64_encode('doc_'.$n_digit_random.''.time()).'.'.$extension;
    }

    public function uploadDocumnt(Builder $tableBuilder)
    {
        $page_title = 'Upload Document';
        $tableBuilder->ajax(['url' => route('admin::merchant_investor::all_documents')]);
        $tableBuilder->columns([['data' => 'rownum', 'name' => 'title', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Title']]);

        return view('admin.merchants.upload_documents', compact('page_title', 'tableBuilder', 'iid'));
    }
    function n_digit_random($digits) {
        $temp = "";
      
        for ($i = 0; $i < $digits; $i++) {
          $temp .= rand(0, 9);
        }
      
        return (int)$temp;
      }
      
}
