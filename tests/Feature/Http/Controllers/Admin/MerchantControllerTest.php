<?php

/**
 * Created by Reshma.
 * User: iocod
 */


use App\User;
use Illuminate\Support\Facades\Validator;
use App\Merchant;
use Illuminate\Http\Request;
use App\Library\Facades\MerchantHelper;
use Faker\Factory;
use App\Exports\Data_arrExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Repository\Interfaces\IRoleRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Label;
use App\SubStatus;

beforeEach(function ()
{
    $user=User::first();
    if ($user) {
        actingAs($user);
        $this->assertAuthenticated(); 
        $this->creator_id = $user->id;   
    }
   // $this->request=new \Illuminate\Http\Request();
    $this->faker= Merchant::factory()->make();
    $this->user_faker=User::factory()->make();
    $this->role= app(IRoleRepository::class);

});
 
test('merchantcontroller', function () {
    expect(true)->toBeTrue();
});

// test('store create lender returns an ok response', function () {  

//         $data = [            
//             'name' => $this->user_faker->name,
//             'email' => $this->user_faker->email,
//             'password' => $this->user_faker->password,
//             'password_confirmation' => $this->user_faker->password,
//             'brokerage' => 0,
//             'management_fee' => 2,
//             'global_syndication' => 2,
//             's_prepaid_status' => 1,
//             'creator_id' =>  $this->creator_id,
//             'lag_time' => 0,
//             'underwriting_fee'=> 0,

//         ];
//         $response = $this->post(route('admin::admins::save_lender_data'), $data);
//         $response->assertSessionHasNoErrors();
//         $response->assertStatus(302);
// }); 

// test('store create company returns an ok response', function () {  
//  for($i=0; $i < 2; $i++)
//     {
//         $faker = Factory::create();
//         $password = $faker->password();
//         $email = $faker->email();

//         $data = [
           
//             'name' => $faker->name(),
//             'email' => $email,
//             'logo' => UploadedFile::fake()->create('logo.png'),
//             'password' => $password,
//             'password_confirmation' => $password ,
//             'brokerage' => 0,
//             'management_fee' => 0,
//             'merchant_permission' => 1,
//             'company_status' => 1,
//             'creator_id' => $this->creator_id,
//             'syndicate' => 0
//         ];
//         $response = $this->post(route('admin::sub_admins::storeCreate'), $data);
//         $response->assertSessionHasNoErrors();
//         $response->assertStatus(302);
     
//     }
// }); 

it('has create merchant returns an ok response', function ()  {
   
       $response = $this->get(route('admin::merchants::create'));
       $response->assertOk();
       $response->assertViewIs('admin.merchants.create');
       $response->assertViewHas('page_title');
       $response->assertViewHas('lender_login');
       $response->assertViewHas('lender_data');
       $response->assertViewHas('syndication_fee_values');
       $response->assertViewHas('statuses');
       $response->assertViewHas('investors');
       $response->assertViewHas('admins');
       $response->assertViewHas('action');
       $response->assertViewHas('industries');
       $response->assertViewHas('states');
       $response->assertViewHas('merchant_source');
       $response->assertViewHas('company');
       $response->assertViewHas('companies');
       $response->assertViewHas('underwriting_company');
       $response->assertViewHas('label');
       $response->assertStatus(200);
       
});

