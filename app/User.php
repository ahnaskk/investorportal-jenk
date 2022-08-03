<?php

namespace App;

use function App\Helpers\modelQuerySql;
use App\Settings;
use Carbon\Carbon;
//fzl laravel8 use Database\Seeders\Role;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\HasApiTokens as PassportHasApiToken;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiToken;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Shetabit\Visitor\Traits\Visitor;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;
    use Visitor;
    use HasRoles,Notifiable, PassportHasApiToken,TwoFactorAuthenticatable;
    const INVESTOR_ROLE    = 2;
    const OVERPAYMENT_ROLE = 13;
    const CRM_ROLE         = 14;
    const MERCHANT_ROLE    = 7;
    const AGENT_FEE_ROLE   = 15;
    const LENDER_ROLE      = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $table = 'users';
    // public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['encrypted_id'];

    public function getNameAttribute()
    {
       $id = $this->id; 
       $user=DB::table('user_has_roles')->where('model_id',$id)->where('role_id',User::INVESTOR_ROLE)->count();

       if($user)
       {
          return strtoupper($this->attributes['name']);

       }else
       {
            return $this->attributes['name'];
       }
        
    }
    public function setNotificationEmailAttribute($notification_email)
    {
        $this->attributes['notification_email'] = preg_replace('/\\s+/', '', $notification_email);
    }

    public function getNotificationEmailAttribute($notification_email)
    {
        return preg_replace('/\\s+/', '', $notification_email);
    }

    public function getEncryptedIdAttribute()
    {
        $hashids = new Hashids();

        return $hashids->encode($this->id);
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function merchants()
    {
        return $this->belongsToMany(Merchant::class);
    }

    public function company_relation()
    {
        return $this->belongsTo(User::class,'company');
    }

    public function Company()
    {
        return $this->belongsTo(User::class,'company');
    }

    public function userDetails()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function userDetails2()
    {
        return $this->belongsTo(UserDetails::class);
    }

    public function investmentData()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function investmentData1()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function investorTransactionsC()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function investmentData2()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function merchantUser()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function participantPayment()
    {
        return $this->hasMany(PaymentInvestors::class);
    }

    public function investorTransactions()
    {
        return $this->hasMany(InvestorTransaction::class, 'investor_id');
    }
    public function investorRoiRate()
    {
        return $this->hasMany(InvestorRoiRate::class, 'user_id');
    }

    public function investorTransactions1()
    {
        return $this->hasMany(InvestorTransaction::class, 'investor_id');
    }

    public function investorTransactions2()
    {
        return $this->hasMany(InvestorTransaction::class, 'investor_id');
    }

    public function investorInterests()
    {
        return $this->hasMany(Interest::class, 'investor_id');
    }

    public static function getLenderReport()
    {
        $result = self::select('users.name', 'users.id', DB::raw('SUM(merchant_user.invest_rtr) as total_rtr'))
                        ->leftJoin('user_has_roles', 'user_has_roles.user_id', 'users.id')
                        ->leftJoin('merchants', 'merchants.lender_id', 'users.id')->where('role_id', 1)
                        ->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
                        ->groupBy('merchants.lender_id')
                        ->get()->toArray();
        if ($result) {
            return $result;
        }
    }

    /**
     * The whitelisted IPs that belong to the user.
     */
    public function firewalls()
    {
        return $this->belongsToMany(\App\Firewall::class)->withTimestamps();
    }

    /**
     * @param null $investorType
     * @param null $velocity
     * @param null $subAdmin
     * @param bool $liquidity
     * $liquidity investors should not be checked by company it's totally by the creatorId
     * @return mixed
     */
    public static function investors($investorType = null, $velocity = null, $subAdmin = null, $liquidity = false, $liquidityValue = 0, $excludeInvestorIds = [])
    {
        $userId = \Auth::user()->id;
        $investors = Role::whereName('investor')->first()->users()->select('users.*');
        $hide = ($liquidity) ? Settings::value('hide') : 0;
        if (\Auth::user()->hasRole(['company']) and ! $liquidity) {
            $investors->where('company', $userId);
        } elseif (\Auth::user()->hasRole(['company']) and ! $liquidity) {
            $investors->where('creator_id', $userId);
            $investors->where('active_status', 1);
        } else {
        }

        if (count($excludeInvestorIds) > 0) {
            $investors->whereNotIn('id', $excludeInvestorIds);
        }

        if ($hide == 1) {
            $investors->where('active_status', 1);
        }
        /**
         * TODO Clean It
         */
        if (! empty($investorType)) {
            $investors->where('investor_type', $investorType);
        }

        if (! empty($liquidityValue)) {
            $investors->whereHas('userDetails', function ($investor) use ($liquidityValue) {
                $investor->where('liquidity', '>', $liquidityValue);
            });
        }

        if (! empty($subAdmin)) {
            $investors->where('creator_id', $subAdmin);
        }

        return $investors;
    }

    public static function getInvestorType()
    {
        $types = [
            1 => 'Debt(65/20/15)',
            2 => 'Equity',
            3 => 'Debt(50/30/20)',
            4 => 'Debt',
            5 => 'Participant',
            6 => 'Other',
        ];

        return $types;
    }

    public static function getAdminRoleUsers()
    {
        return Role::whereIn('roles.name', ['admin', 'accounts', 'wire ach', 'editor'])
      ->select('users.*')
      ->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
      ->join('users', 'users.id', 'user_has_roles.model_id')
      ->get();
    }

    public function Role()
    {
        return Role::where('users.id', $this->id)
      ->select('roles.*')
      ->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
      ->join('users', 'users.id', 'user_has_roles.model_id')
      ->get();
    }

    public static function getLenders()
    {
        $userId = \Auth::user()->id;
        $lenderQuery = Role::whereName('lender')->first()->users();
        $lenderQuery->where('active_status', 1);

        return $lenderQuery;
    }

    public static function getAllCompanies()
    {
        return Role::whereName('company')->first()->users()->where('company_status',1);
    }

    public function sanctumTokens()
    {
        return $this->morphMany(Sanctum::$personalAccessTokenModel, 'tokenable');
    }

    public function createSanctumToken(string $name, array $abilities = ['*'])
    {
        $token = $this->sanctumTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(80)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->id.'|'.$plainTextToken);
    }

    public function getMerchants()
    {
        return Merchant::where('id', $this->merchant_id_m)
            ->union(Merchant::where('user_id', $this->id))
            ->orderByDesc('created_at')
            ->get();
    }

    public function getDownloadToken()
    {
        return $this->id.'-'.substr(md5($this->getAuthPassword().date('Y-m-d')), 0, 20);
    }

    public function getAccessToken($refresh = false)
    {
        $token = (! $refresh) ? UserMeta::findObject($this->id, '_access_token') : false;
        if (! $token or ($token and $token->updated_at->addDays(2)->getTimestamp() < Carbon::now()->getTimestamp())) {
            $this->sanctumTokens()->delete();
            $token = $this->createSanctumToken(request()->device_name.'-'.$this->email);
            $token = $token->plainTextToken;
            UserMeta::update_it($this->id, '_access_token', $token);
        } elseif ($token) {
            $token = $token->value;
        }

        return $token;
    }

    public function getLabelNameAttribute()
    {
        $labels = '';
        if ($this->label) {
            $this->label = str_replace('[', '', $this->label);
            $this->label = str_replace(']', '', $this->label);
            $explode = explode(',', $this->label);
            $labels = '';
            foreach ($explode as $key => $value) {
                $labels .= \App\Label::find($value)->name.',';
            }
        }

        return $labels;
    }

    public function getExcludedLabelNameAttribute()
    {
        $this->label = str_replace('[', '', $this->label);
        $this->label = str_replace(']', '', $this->label);
        $explode = explode(',', $this->label);
        $list = \App\Label::whereNotIn('id', $explode)->get();
        $labels = '';
        foreach ($list as $key => $value) {
            $labels .= $value->name.',';
        }
        $labels = rtrim($labels, ',');

        return $labels;
    }

    public function getExcludedLabelIdAttribute()
    {
        $this->label = str_replace('[', '', $this->label);
        $this->label = str_replace(']', '', $this->label);
        $explode = explode(',', $this->label);
        $list = \App\Label::whereNotIn('id', $explode)->get();
        $labels = [];
        foreach ($list as $key => $value) {
            $labels[] = $value->id;
        }

        return $labels;
    }
    public function reserveLiquidity()
    { 
        return $this->hasMany(ReserveLiquidity::class, 'user_id');
    }
    public static function companies() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->where('user_has_roles.role_id', 6);
        return $Self;
    }
    public static function OverpaymentId() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->where('user_has_roles.role_id', Self::OVERPAYMENT_ROLE);
        $Self = $Self->first(['users.id']);
        if($Self){
            return $Self->id;
        } 
        return '';
    }
    public static function AgentFeeId() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->where('user_has_roles.role_id', Self::AGENT_FEE_ROLE);
        $Self = $Self->first(['users.id']);
        if($Self){
            return $Self->id;
        } 
        return '';
    }
    public static function OverpaymentIds() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->where('user_has_roles.role_id', Self::OVERPAYMENT_ROLE);
        $Self = $Self->pluck('users.id','users.id');
        if(count($Self)){
            return $Self->toArray();
        } 
        return [];
    }
    public static function AgentFeeIds() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->where('user_has_roles.role_id', Self::AGENT_FEE_ROLE);
        $Self = $Self->pluck('users.id','users.id');
        if(count($Self)){
            return $Self->toArray();
        } 
        return [];
    }
    public static function AgentAndOverpaymentIds() {
        $Self = Self::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $Self = $Self->whereIn('user_has_roles.role_id', [Self::AGENT_FEE_ROLE,Self::OVERPAYMENT_ROLE]);
        $Self = $Self->pluck('users.id','users.id');
        if(count($Self)){
            return $Self->toArray();
        } 
        return [];
    }
}
