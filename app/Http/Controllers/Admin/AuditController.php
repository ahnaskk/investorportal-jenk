<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Merchant;
use App\UserDetails;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index($model, $id)
    {
        $type = $model;
        switch ($model) {
            case 'UserDetails':
            $model = \App\UserDetails::class;
            $ActualModel = UserDetails::find($id);
            break;
            case 'Merchant':
            $model = \App\Merchant::class;
            $ActualModel = Merchant::find($id);
            break;
            default:
            break;
        }
        $audits = Audit::whereauditable_type($model)->whereauditable_id($id)->orderBy('created_at', 'DESC')->get();

        return view('admin.audits', [
            'audits' => $audits,
            'ActualModel' => $ActualModel->toArray(),
            'type' => $type,
        ]);
    }
}
