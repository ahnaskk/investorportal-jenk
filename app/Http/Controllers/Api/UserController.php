<?php

namespace App\Http\Controllers\Api;

use App\CompanyAmount;
use App\Events\UserHasAssignedInvestor;
use App\Http\Controllers\Controller;
use App\Industries;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantDetails;
use App\MerchantUser;
use App\MNotes;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\Template;
use App\User;
use App\UserDetails;
use App\SubStatus;
use Carbon\Carbon;
use Exception;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PayCalc;
use InvestorHelper;
use CRMHelper;

class UserController extends Controller
{
    public $successStatus = 200;

    public function __construct(IMerchantRepository $merchant, IRoleRepository $role)
    {
        $this->merchant = $merchant;
        $this->role = $role;
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = $request->user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $user->api_token = $success['token'];
            $user->save();

            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), ['name' => 'required', 'email' => 'required|email', 'password' => 'required', 'c_password' => 'required|same:password']);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return response()->json(['success' => $success], $this->successStatus);
    }

    public function details(Request $request)
    {
        $user = $request->user();

        return response()->json(['success' => $user], $this->successStatus);
    }
    public function merchantPaymentDetailsAction(Request $request)
    {
       try
       {
          $result=CRMHelper::merchantPaymentDetails($request);
          if($result['status']!='success') throw new Exception($result['msg'], 1);
          return response()->json(['status' => $result['status'], 'result' => $result['result']]);
          
       }catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }

    }
    public function getMerchantDetails(Request $request)
    {
        try {

             $result=CRMHelper::merchantDetails($request);
              if($result['status']!='success') throw new Exception($result['msg'], 1);
              return response()->json(['status' => $result['status'], 'result' => $result['result']]);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }
    }

    public function update_CRMIDAction(Request $request)
    {
       try{
        DB::beginTransaction();
        $result=CRMHelper::updateCRMID($request);
        if($result['status']!='success') throw new Exception($result['msg'], 1);
        DB::commit();  
        return response()->json(['status' => $result['status'], 'result' => $result['msg']]);

       } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'result' => $e->getMessage()]);
        }

    }

    public function addMerchantNotes(Request $request)
    {

        try{
            DB::beginTransaction();
           $result=CRMHelper::addMerchantNotes($request);
            DB::commit();  
            if($result['status']!='success') throw new Exception($result['error'], 1);
            return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);
            
        }catch (\Exception $e) {
             DB::rollback();
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }
    }

    public function merchantBankAccountUpdate(Request $request)
    {
         try{
            DB::beginTransaction();
            $result=CRMHelper::updateMerchantBankAccount($request);
             if($result['status']!='success') throw new Exception($result['msg'], 1);
             DB::commit();  
            return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);

         }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }
    }

    public function merchantBankAccountCreate(Request $request)
    {
        try{
             DB::beginTransaction();
          $result=CRMHelper::createMerchantBankAccount($request);
          if($result['status']!='success') throw new Exception($result['msg'], 1);
           DB::commit();  
           return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);

        }catch (\Exception $e) {
             DB::rollback();
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }

    }

    public function merchantBankAccountDelete(Request $request)
    {
         try {
            DB::beginTransaction();
             $result=CRMHelper::deleteMerchantBankAccount($request);
              if($result['status']!='success') throw new Exception($result['msg'], 1);
            DB::commit();  
            return response()->json(['status' => $result['status'], 'msg' =>$result['msg']]);
         }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }

    }

    public function merchantUpdate(Request $request)
    {
        try {
            DB::beginTransaction();
            $result=CRMHelper::merchantUpdate($request);
            if($result['status']!='success') throw new Exception($result['error'], 1);
            DB::commit();    
           return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);

        } catch (\Exception $e) {
              DB::rollback();
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
        }
    }

    public function merchantCreate(Request $request)
    {
      try
         {
           DB::beginTransaction();
           $result=CRMHelper::merchantCreate($request);
           if($result['status']!='success') throw new Exception($result['error'], 1);
           DB::commit();    
           return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);
    }
    catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
         }
    
    }

    public function getErrorMessages($errors)
    {
        return ['status' => false, 'errors' => $errors];
    }

    public function create_investor(Request $request)
    {
        try
        {
              DB::beginTransaction();
              $result=CRMHelper::investorCreate($request);
              if($result['status']!='success') throw new Exception($result['error'], 1);
              DB::commit();    
             return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);

        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
         }
           
    }
    public function update_investor(Request $request)
    {
        try
        {
             DB::beginTransaction();
             $result=CRMHelper::investorUpdate($request);
            
             if($result['status']!='success') throw new Exception($result['error'], 1);
              DB::commit();    
             return response()->json(['status' => $result['status'], 'msg' => $result['msg'], 'data' => $result['data']]);

        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
         }

    }

    public function getParticipants(Request $request)
    {
        try{

            $result=CRMHelper::getParticipants($request); 
            if($result['status']!='success') throw new Exception($result['error'], 1);
             return response()->json(['status' => $result['status'], 'result' => $result['result']]);

        }catch (\Exception $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
        }

    }

    public function mapParticipants(Request $request)
    {
        try
        {
              $result=CRMHelper::mapParticipants($request);
              if($result['status']!='success') throw new Exception($result['error'], 1);
              return response()->json(['status' => $result['status'], 'msg' => $result['msg']]);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
        }

    }

 public function assign_participants(Request $request)
    {
        try {
             DB::beginTransaction();
             $result=CRMHelper::assignParticipants($request);
             if($result['status']!='success') throw new Exception($result['msg'], 1);
              DB::commit();    
             return response()->json(['status' => $result['status1'], 'msg' => $result['msg']]);

         }catch (\Exception $e) {
             DB::rollback();
            return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }
    

    }
}
