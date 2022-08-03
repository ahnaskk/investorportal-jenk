<?php

use App\CompanyAmount;
use App\Helpers\Report\LiquidityReportHelper;
use App\InvestorTransaction;
use App\Library\Facades\MerchantHelper;
use App\Library\Repository\RoleRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Response;

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
    $faker = Factory::create();
    $user_count_before = User::count();
    $data=[
        'name'                  => $faker->name,
        'email'                 => $faker->email,
        'password'              => 'asdasd',
        'password_confirmation' => 'asdasd',
    ];
    $this
    ->actingAs($user)
    ->post(route('admin::admins::save_lender_data'),$data)
    ->assertSessionHasNoErrors();
    $user_id=User::latest()->first()->id;
    $this
    ->actingAs($user)
    ->get(route('admin::lenders::view_lender',$user_id))
    ->assertStatus(Response::HTTP_OK);
    $user_count_after = User::count();
    $inserted_count   = $user_count_after - $user_count_before;
    $this->assertTrue($inserted_count==1);
});
test('merchants', function ()
{
    ini_set('memory_limit', '-1');
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::merchants::index'))
    ->assertStatus(Response::HTTP_OK);
    $this
    ->actingAs($user)
    ->get(route('admin::merchants::create'))
    ->assertStatus(Response::HTTP_OK);
    $faker = Factory::create();
    $user_count_before = User::count();
    $amount     = $faker->numberBetween(10000,100000);
    $percentage = $faker->numberBetween(10,100);
    $percentage = 100;
    function random_float($start_number = 0,$end_number = 1,$mul = 1000000) {
        if ($start_number > $end_number) return false;
        return mt_rand($start_number * $mul,$end_number * $mul)/$mul;
    }
    $this->RoleRepository = new RoleRepository();
    $lenders = $this->RoleRepository->allLenders()->pluck('id','id')->toArray();
    for ($i=0; $i <2 ; $i++) {
        echo "\n Merchant $i created";
        $factor_rate       = random_float(1.5,2,100);
        $user_count_before = User::count();
        $data=[
            'name'                     => $faker->name,
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
        if ($i==1) {
            $data['label'] = 3; //insurance merchant
        }
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
});
test('settings updation',function(){
    $user = User::first();//Admin
    $this
    ->actingAs($user)
    ->get(route('admin::settings::index'))
    ->assertStatus(Response::HTTP_OK);
    $faker = Factory::create();
    $CompanyAmount=CompanyAmount::where('max_participant','!=',0)->orderBy('max_participant','ASC')->first();
    $minimum_investment_value=1;
    if($CompanyAmount){
        $minimum_investment_value=$CompanyAmount->max_participant;
    }
    $data=[
        'edit'                     =>'true',
        'minimum_investment_value' =>$minimum_investment_value,
        'max_investment_per'       =>$faker->numberBetween(10,50),
    ];
    $this
    ->actingAs($user)
    ->post(route('admin::settings::settings.update'),$data)
    ->assertSessionHasNoErrors()
    ->assertRedirect(route('admin::settings::index'))
    ->assertStatus(Response::HTTP_FOUND)
    ;
});

test('liquidity log view page exists', function () {
    $user = User::first();
    $response = actingAs($user)->get(route('admin::reports::liquidity-log'));
    $response->assertOk();
    $response->assertViewIs('admin.reports.liquidity_log');
});

it('will load the liquidity log page correctly', function () {
    $user = User::first();
    $response = actingAs($user)->get(route('admin::reports::liquidity-log'));
    $response->assertStatus(200);
});
it('will list liquidity log ajax data correctly', function () {
    $user = User::first();
    $response = actingAs($user)->post(route('admin::reports::liquidity-log'));
    $response->assertStatus(200);
});

test('liquidity log lists transactions done from the add-transaction screen correctly', function () {
    $user = User::first();
    $role = new RoleRepository;
    $investors = $role->allInvestors();
    foreach ($investors as $investor) {
        $faker = Factory::create();
        $amount=$faker->numberBetween(1000,100000);
        $data = [
            'amount' => $amount,
            'date' => date('Y-m-d'),
            'transaction_category' => 1,
            'investor_id' => $investor->id,
            'tran_type' => InvestorTransaction::CREDIT,
            'creator_id' => $user->id
        ];
        $response = actingAs($user)->post(route('admin::investors::transaction::store', $investor->id), $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('liquidity_log', [
            'member_id' => $investor->id,
            'description' => 'Transfer To Velocity',
            'liquidity_change' => $amount,
            'member_type' => 'investor',
            'creator_id' => $user->id
        ]);
    }
});

it('will check total liquidity is correct on the listing page', function () {
    $user = User::first();
    $response = actingAs($user)->postJson(route('admin::reports::liquidity-log'), [
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d')
    ]);
    $response->assertSessionHasNoErrors();
    $totalFromDB = LiquidityLog::whereDate('created_at', Carbon::today())->sum('liquidity_change');
    $this->assertEquals($response['t_liquidity_change'], \FFM::dollar($totalFromDB));
});

test('assign_based_on_liquidity logging changes correctly', function () {
    $merchant = Merchant::leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
                ->whereNull('merchant_user.merchant_id')->select('merchants.*')->first();
    $user = User::first();
    actingAs($user);
    $role = new RoleRepository;
    $investors = $role->allInvestors();
    $data['investors'] = $investors->pluck('id','id')->toArray();
    $data['merchant_id'] = $merchant->id;
    $liquidityShareData = MerchantHelper::LiquidityBasedShare($data);  
    $selectedData = $liquidityShareData['selectedData'];
    //Before investment checking liquidity
    $liquidity_old = [];
    foreach ($selectedData as $investor_id => $d) {
        $liquidity_old[$investor_id] = UserDetails::where('user_id', $investor_id)->value('liquidity');
    }
    $postData = [
        'all_investors' => $data['investors'],
        'merchant_id' => $merchant->id
    ];
    $response = $this->post(route('admin::merchants::Investment::LiquidityBased::Assign'), $postData);
    $response->assertSessionHasNoErrors();

    foreach ($selectedData as $investor_id => $d) {
        $log = LiquidityLog::where('member_id', $investor_id)->latest()->first();
        $this->assertSame($investor_id, $log->member_id);
        $this->assertSame($merchant->id, $log->merchant_id);
        $this->assertSame('based_on_liquidity', $log->description);
        $change = $selectedData[$investor_id]['investment'] + $log->liquidity_change;
        expect($change)->toBeFalsy();
        $this->assertSame($user->id, $log->creator_id);
        //after investment checking expected amount is taken from liquidity
        $new_liquidity = UserDetails::where('user_id', $investor_id)->value('liquidity');
        $liquidity_change = $liquidity_old[$investor_id] - $new_liquidity;
        expect($liquidity_change)->toEqual($selectedData[$investor_id]['investment']);
    }
});
test('payment changes logging correctly',function(){
    $user = User::first();//Admin
    $Merchant=Merchant::first();
    $merchant_id = $Merchant->id;
    $rtr = $Merchant->rtr;
    $no_of_payments = $Merchant->pmnts;
    
    actingAs($user)
    ->get(route('admin::merchants::index'))
    ->assertStatus(Response::HTTP_OK);
    
    actingAs($user)
    ->get(route('admin::merchants::view',$merchant_id))
    ->assertStatus(Response::HTTP_OK);
    
    actingAs($user)
    ->get(route('admin::payments::createForMerchant',$merchant_id))
    ->assertStatus(Response::HTTP_OK);

    $MerchantUser = MerchantUser::where('merchant_id',$merchant_id);
    $MerchantUser = $MerchantUser->where('amount','!=',0);
    $MerchantUser = $MerchantUser->pluck('user_id','user_id');
    $MerchantUser = $MerchantUser->toArray();
    $expected_total_liquidity_change = 0;
    for ($i=0; $i < $no_of_payments ; $i++) {
        $paid=ParticipentPayment::where('merchant_id',$merchant_id)->where('model','like', '%ParticipentPayment%')->sum('payment');
        $payments=$paid+$Merchant->payment_amount;
        if($rtr>=$payments){
            $payment_amount=$Merchant->payment_amount;
        } else {
            $balance=$rtr-$paid;
            $payment_amount=$balance;
        }
        $data=[
            'user_id'      => $MerchantUser,
            'payment'      => $payment_amount,
            'payment_date' => date('Y-m-d',strtotime($Merchant->date_funded." +".$i." day")),
            'merchant_id'  => $merchant_id,
        ];
        //checking share
        $share = actingAs($user)->postJson(route('admin::payments::shareCheck'), [
            'user_id' => $MerchantUser,
            'merchant_id' => $merchant_id,
            'payment' => $payment_amount 
        ]);
        actingAs($user)
                ->get(url('admin/merchants/adjust-company-funded-amount',['mid'=>$merchant_id]))
                ->assertSessionHasNoErrors();
        actingAs($user)
        ->post(route('admin::payments::store'),$data)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin::merchants::view',$merchant_id))
        ->assertStatus(Response::HTTP_FOUND);
        // Checking expected amount changes is logged in liquidity log
        $response = actingAs($user)->postJson(route('admin::reports::liquidity-log'), [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
            'description' => ['Payment'],
            'groupbypay' => 1
        ]);
        $formatted_share = str_replace(["$", ","], "", $share['ToParticipant']);
        $expected_total_liquidity_change += $formatted_share;
        expect(\FFM::dollar($expected_total_liquidity_change))->toEqual($response['t_liquidity_change']);
       
    }
});
