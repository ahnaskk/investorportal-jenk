<?php

namespace App\Http\Resources;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;


class SuccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    public function with($request)
    {
        $two_factor_required = DB::table('roles')->where('name','investor')->value('two_factor_required');
        return [
            'status'=> true,
            'maintenance_mode'=> true,
            'login_dashboard'=>(Auth::user()) ? Auth::user()->login_board : '',
            'two_factor_mandatory'=>($two_factor_required==1 ? true :false)
        ];
    }
}
