<?php

namespace App\Http\Resources;

use App\Merchant;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Settings;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    private $token;

    public function __construct($user, $token = null)
    {
        $this->resource = $user;
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->token = $this->getAccessToken();
        $role = optional($this->roles()->first()->toArray())['name'] ?? '';
        $encrypted_id = $this->encrypted_id;
        $company_name = User::where('id', $this->company)->value('name');

        if ($role == 'merchant') {
            $merchantId = $this->current_merchant_id;
            $merchantId = empty($merchantId) ? (optional($this->getMerchants()->pluck('id')->toArray())[0] ?? $this->merchant_id_m) : $merchantId;
            $merchant = Merchant::where('id', $merchantId)->first();
            $encrypted_id = $merchant ? $merchant->encrypted_id : $encrypted_id;
        }
        $two_factor_mandatory = optional($this->roles()->first()->toArray())['two_factor_required'] ?? '';
        $two_factor_mandatory = ($two_factor_mandatory==1) ? true : false;
        $two_factor_enabled = User::where('id', $this->id)->value('two_factor_secret');
        $data = [
            'id' => $this->id,
            'encrypted_id' => $encrypted_id,
            'token' => $this->token,
            'download-token' => $this->getDownloadToken(),
            'name' => $this->name,
            'email' => $this->email,
            'notification_email' => $this->notification_email,
            'notification_recurence' => $this->notification_recurence,
            'underwriting_fee' => $this->underwriting_fee,
            'underwriting_status' => $this->underwriting_status,
            'whole_portfolio' => $this->whole_portfolio,
            'logo' => $this->logo,
            'investor_type' => $this->investor_type,
            'marketplace_flag' => $this->investor_type == 5 ? true : false,
            'two_factor_mandatory' => $two_factor_mandatory,
            'two_factor_enabled' =>($two_factor_enabled!=null) ? true : false,
            'role' => $role,
            'company' => $this->company,
            'company_name'=>$company_name,
            'active_status' => $this->active_status,
            'created_at' => $this->created_at->unix(),
            'updated_at' => $this->updated_at->unix(),
            'login_board' => $this->login_board,
        ];

        if (optional($data)['role'] == 'merchant') {
            $merchants = $this->getMerchants();
            $data['merchants'] = collect($merchants)->map(function ($merchant) {
                return [
                        'id' => $merchant->id,
                        'name' => $merchant->name.'('.Carbon::parse($merchant->date_funded)->format('m-d-Y').')',
                    ];
            })
                ->toArray();
        }

        return $data;
    }

    public function with($request)
    {
        return [
            'status'=> true,
            'token' => $this->token,
            'maintenance_mode'=> true,
        ];
    }
}