test('create merchant mandatory validation returns an ok response', function () {
     
     $default_lender = $this->role->allLenders()->first();
     $label=Label::first();
     $substatus=SubStatus::first();

     $request = [ 
     'name'=>'',
     'first_name'=>'',
     'last_name'=>'',   
     'label'=>'',
     'funded'=>0,
     'max_participant_fund'=>0,
     'max_participant_fund_per'=>0,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>'',
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'pmnts'=>'',
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'm_s_prepaid_status'=>'',
     'origination_fee'=>0,
     'lender_id'=>'',
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>'',

 ];

  $response = $this->post(route('admin::merchants::create'),$request);
  $response->assertStatus(302);

   });

 test('create merchant decimals in funded amount only for insurance merchants returns an ok response', function () {
     
     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins()->toArray();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$key]=$company['id'];
            $company_share[$company['id']]=1000.00;
        }

     }

    $request = [ 
     'name'=>'decimal insurance',
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'label'=>$label->id,
     'funded'=>3000.78,
     'max_participant_fund'=>3000.78,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.5,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,
 ];

  $response = $this->post(route('admin::merchants::create'),$request);
  $response->assertStatus(302);

   });

 test('create merchant decimals in max participant fund amount only for insurance merchants', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins()->toArray();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$key]=$company['id'];
            $company_share[$company['id']]=1000.00;
        }

     }

     $request = [ 
     'name'=>'decimal',
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>'',
     'label'=>$label->id,
     'funded'=>20000,
     'max_participant_fund'=>3000.78,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.2,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,

 ];

  $response = $this->post(route('admin::merchants::create'),$request);
  $response->assertStatus(302);

 });
  test('create merchant factor rate validation returns an ok response', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins()->toArray();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$key]=$company['id'];
            $company_share[$company['id']]=1000.00;
        }

     }

     $request = [ 
     'name'=>'Factor rate',
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>'',
     'label'=>$label->id,
     'funded'=>20000,
     'max_participant_fund'=>20000,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.2222222222222,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,

 ];

  $response = $this->post(route('admin::merchants::create'),$request);
  $response->assertStatus(302);

 }); 

 test('Company Share not completed for this merchant', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];
     $company_per=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$company['id']]=$company['id'];
            $company_share[$company['id']]=1000.00;
            $company_per[$company['id']]=25.00;
        }

     }

     $request = [ 
     //'merchant_id'=>Merchant::orderBy('id','desc')->first()->id,
     'name'=>$this->faker->name,
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>$this->faker->email,
     'label'=>$label->id,
     'funded'=>40000,
     'max_participant_fund'=>40000,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.5,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'company_per'=>$company_per,
     'company_id'=>$company_id,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,
     'password'=>''
 ];

    $response = $this->post(route('admin::merchants::create'),$request);
    $response->assertStatus(302);

});

 test('store create merchant returns an ok response', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins()->toArray();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];
     $company_per=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$company['id']]=$company['id'];
            $company_share[$company['id']]=10000;
            $company_per[$company['id']]=50;
        }

     }

  $request = [ 
     'name'=>'New Merchant',
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>'',
     'label'=>$label->id,
     'funded'=>20000,
     'max_participant_fund'=>20000,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.5,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'company_per'=>$company_per,
     'company_id'=>$company_id,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,

 ];
    $response = $this->post(route('admin::merchants::create'),$request);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);

});  

 test('update merchant returns an ok response', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];
     $company_per=[];

     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$company['id']]=$company['id'];
            $company_share[$company['id']]=2000;
            $company_per[$company['id']]=50;
        }

     }

  $request = [ 
     'merchant_id'=>Merchant::orderBy('id','desc')->first()->id,
     'name'=>$this->faker->name,
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>$this->faker->email,
     'label'=>$label->id,
     'funded'=>40000,
     'max_participant_fund'=>40000,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.5,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
     'company_max' =>$company_share,
     'company_per'=>$company_per,
     'company_id'=>$company_id,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,
     'password'=>''
 ];
    $response = $this->post(route('admin::merchants::update'),$request);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);

});

 it('has edit merchant returns an ok response', function ()  {

       $id=Merchant::orderBy('id','desc')->first()->id;
       $response = $this->get(route('admin::merchants::edit',$id));
       $response->assertOk();
       $response->assertViewIs('admin.merchants.create');
       $response->assertViewHas('page_title');
       $response->assertViewHas('lender_login');
       $response->assertViewHas('lender_data');
       $response->assertViewHas('syndication_fee_values');
       $response->assertViewHas('statuses');
       $response->assertViewHas('investors');
       $response->assertViewHas('admins');
       $response->assertViewHas('action');
       $response->assertViewHas('industries');
       $response->assertViewHas('states');
       $response->assertViewHas('merchant_source');
       $response->assertViewHas('company');
       $response->assertViewHas('companies');
       $response->assertViewHas('underwriting_company');
       $response->assertViewHas('label');
       $response->assertStatus(200);
       
}); 

