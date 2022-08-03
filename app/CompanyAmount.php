<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Views\MerchantUserView;
use App\Merchant;
use DB;

class CompanyAmount extends Model
{
    protected $guarded = [];
    protected $table = 'company_amount';
    public static function CompanyFundDiffrence($merchant_id)
    {
        $CompanyFundDiffrence=MerchantUserView::join('company_amount', function($join) {
            $join->on('company_amount.company_id',  '=', 'company');
            $join->on('company_amount.merchant_id', '=', 'merchant_user_views.merchant_id');
        });
        $CompanyFundDiffrence=$CompanyFundDiffrence->groupBy('company');
        $CompanyFundDiffrence=$CompanyFundDiffrence->where('merchant_user_views.merchant_id',$merchant_id)->get([DB::raw('sum(amount) as company_funded'),'company','company_id','max_participant']);
        if(count($CompanyFundDiffrence)>0){
        $NewCompanyFundDiffrence = Self::leftjoin('merchant_user_views',function($join) {
            $join->on('merchant_user_views.company',  '=', 'company_amount.company_id');
            $join->on('merchant_user_views.merchant_id', '=', 'company_amount.merchant_id');
        })->join('users','users.id','company_amount.company_id')->groupBy('merchant_user_views.company')->where('company_amount.merchant_id',$merchant_id)->get([DB::raw('sum(IF((amount),amount,0)) as company_funded'),DB::raw('IF(merchant_user_views.company,merchant_user_views.company,company_id) as company'),'company_id','max_participant','users.name']);
        return $NewCompanyFundDiffrence;
          }
        return $CompanyFundDiffrence;
    }
    public static function FinalizeCompanyShare($merchant_id) {
        try {
            // $CompanyFundDiffrence=MerchantUserView::join('company_amount', function($join) {
            //     $join->on('company_amount.company_id',  '=', 'company');
            //     $join->on('company_amount.merchant_id', '=', 'merchant_user_views.merchant_id');
            // });
            
            $CompanyFundDiffrence=MerchantUserView::groupBy('company_id');
            $CompanyFundDiffrence=$CompanyFundDiffrence->where('merchant_user_views.merchant_id',$merchant_id)->get([DB::raw('sum(amount) as company_funded'),'company as company_id']);
            $companies = array_unique(array_column($CompanyFundDiffrence->toArray(),'company_id'));
            if(count($companies)>0){
                $merchant_companies = Self::where('merchant_id',$merchant_id)->whereNotIn('company_id',$companies)->pluck('company_id')->toarray(); 
            }else{
                $merchant_companies = Self::where('merchant_id',$merchant_id)->pluck('company_id')->toarray();
            }
           
            foreach ($CompanyFundDiffrence as $key => $value) {
                Self::updateOrCreate([
                    'merchant_id'     =>$merchant_id,
                    'company_id'      =>$value->company_id,
                ],[
                    'max_participant' =>$value->company_funded
                ]);
            }
            foreach($merchant_companies as $com){
                Self::updateOrCreate([
                    'merchant_id'     =>$merchant_id,
                    'company_id'      =>$com,
                ],[
                    'max_participant' =>0
                ]);

            }
            $max_participant_fund=Self::where('merchant_id',$merchant_id)->sum('max_participant');
            $Merchant=Merchant::find($merchant_id);
            if($max_participant_fund>$Merchant->funded){
              $return['result']='maximum participant fund should not be greater than merchant funded amount';  
            }else{
            $max_participant_fund_per=$max_participant_fund/$Merchant->funded*100;
            $Merchant->max_participant_fund=$max_participant_fund;
            $Merchant->max_participant_fund_per=$max_participant_fund_per;
            if($max_participant_fund_per<100){
                $Merchant->agent_fee_applied=0;
            }
            $Merchant->save();
            $return['result']='success';
           }
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
        }
        return $return;
    }
    
    public function Company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
    
    public function Merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}
