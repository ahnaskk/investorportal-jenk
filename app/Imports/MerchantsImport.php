<?php

namespace App\Imports;

use App\Merchant;
use App\MerchantDetails;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\User;

class MerchantsImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
     
        if (count($row)==7) {
             $crm = MerchantDetails::where('crm_id', $row[0]);

             if($crm->count()>0)
             {
                $crmArray = [

                 'crm_id'=>$row[0],
                 'negative_days'=>$row[1],
                 'no_of_deposit'=>$row[2],
                 'nsf'=>$row[3],
                 'fico_score_primary'=>$row[4],
                 'fico_score_secondary'=>$row[5],
                 'withhold_percentage'=>$row[6]
              ];

            MerchantDetails::where('crm_id',$row[0])->update($crmArray);

             }
            

        } else 

        {
            if (isset($row[5])) {
                $states = DB::table('us_states')->select('id')->where('state_abbr', '=', $row[5])->first();
            }

            if (isset($row[35])) {
                $owner_states = DB::table('us_states')->select('id')->where('state_abbr', '=', $row[35])->first();
            }

            if (isset($row[48])) {
                $partner_states = DB::table('us_states')->select('id')->where('state_abbr', '=', $row[48])->first();
            }

            $merchant = Merchant::where('id', $row[1]);

            if ($merchant->count() > 0) {
             
                 $m_d = $merchant->first()->toArray();

        $updateArray = [

           'first_name'=>$row[2],
           'last_name'=>$row[3],
           'phone'=>$row[9],
           'cell_phone'=>$row[10],
           'city'=>$row[4],
           'state_id'=>isset($states) ? $states->id : 0,
           'zip_code'=>$row[8],
         ];

            $update = Merchant::where('id',$row[1])->update($updateArray);

            if($row[12])
            {
                $user=User::where('id',$m_d['user_id']);

                if($user->count()>0)
                {
                   $check= $user->where('email',$row[12]);
                   if($check->count()==0)
                   {
                       $update=$user->update(['email'=>$row[12]]);
                   }

                  
                }else
                {  
                     $check_email=User::where('email',$row[12]);
                     if($check_email->count()==0)
                     {
                         $password = $this->generateRandomString(7);
                         $user=User::create(['email'=>$row[12],'name'=>$m_d['name'],'password'=>$password]);
                         session_set('user_role', 'user_merchant');
                         $user->assignRole('merchant');
                         $merchant->update(['user_id'=>$user->id]);

                     }

                  
                }

            }

               // if ($update) {
                

            $crmArray = [
                 'merchant_id'=>$row[1],
                 'crm_id'=>$row[0],
                 'work_phone'=>$row[11],
                 'exact_legal_company_name'=>$row[14],
                 'physical_address'=>$row[16],
                 'fax'=>$row[17],
                 'federal_tax_id'=>$row[18],
                 'date_business_started'=>date('Y-m-d',strtotime($row[19])),
                 'ownership_length'=>! empty($row[20]) ? $row[20] : 0,
                 'website'=>$row[21],
                 'entity_type'=>$row[22],
                 'product_sold'=>$row[24],
                 'use_of_proceeds'=>$row[25],
                 'annual_revenue'=>! empty($row[26]) ? $row[26] : 0,
                 'owner_first_name'=>$row[30],
                 'owner_last_name'=>$row[31],
                 'ownership_percentage'=>! empty($row[32]) ? $row[32] : 0,
                 'home_address'=>$row[33],
                 'owner_city'=>$row[34],
                 'owner_state_id'=>isset($owner_states) ? $owner_states->id : 0,
                 'owner_zip'=>$row[37],
                 'ssn'=>$row[38],
                 'dob'=>isset($row[39])?date('Y-m-d',strtotime($row[39])):'',
                 'partner_first_name'=>$row[43],
                 'partner_last_name'=>$row[44],
                 'partner_ownership_percentage'=>! empty($row[45]) ? $row[45] : 0,
                 'partner_home_address'=>$row[46],
                 'partner_city'=>$row[47],
                 'partner_state_id'=>isset($partner_states) ? $partner_states->id : 0,
                 'partner_zip'=>$row[50],
                 'partner_ssn'=>$row[51],
                 'partner_dob'=>($row[52])?date('Y-m-d',strtotime($row[52])):'',
                 'partner_home_hash'=>$row[53],
                 'partner_cell_hash'=>$row[54],
                 'buy_rate'=>$m_d['factor_rate'] - $m_d['commission'] / 100,
                 'lead_source'=>$row[59],
                 'disposition'=>$row[60],
                 'marketing_notification'=>$row[61],
                 'owner_credit_score'=>! empty($row[62]) ? $row[62] : 0,
                 'partner_credit_score'=>! empty($row[63]) ? $row[63] : 0,
                 'physical_address2'=>$row[64],
                 'monthly_revenue'=>! empty($row[65]) ? $row[65] : 0,
                 'owner_cell2'=>$row[66],
                 'owner_email'=>$row[67],
                 'owner_address2'=>$row[68],
                 'partner_cell2'=>$row[69],
                 'partner_email'=>$row[70],
                 'partner_address2'=>$row[71],
                 'requested_amount'=>! empty($row[72]) ? $row[72] : 0,
                 'campaign'=>$row[73],
                 'broker_commission'=>! empty($row[74]) ? $row[74] : 0,
                 'created_date'=>isset($row[77])?date('Y-m-d H:i',strtotime($row[77])):'',
                 'payback_amount'=>! empty($row[81]) ? $row[81] : 0,
                 'terms_in_days'=>! empty($row[82]) ? $row[82] : 0,
                 'lender_email'=>$row[84],
            ];

                    MerchantDetails::where('merchant_id',$row[1])->update($crmArray);
                //}
            }
        }
    }
}
