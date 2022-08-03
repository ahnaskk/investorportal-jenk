<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PusherController extends Controller
{
    protected $user = false;

    public function __construct()
    {
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
    }

    public function getBeamsToken(Request $request)
    {
        $errors = '';
        try {
            $beamsClient = new \Pusher\PushNotifications\PushNotifications(['instanceId' => config('services.pusher.beams_instance_id'), 'secretKey' => config('services.pusher.beams_secret_key')]);
            $beamsToken = $beamsClient->generateToken("{$this->user->id}");
            if ($beamsToken) {
                return new SuccessResource($beamsToken);
            } else {
                $errors = 'Inconsistent request';
            }
        } catch (\Exception $e) {
            $errors = $e->getMessage();

            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }

        return response()->json(['status' => false, 'errors' => $errors], 401);
    }
}
