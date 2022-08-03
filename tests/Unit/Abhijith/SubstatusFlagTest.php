<?php

namespace Tests\Unit\Abhijith;

use App\User;
use Faker\Factory;
use App\MerchantUser;
use Symfony\Component\HttpFoundation\Response;
use App\SubStatusFlag;

// it('Set Database for Testing', function () {
//     \Artisan::call('migrate:fresh --seed');
//     $this->assertTrue(true);
// });

beforeEach(function ()
{   
    $user = User::first();
    $this->actingAs($user);
    $this->userId = $user->id;  
});


 test('substatus flag list page',function(){

     $showSubstatus = $this->get(route('admin::sub_status_flag::index'));
     $showSubstatus->assertStatus(Response::HTTP_OK);
     $this->assertTrue(true);
     echo "\n Substatus List page success";

 });


 test('substatus flag create page',function(){

     $createSubstatusFlag = $this->get(route('admin::sub_status_flag::create'));
     $createSubstatusFlag->assertStatus(Response::HTTP_OK);
     $this->assertTrue(true);
     echo "\n create substatus view page success";


 });


 test('substatus flag creation',function(){

     $faker = Factory::create();
     $flagName = $faker->randomElement(['Collections', 'Bankruptcy','Hardship']);
     $flagData = [
          'name' => $flagName
     ];
     $response = $this->post(route('admin::sub_status_flag::storeCreate'),$flagData);
     $response ->assertSessionHasNoErrors();
     $response->assertStatus(302);
     echo "\n  Substatus created successfully";

 });

 test('substatus flag edit page and update',function(){

     $latestFlag = SubStatusFlag::latest()->first();
     $flagId = ['id' =>$latestFlag->id];

     $flagEdit = $this->get(route('admin::sub_status_flag::edit',$flagId));
     $flagEdit ->assertStatus(Response::HTTP_OK);
     $this->assertTrue(true);
     echo "\n edit substatus view page success";

     $faker = Factory::create();
     $flagName = $faker->randomElement(['Collections', 'Bankruptcy','Hardship']);

     $flagData = [
          'name' => $flagName
     ];
     $updateflag = $this->post(route('admin::sub_status_flag::update',$flagId),$flagData);
     $updateflag->assertSessionHasNoErrors();
     $updateflag->assertStatus(Response::HTTP_FOUND);

     echo "\n Edit and Update substatus flag successful";

 });


 test('substatus flag deleted successfully',function(){

     $latestFlag = SubStatusFlag::latest()->first();
     $flagId = ['id' =>$latestFlag->id];
     $deleteFlag = $this->post(route('admin::sub_status_flag::update',$flagId));
     $deleteFlag ->assertSessionHasNoErrors();
     $deleteFlag0>assertStatus(Response::HTTP_FOUND);

     echo "\n Delete substatus flag success";
 })->skip();