test('update merchant mail duplicate validation returns an ok response', function () {

     $default_lender = $this->role->allLenders()->first();
     $companies=$this->role->allSubAdmins();
     $label=Label::first();
     $substatus=SubStatus::first();
     $company_id=[];
     $company_share=[];
     $company_per=[];

     $email=User::orderBy('id','desc')->value('email');
     if($companies)
     {
        foreach ($companies as $key => $company) {
            $company_id[$company['id']]=$company['id'];
            $company_share[$company['id']]=2000;
            $company_per[$company['id']]=50;
        }

     }
    
    $request = [ 
     'merchant_id'=>Merchant::orderBy('id','desc')->first()->id,
     'name'=>$this->faker->name,
     'first_name'=>$this->faker->name,
     'last_name'=>'',   
     'email'=>$email,
     'label'=>$label->id,
     'funded'=>40000,
     'max_participant_fund'=>40000,
     'max_participant_fund_per'=>100,
     'date_funded'=>'2021-09-01',
     'cell_phone'=>'',
     'industry_id'=>151,
     'factor_rate'=>1.5,
     'state_id'=>1,
     'm_mgmnt_fee'=>$default_lender->management_fee,
      'company_max' =>$company_share,
     'company_per'=>$company_per,
     'company_id'=>$company_id,
     'pmnts'=>20,
     'experian_intelliscore'=>0,
     'experian_financial_score'=>0,
     'account_holder_name'=>'',
     'routing_number'=>'',
     'bank_name'=>'',
     'account_number'=>'',
     'underwriting_status'=>[1,2],
     'underwriting_fee'=>$default_lender->underwriting_fee,
     'm_s_prepaid_status'=>$default_lender->s_prepaid_status,
     'm_syndication_fee'=>$default_lender->global_syndication,
     'origination_fee'=>0,
     'lender_id'=>$default_lender->id,
     'source_id'=>1,
     'marketplace_status'=>0,
     'advance_type'=>'daily_ach',
     'sub_status_id'=>$substatus->id,
     'credit_score'=>450,
     'commission'=>15,
     'password'=>''
 ];
    $response = $this->post(route('admin::merchants::update'),$request);
    $response->assertSessionHasNoErrors();

 });  

it('has all merchants returns an ok response', function () {

    $response = $this->get(route('admin::merchants::index'));
    $response->assertStatus(200);
    $response->assertViewIs('admin.merchants.index');
});

test('all merchants returns an ok response', function () {
    $default_lender = $this->role->allLenders()->first();

    $filter = [
        'user_id'=>'',
        'lender_id' => $default_lender->id,
        'status_id' => 1,
        'market_place'=>false,
        'not_started'=>false,
        'not_invested'=>false,
        'paid_off'=>false,
        'stop_payment'=>false,
        'over_payment'=>false,
        'late_payment'=>'',
        'date_start'=>'',
        'date_end'=>'',
        'advance_type'=>'',
        'substatus_flag_id'=>0,
        'label'=>'',
        'bank_account'=>'',
        'payment_pause'=>'',
        'owner'=>0,
        'mode_of_payment'=>0,

    ];
    $response = $this->getJson(route('admin::merchants::index'),$filter);
    $response->assertStatus(200);

    //print_r($response);
   // dd();
    //$response->assertStatus(200);
});

test('merchant list download returns an ok response', function () {

     $default_lender = $this->role->allLenders()->first();

     $response = $this->post(route('admin::reports::merchant-list-download'), [
            'user_id'=>'',
            'lender_id' => $default_lender->id,
            'status_id' => 1,
            'market_place'=>false,
            'not_started'=>false,
            'not_invested'=>false,
            'paid_off'=>false,
            'stop_payment'=>false,
            'over_payment'=>false,
            'late_payment'=>'',
            'date_start'=>'',
            'date_end'=>'',
            'advance_type'=>'',
            'substatus_flag_id'=>0,
            'label'=>'',
            'bank_account'=>'',
            'payment_pause'=>'',
            'owner'=>0,
            'mode_of_payment'=>0,
        ]);

      //$response->assertRedirect(route('admin::merchants::index'));

    // Excel::assertDownloaded('filename.csv', function(Data_arrExport $export) {
    //     // Assert that the correct export is downloaded.
    //     return $export->collection()->contains('#2021-01');
    // });

     //print_r($result);
    // dd();
        //$response->assertDownload($result);
      //  $response->assertStatus(302);


});

 test('merchant view returns an ok response', function () {

    $id=Merchant::orderBy('id','desc')->first()->id;
    $response = $this->get(route('admin::merchants::view',$id));
    $response->assertViewHas('selected_investors');
    $response->assertViewHas('paid_to_participant');
    $response->assertStatus(200);
    
  });

// test('merchant payments list returns an ok response',function()
//  {
//      $id=Merchant::orderBy('id','desc')->first()->id;
//      $investor_id=15;
//      $company_id=89;
//      $response = $this->get(route('admin::merchants::merchant_data',['merchant_id'=>$id,'company_id'=>$company_id,'investor_id'=>$investor_id]));
//      $response->assertStatus(200);

//  });

 it('merchant user role view returns an ok response',function()
 {
       $response = $this->get(route('admin::merchants::show-merchant-users'));
       $response->assertOk();
       $response->assertViewHas('page_title');
       $response->assertViewHas('tableBuilder');
       $response->assertStatus(200);

 });

  test('delete merchant returns an ok response', function () {

    $id=Merchant::orderBy('id','desc')->first();
    $response = $this->post(route('admin::merchants::delete',$id));
    $response->assertRedirect(route('admin::merchants::index'));

  });


 