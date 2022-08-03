<?php

namespace Tests\Feature\Http\Controllers\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use Faker\Factory;
use Illuminate\Http\UploadedFile;
beforeEach(function ()
{   
    $user = User::first();
    $this->actingAs($user);    
    $this->assertAuthenticated();
    $this->user_id = $user->id;
   
});

it('migrate fresh database', function () {  
       ini_set('memory_limit', '-1');
       $this->artisan('migrate:fresh --seed');  
       $this->artisan('db:seed --class=View');
   $this->assertDatabaseCount('users', 3);
});

test('company list', function () {  
    $response = $this->get(route('admin::sub_admins::index'));  
    $response->assertOk();  
    $response->assertViewIs('admin.sub_admins.index');
    $response->assertViewHas('page_title');
    $response->assertViewHas('tableBuilder');
    $response->assertStatus(200);
   
 });
 test('view company returns an ok response', function ()  {
 
        $response = $this->get(route('admin::sub_admins::create'));
        $response->assertOk();
        $response->assertViewIs('admin.sub_admins.create');
        $response->assertViewHas('page_title');       
        $response->assertStatus(200);
        
 });
 test('store create company returns an ok response', function () {  
  for($i=0; $i < 3; $i++)
     {
         $faker = Factory::create();
         $password = $faker->password();
         $name = $faker->company();
         $email = $faker->email();
     
         $data = [
             'logo' => UploadedFile::fake()->create('test.png'),
             'name' => $name,
             'email' => $email,
             'password' => $password,
             'password_confirmation' => $password,
             'brokerage' => $faker->numberBetween(0, 100),
             'management_fee' => 0,
             'merchant_permission' => 1,
             'company_status' => 1,
             'creator_id' => $this->user_id,
             'syndicate' => 0
         ];
         $response = $this->post(route('admin::sub_admins::storeCreate'), $data);
         $response->assertStatus(302);
         $this->assertDatabaseHas('users', [
            'email' => $email,            
            'name' => $name            
        ]);
         $response->assertSessionHasNoErrors();
      
     }
 });  
 
 test('update company working correctly', function () {
     $lastest = User::latest('id')->first();
     $id = $lastest->id;
     
     $faker = Factory::create();
     $name = $faker->name();
     $email = $faker->email();
     $data = [
         'name' => $name,
         'email' => $email,
         'brokerage' => $faker->numberBetween(0, 100),
         'merchant_permission' => 0,
         'company_status' => 1,
         'syndicate' => 1
     ];
     $response = $this->post(route('admin::sub_admins::update', $id), $data);
     $response->assertSessionHasNoErrors();
 });
 
 test('delete company working correctly', function () {
     $lastest = User::latest('id')->first();
     $id = $lastest->id;
     $response = $this->post(route('admin::sub_admins::delete', $id));
     $response->assertSessionHasNoErrors();
 });

test('lenders list', function () {  
    $response = $this->get(route('admin::admins::index'));  
    $response->assertOk();  
    $response->assertViewIs('admin.admins.index');   
    $response->assertStatus(200);
  
});
test('view lender returns an ok response', function ()  {
       $response = $this->get(route('admin::lenders::create_lenders'));
       $response->assertOk();
       $response->assertViewIs('admin.admins.create_lender');
       $response->assertViewHas('page_title');       
       $response->assertStatus(200);       
});
test('store create lender returns an ok response', function () {  
 for($i=0; $i < 2; $i++)
    {
        $faker = Factory::create();
        $password = $faker->password();
        $name = $faker->name();
        $email = $faker->email();
    
        $data = [            
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'lag_time' => $faker->numberBetween(0, 100),
            'management_fee' => 0,
            'global_syndication' => $faker->numberBetween(0, 100),
            's_prepaid_status' => 1,
            'creator_id' => $this->user_id,
            'lag_time' => 0,
            'underwriting_fee'=> 0,

        ];
        $response = $this->post(route('admin::admins::save_lender_data'), $data);
        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
                       'email' => $email,            
                       'name' => $name            
                   ]);
        $response->assertSessionHasNoErrors();
     
    }
}); 
    test('update lender working correctly', function () {
        $lastest = User::latest('id')->first();
        $id = $lastest->id;
        
        $faker = Factory::create();
        $name = $faker->name();
        $email = $faker->email();
        $data = [
            'name' => $name,
            'email' => $email,
            'lag_time' => $faker->numberBetween(0, 100),
            'management_fee' => $faker->numberBetween(0, 5),
            'global_syndication' => $faker->numberBetween(0, 5),
            'underwriting_fee' => $faker->numberBetween(0, 5),
        ];      
        $response = $this->post(route('admin::admins::update_lender', $id), $data);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    
}); 

