<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSettings;
use App\Library\Facades\SettingHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function __construct()
    {
    }

    public function  systemSettingUpdate()
    {
        $result = SettingHelper::systemSettingIndex();

        return view('admin.systemSettings', $result);
    }

    public function systemSettingUpdateAction(AdminSettings $request)
    {
        try {
            DB::beginTransaction();
            $result = SettingHelper::systemSettingUpdateAction($request);
            if (!$result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }
        return redirect()->back()->with($message_type, $message);
    }

    public function accountViewStatusUpdate(AdminSettings $request)
    {
        try {
            DB::beginTransaction();
            $result = SettingHelper::accountViewStatusUpdate($request);
            if (!$result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }
        return redirect()->back()->with($message_type, $message);
    }

    public function paymentModeUpdateAction(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = SettingHelper::paymentModeUpdateAction($request);
            if (!$result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }
        return redirect()->back()->with($message_type, $message);
    }
    public function revertDateModeUpdateAction(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = SettingHelper::revertDateModeUpdateAction($request);
            if (! $result['status']) {
                throw new Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message      = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();

             return redirect()->back()->withErrors($message);
         }
         return redirect()->back()->with($message_type, $message);
     }

    public function settingUpdateAction(AdminSettings $request)
    {
        try {
            DB::beginTransaction();
            $result = SettingHelper::settingUpdateAction($request);
            if (!$result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message = $result['message'];
            if ($result['updation']) {
                DB::commit();
                return redirect()->back()->with($message_type, $message);
            } else {
                DB::commit();
                return view('admin.defaultSetting', $result['result']);
            }
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }
    }
    public function twoFactorRequiredUpdation(Request $request){
        try {
            DB::beginTransaction();
            $result = SettingHelper::twoFactorRequiredUpdation($request);
            if (! $result['status']) {
                throw new Exception($result['message'], 1);
            }
            $message_type = 'message';
            $message      = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();

             return redirect()->back()->withErrors($message);
         }
         return redirect()->back()->with($message_type, $message);


     }
    
}
