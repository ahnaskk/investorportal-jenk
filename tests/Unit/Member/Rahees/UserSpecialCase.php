<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;
use App\User;
use App\Settings;
use App\Label;
use App\Merchant;
use App\MerchantUser;
use App\CompanyAmount;
use App\InvestorTransaction;
use App\ParticipentPayment;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use App\Helpers\Report\LiquidityReportHelper;
use App\Library\Repository\RoleRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
// Response::HTTP_NOT_FOUND = 404;
// Response::HTTP_CREATED = 201;
// Response::HTTP_BAD_REQUEST = 400;
// Response::HTTP_OK = 200;
// Response::HTTP_FOUND = 302;
it('freshdb', function () {
    $this->artisan('migrate:fresh');
    // uses(RefreshDatabase::class);
    $this->artisan('db:seed');
    $this->assertTrue(true);
})
->skip()
;
it('redirects to dashboard', function () {
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::dashboard::index'))
    ->assertSee($user->name)
    ->assertStatus(Response::HTTP_OK);
});
// test('company',function(){
//     $user = User::first();//Admin
//     $this
//     ->actingAs($user)
//     ->get(route('admin::sub_admins::index'))
//     ->assertStatus(Response::HTTP_OK);
//     $faker = Faker::create();
//     Storage::fake('local');
//     $companies=[
//         'company',
//         'Velocity',
//         'Syndicates',
//         // 'outside funding'
//     ];
//     foreach ($companies as $key => $value) {
//         echo "\n $value Created";
//         $Duplicate=User::where('name',$value)->first();
//         if(!$Duplicate){
//             $user_count_before = User::count();
//             $file = UploadedFile::fake('storage')->create('file.jpg');
//             $data=[
//                 'name'                  => $value,
//                 'email'                 => $faker->email,
//                 'brokerage'             => 1,
//                 'company_status'        => 1,
//                 'active_status'         => 1,
//                 'password'              => 'asdasdasd',
//                 'password_confirmation' => 'asdasdasd',
//                 'logo'                  => $file,
//             ];
//             $Investor=User::where('name',$data['name'])->first();
//             if(!$Investor){
//                 $this
//                 ->actingAs($user)
//                 ->post(route('admin::sub_admins::storeCreate'),$data)
//                 ->assertRedirect(route('admin::sub_admins::index'))
//                 ->assertStatus(Response::HTTP_FOUND)
//                 ->assertSessionHasNoErrors()
//                 ->withSession(['message' => 'New Company created!'])
//                 ;
//                 $user_count_after = User::count();
//                 $inserted_count   = $user_count_after - $user_count_before;
//                 $this->assertTrue($inserted_count==1);
//             }
//         }
//     }
// })
// ->skip()
//;
// test('investor', function () {
//     $user = User::first();//Admin
//     $this
//     ->actingAs($user)
//     ->get(route('admin::investors::index'))
//     ->assertStatus(Response::HTTP_OK);
//     $this
//     ->actingAs($user)
//     ->get(route('admin::investors::create'))
//     ->assertStatus(Response::HTTP_OK);
//     $companies=LiquidityReportHelper::allSubAdmin();
//     $this->assertTrue($companies->count()>0);
//     $companies=$companies->pluck('name','id');
//     foreach ($companies as $key => $company) {
//         echo "\n            $company company";
//         for ($i=0; $i < 4 ; $i++) {
//             echo "\n $i investor";
//             $faker = Faker::create();
//             $user_count_before = User::count();
//             $data=[
//                 'name'                  => $company.' Investor '.$i,
//                 'contact_person'        => $company.' Investor '.$i,
//                 'cell_phone'            => $faker->phoneNumber,
//                 's_prepaid_status'      => 2,
//                 'role_id'               => 2,
//                 'investor_type'         => 2,
//                 'company'               => $key,
//                 'email'                 => $faker->email,
//                 'notification_email'    => $faker->email,
//                 'password'              => 'asdasd',
//                 'password_confirmation' => 'asdasd',
//                 'file_type'             => 1,
//                 'show_name_mid'         => 'on',
//                 'active_status'         => 1,
//                 'login_board'           =>'new'
//             ];
//             $Investor=User::where('name',$data['name'])->first();
//             if(!$Investor){
//                 $response = $this
//                 ->actingAs($user)
//                 ->post(route('admin::investors::storeCreate'),$data)
//                 ->assertSessionHasNoErrors();
//                 $user_id=User::latest('id')->first()->id;
//                 $response->assertRedirect(route('admin::investors::portfolio',$user_id))
//                 ->assertStatus(Response::HTTP_FOUND)
//                 ->withSession(['message' => 'New Account Created!']);
//                 $user_count_after = User::count();
//                 $inserted_count   = $user_count_after - $user_count_before;
//                 $this->assertTrue($inserted_count==1);
//             }
//         }
//     }
//     echo "\n $i investor \n";
//     $faker = Faker::create();
//     $user_count_before = User::count();
//     $data=[
//         'name'                  => 'Re-Assigner',
//         'contact_person'        => $faker->name,
//         'cell_phone'            => $faker->phoneNumber,
//         's_prepaid_status'      => 2,
//         'role_id'               => 2,
//         'investor_type'         => 2,
//         'company'               => $key,
//         'email'                 => $faker->email,
//         'notification_email'    => $faker->email,
//         'password'              => 'asdasd',
//         'password_confirmation' => 'asdasd',
//         'file_type'             => 1,
//         'show_name_mid'         => 'on',
//         'active_status'         => 0,
//         'login_board'           =>'new'
//     ];
//     $Investor=User::where('name',$data['name'])->first();
//     if($Investor){
//         $response = $this
//         ->actingAs($user)
//         ->post(route('admin::investors::storeCreate'),$data)
//         ->assertSessionHasNoErrors();
//         $user_id=User::latest('id')->first()->id;
//         $response->assertRedirect(route('admin::investors::portfolio',$user_id))
//         ->assertStatus(Response::HTTP_FOUND)
//         ->withSession(['message' => 'New Account Created!']);
//         $user_count_after = User::count();
//         $inserted_count   = $user_count_after - $user_count_before;
//         $this->assertTrue($inserted_count==1);
//     }
// })
// // ->skip()
// ;
test('transaction', function () {
    $user = User::first();//Admin
    $this->RoleRepository=new RoleRepository();
    $investors = $this->RoleRepository->allInvestors();
    $investors = $investors->pluck('id','id');
    $this
    ->actingAs($user)
    ->get(route('admin::investors::index'))
    ->assertStatus(Response::HTTP_OK);
    foreach ($investors as $investor_id) {
        echo "\n Transaction $investor_id created ";
        $this
        ->actingAs($user)
        ->get(route('admin::investors::portfolio',$investor_id))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::investors::transaction::index',$investor_id))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::investors::transaction::create',$investor_id))
        ->assertStatus(Response::HTTP_OK);
        $InvestorTransaction=InvestorTransaction::where('investor_id',$investor_id)->first();
        if(!$InvestorTransaction){
            $faker = Faker::create();
            $amount=$faker->numberBetween(1000000,10000000);
            $data=[
                'investor_id'          => $investor_id,
                'amount'               => $amount,
                'transaction_category' => 1,
                'tran_type'            => InvestorTransaction::CREDIT,
                'date'                 => date('Y-m-d'),
            ];
            $this
            ->actingAs($user)
            ->post(route('admin::investors::transaction::store',$investor_id),$data)
            ->assertSessionHasNoErrors();
            $this
            ->actingAs($user)
            ->get(route('admin::investors::transaction::index',$investor_id))
            ->assertStatus(Response::HTTP_OK);
        }
    }
})
// ->skip()
;
test('lender', function () {
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::lenders::show_lenders'))
    ->assertStatus(Response::HTTP_OK);
    $this
    ->actingAs($user)
    ->get(route('admin::lenders::create_lenders'))
    ->assertStatus(Response::HTTP_OK);
    $faker = Faker::create();
    $user_count_before = User::count();
    $data=[
        'name'                  => 'Lender 1',
        'email'                 => $faker->email,
        'password'              => 'asdasd',
        'password_confirmation' => 'asdasd',
    ];
    $Lender=User::where('name',$data['name'])->first();
    if(!$Lender){
        $response = $this
        ->actingAs($user)
        ->post(route('admin::admins::save_lender_data'),$data)
        ->assertSessionHasNoErrors();
        $user_id=User::latest('id')->first()->id;
        $response->assertRedirect(route('admin::lenders::view_lender',$user_id))
        ->assertStatus(Response::HTTP_FOUND);
        $user_count_after = User::count();
        $inserted_count   = $user_count_after - $user_count_before;
        $this->assertTrue($inserted_count==1);
    }
})
// ->skip()
;
test('AGENT_FEE_ROLE',function(){
    $user = User::first();//Admin
    $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
    $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
    $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
    if(!$AgentFeeAccount){
        $faker = Faker::create();
        $user_count_before = User::count();
        $companies=LiquidityReportHelper::allSubAdmin();
        $this->assertTrue($companies->count()>0);
        $companies=$companies->pluck('id','id');
        $companies=$companies->toArray();
        $data=[
            'name'                  => 'AGENT FEE ACCOUNT',
            'contact_person'        => 'AGENT FEE ACCOUNT',
            'cell_phone'            => $faker->phoneNumber,
            's_prepaid_status'      => 2,
            'role_id'               => User::AGENT_FEE_ROLE,
            'investor_type'         => 2,
            'company'               => array_rand($companies),
            'email'                 => 'agent_fee@iocod.com',
            'notification_email'    => 'agent_fee@iocod.com',
            'password'              => 'asdasd',
            'password_confirmation' => 'asdasd',
            'file_type'             => 1,
            'show_name_mid'         => 'on',
            'active_status'         => 1,
        ];
        $response = $this
        ->actingAs($user)
        ->post(route('admin::investors::storeCreate'),$data)
        ->assertSessionHasNoErrors();
        $user_id=User::latest('id')->first()->id;
        $response->assertRedirect(route('admin::investors::portfolio',$user_id))
        ->assertStatus(Response::HTTP_FOUND)
        ->withSession(['message' => 'New Account Created!']);
        $user_count_after = User::count();
        $inserted_count   = $user_count_after - $user_count_before;
        $this->assertTrue($inserted_count==1);
    } else {
        $this->assertTrue(true);
    }
});
test('OVERPAYMENT_ROLE',function(){
    $user = User::first();//Admin
    $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
    $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
    $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
    if(!$OverpaymentAccount){
        $faker = Faker::create();
        $user_count_before = User::count();
        $companies=LiquidityReportHelper::allSubAdmin();
        $this->assertTrue($companies->count()>0);
        $companies=$companies->pluck('id','id');
        $companies=$companies->toArray();
        $data=[
            'name'                  => 'OVERPAYMENT ACCOUNT',
            'contact_person'        => 'OVERPAYMENT ACCOUNT',
            'cell_phone'            => $faker->phoneNumber,
            's_prepaid_status'      => 2,
            'role_id'               => User::OVERPAYMENT_ROLE,
            'investor_type'         => 2,
            'company'               => array_rand($companies),
            'email'                 => 'overpayment@iocod.com',
            'notification_email'    => 'overpayment@iocod.com',
            'password'              => 'asdasd',
            'password_confirmation' => 'asdasd',
            'file_type'             => 1,
            'show_name_mid'         => 'on',
            'active_status'         => 1,
        ];
        $response = $this
        ->actingAs($user)
        ->post(route('admin::investors::storeCreate'),$data)
        ->assertSessionHasNoErrors();
        $user_id=User::latest('id')->first()->id;
        $response->assertRedirect(route('admin::investors::portfolio',$user_id))
        ->assertStatus(Response::HTTP_FOUND)
        ->withSession(['message' => 'New Account Created!']);
        $user_count_after = User::count();
        $inserted_count   = $user_count_after - $user_count_before;
        $this->assertTrue($inserted_count==1);
    } else {
        $this->assertTrue(true);
    }
});
test('merchants', function () {
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::merchants::index'))
    ->assertStatus(Response::HTTP_OK);
    $this
    ->actingAs($user)
    ->get(route('admin::merchants::create'))
    ->assertStatus(Response::HTTP_OK);
    $faker = Faker::create();
    $user_count_before = User::count();
    $amount     = $faker->numberBetween(10000,100000);
    $percentage = $faker->numberBetween(10,100);
    $percentage = 100;
    function random_float1($start_number = 0,$end_number = 1,$mul = 1000000) {
        if ($start_number > $end_number) return false;
        return mt_rand($start_number * $mul,$end_number * $mul)/$mul;
    }
    $this->RoleRepository = new RoleRepository();
    $lenders = $this->RoleRepository->allLenders()->pluck('id','id')->toArray();
    for ($i=0; $i <1 ; $i++) {
        echo "\n Merchant $i created";
        $factor_rate       = random_float1(1.5,2,100);
        $user_count_before = User::count();
        $data=[
            'name'                     => 'Merchant '.$i,
            'first_name'               => $faker->name,
            'last_name'                => $faker->name,
            'business_address'         => $faker->name,
            'city'                     => $faker->streetAddress,
            'zip_code'                 => '12312',
            'state_id'                 => 2,
            'industry_id'              => 20,
            'cell_phone'               => $faker->phoneNumber,
            'email'                    => $faker->email,
            'merchant_email'           => $faker->email,
            'password'                 => 'asdasd',
            'password_confirmation'    => 'asdasd',
            'funded'                   => $amount,
            'factor_rate'              => $factor_rate,
            'date_funded'              => date('Y-m-d',strtotime('-1 month')),
            'max_participant_fund_per' => $percentage,
            'commission'               => $faker->numberBetween(5,10),
            'pmnts'                    => $faker->numberBetween(5,10),
            'sub_status_id'            => 1,
            'advance_type'             => 'daily_ach',
            'source_id'                => 1,
            'lender_id'                => array_rand($lenders),
            'm_mgmnt_fee'              => $faker->numberBetween(1,3),
            'm_s_prepaid_status'       => 2,
            'experian_intelliscore'    => NULL,
            'experian_financial_score' => NULL,
        ];
        $Merchant=Merchant::where('name',$data['name'])->first();
        if(!$Merchant){
            $data['max_participant_fund'] = $data['funded']*$data['max_participant_fund_per']/100;
            $data['max_participant_fund'] = round($data['max_participant_fund'],2);
            $companies=LiquidityReportHelper::allSubAdmin();
            $companies=$companies->pluck('id')->toArray();
            $percentage=$faker->numberBetween(1,50);
            $number_of_companies = count($companies);
            $max_percentage      = 100;
            $groups              = array();
            $group               = 0;
            while(array_sum($groups) != $max_percentage) {
                $groups[$group] = mt_rand(0, $max_percentage/mt_rand(1,$number_of_companies));
                if(++$group == $number_of_companies) {
                    $group  = 0;
                }
            }
            foreach ($companies as $key => $company) {
                $percentage=$groups[$key];
                $data['company_id'][$company]  = $company;
                $data['company_per'][$company] = $percentage;
                $data['company_max'][$company] = $data['max_participant_fund']*$percentage/100;
            }
            $this
            ->actingAs($user)
            ->post(route('admin::merchants::storeCreate'),$data)
            ->assertSessionHasNoErrors();
            $user_count_after = User::count();
            $inserted_count   = $user_count_after - $user_count_before;
            $this->assertTrue($inserted_count==1);
        }
    }
})
// ->skip()
;
test('settings updation',function(){
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::settings::index'))
    ->assertStatus(Response::HTTP_OK);
    $faker = Faker::create();
    $CompanyAmount=CompanyAmount::where('max_participant','!=',0)->orderBy('max_participant','ASC')->first();
    $minimum_investment_value=1;
    if($CompanyAmount){
        $minimum_investment_value=$CompanyAmount->max_participant;
    }
    $data=[
        'edit'                     =>'true',
        'minimum_investment_value' =>10,
        'max_investment_per'       =>$faker->numberBetween(45,50),
    ];
    $this
    ->actingAs($user)
    ->post(route('admin::settings::settings.update'),$data)
    ->assertSessionHasNoErrors()
    ->assertRedirect(route('admin::settings::index'))
    ->assertStatus(Response::HTTP_FOUND)
    ;
})
// ->skip()
;
test('label',function(){
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::label::index'))
    ->assertStatus(Response::HTTP_OK);
    $data=[
        'MCA',
        'Luthersales',
        'Insurance',
    ];
    foreach ($data as $label) {
        $Label = Label::where('name',$label)->first();
        if(!$Label){
            $data=[
                'name' =>$label
            ];
            $this
            ->actingAs($user)
            ->post(route('admin::label::storeCreate'),$data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin::label::index'))
            ->assertStatus(Response::HTTP_FOUND)
            ;
        }
    }
})
// ->skip()
;
test('investment',function(){
    $user = User::first();//Admin
    $Merchant=Merchant::get();
    foreach ($Merchant as $key => $value) {
        $merchant_id          = $value->id;
        echo "\n investment Merchant ".$merchant_id;
        $max_participant_fund = $value->max_participant_fund;
        $funded=MerchantUser::where('merchant_id', $merchant_id)->sum('amount');
        $this
        ->actingAs($user)
        ->get(route('admin::merchants::index'))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::merchants::view',$merchant_id))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::merchants::Investment::LiquidityBased::Page',$merchant_id))
        ->assertStatus(Response::HTTP_OK);
        $this->RoleRepository=new RoleRepository();
        $investors     = $this->RoleRepository->allInvestors();
        $MerchantUser  = MerchantUser::where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->toArray();
        $all_investors = $investors
        ->whereNotIn('id',$MerchantUser)
        ->pluck('id','id')->toArray();
        if(!empty($all_investors)) {
            $data=[
                'all_investors' => $all_investors,
                'merchant_id'   => $merchant_id,
            ];
            if($funded==0){
                $this
                ->actingAs($user)
                ->post(route('admin::merchants::Investment::LiquidityBased::Assign'),$data)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin::merchants::view',$merchant_id))
                ->assertStatus(Response::HTTP_FOUND)
                ; 
            }
        }
    }
})
// ->skip()
;
test('payment',function(){
    $user = User::first();//Admin
    $Merchant=Merchant::get();
    foreach ($Merchant as $key => $value) {
        $merchant_id = $value->id;
        $rtr = $value->rtr;
        echo "\n payment Merchant ".$merchant_id;
        $no_of_payments = $value->pmnts;
        $this
        ->actingAs($user)
        ->get(route('admin::merchants::index'))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::merchants::view',$merchant_id))
        ->assertStatus(Response::HTTP_OK);
        $this
        ->actingAs($user)
        ->get(route('admin::payments::createForMerchant',$merchant_id))
        ->assertStatus(Response::HTTP_OK);
        $MerchantUser = MerchantUser::where('merchant_id',$merchant_id);
        $MerchantUser = $MerchantUser->where('amount','!=',0);
        $MerchantUser = $MerchantUser->pluck('user_id','user_id');
        $MerchantUser = $MerchantUser->toArray();
        for ($i=0; $i < 1 ; $i++) {
            echo "\n payment $i Added ";
            $paid=ParticipentPayment::where('merchant_id',$merchant_id)->where('model','like', '%ParticipentPayment%')->sum('payment');
            $payments=$paid+$value->payment_amount;
            if($rtr>=$payments){
                $payment_amount=$value->payment_amount;
            } else {
                $balance=$rtr-$paid;
                $payment_amount=$balance;
            }
            if($payment_amount>0){
                $data=[
                    'user_id'      => $MerchantUser,
                    'payment'      => $payment_amount,
                    'payment_date' => date('Y-m-d',strtotime($value->date_funded." +".$i." day")),
                    'merchant_id'  => $merchant_id,
                ];
                $this
                ->actingAs($user)
                ->get(url('admin/merchants/adjust-company-funded-amount',['mid'=>$merchant_id]))
                ->assertSessionHasNoErrors();
                $this
                ->actingAs($user)
                ->post(route('admin::payments::store'),$data)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin::merchants::view',$merchant_id))
                ->assertStatus(Response::HTTP_FOUND)
                ;
            }
        }
    }
})
// ->skip()
;
