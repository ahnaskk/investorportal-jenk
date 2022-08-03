<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserActivityLog extends Model
{
    use SoftDeletes;
    protected $table = 'user_activity_logs';

    protected $fillable = [
        'user_id',
        'object_id',
        'type',
        'action',
        'detail',
        'investor_id',
        'merchant_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id')->withTrashed();
    }
    public function investor()
    {
        return $this->belongsTo(\App\User::class, 'investor_id')->withTrashed();
    }

    public static function logTypes()
    {
        // Types showing in user activity log filter
        $logTypes = [];
        $log_type_list = [
            'faq',
            'investor',
            'login',
            'payment',
            'merchant',
            'user',
            'user_details',
            'company_amount',
            'merchant_user',
            'user_merchant',
            'merchant_bank_account',
            'merchant_note',
            'merchant_ach_term',
            'payment_pause',
            'payment_resume',
            'ach_payment',
            'ach_request',
            'velocity_fee',
            'investor_ach_request',
            'lender',
            'bank_account',
            'company'
        ];
        foreach ($log_type_list as $item) {
            $logTypes[$item] = self::prettyStatus($item);
            if ($item == 'merchant_user') {
                $logTypes[$item] = 'Merchant Investor';
            }
        }
        asort($logTypes);
        return $logTypes;
    }

    public static function logType()
    {
        // Types showing in merchant activity log filter
        $logTypes = [];
        $log_type_list = [
            'payment',
            'merchant',
            'company_amount',
            'merchant_user',
            'merchant_note',
            'merchant_bank_account',
            'merchant_ach_term',
            'payment_pause',
            'payment_resume',
            'ach_payment',
            'ach_request',
            'velocity_fee',

        ];
        foreach ($log_type_list as $item) {
            $logTypes[$item] = self::prettyStatus($item);
            if ($item == 'merchant_user') {
                $logTypes[$item] = 'Merchant Investor';
            }
        }

        return $logTypes;
    }

    public static function logTypePrettyStatus($type)
    {
        $logTypes = self::logTypes();

        return $logTypes[$type] ?? self::prettyStatus($type);
    }

    public static function prettyStatus($status)
    {
        $status = str_replace('_', ' ', $status);

        return ucwords($status);
    }

    public static function activity_user()
    {
        $users = User::where('email', '!=', '')->select('id', 'name')->get();

        return self::transformer($users);
    }

    public static function activity_merc_inv()
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $investors = collect(User::investors()->withTrashed()->get());
        $merchant_user = MerchantUser::whereIn('user_id', $investors->pluck('id')->toArray())->groupBy('merchant_id')->pluck('merchant_id')->toArray();
        $merchants = collect(Merchant::withTrashed()->whereIn('id', $merchant_user)->get());
        $result = $investors->merge($merchants);
        $listing = [];
        if ($investors) {
            foreach ($investors as $user) {
                $listing[$user->id.'-investor'] = $user->name;
            }
        }
        if ($merchants) {
            foreach ($merchants as $merc) {
                $listing[$merc->id.'-merchant'] = $merc->name;
            }
        }

        return $listing;
    }

    public static function activity_actions()
    {
        return [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
        ];
    }

    public static function transformer($users)
    {
        $listing = [];
        if ($users) {
            foreach ($users as $user) {
                $listing[$user->id] = $user->name;
            }
        }

        return $listing;
    }

    public static function investors()
    {
        return User::investors()->withTrashed()->pluck('name', 'id')->toArray();
    }

    public static function getNameFromDB($table_name, $id, $label)
    {
        if (gettype($id) != 'array') {
            $id = [$id];
        }

        return DB::table($table_name)->whereIn('id', $id)->select($label)->value($label);
    }
}
