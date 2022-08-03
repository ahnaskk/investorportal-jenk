<?php

/**
 * Created by Reshma.
 * User: iocod
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use App\User;
use Faker\Factory;
use App\SubStatus;

beforeEach(function ()
{
    $user=User::first();
    if ($user) {
        actingAs($user);
        $this->assertAuthenticated(); 
        $this->creator_id = $user->id;   
    }
});

it('has create substatus returns an ok response', function ()  {
   
       $response = $this->get(route('admin::sub_status::create'));
       $response->assertOk();
       $response->assertViewIs('admin.sub_status.create');
       $response->assertViewHas('page_title');
       $response->assertViewHas('action');
       $response->assertStatus(200);
       
});

test('store create substatus validation returns an ok response', function () {  

        $data = ['name' =>'' ];
        $response = $this->post(route('admin::sub_status::storeCreate'),$data);
        $response->assertStatus(302);
});

test('store create substatus returns an ok response', function () {  

    $substatus=['Active Advance','Payment Temporarily Suspended','Default','Collections','Referred To Legal','Advance Completed','Merchant in collections/ see notes','Other/ see notes','Partial Payment','Payment Modified','Cancelled','Settled','Early Pay Discount','Default+','Default / Legal'];

    foreach ($substatus as $key => $value) {
          $data = [            
            'name' => $value,
        ];
         $response = $this->post(route('admin::sub_status::storeCreate'),$data);
       
    }
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::sub_status::index'));
});

it('has edit substatus returns an ok response', function ()  {

        $substatus=SubStatus::orderBy('id','desc')->first();
        $response = $this->get(route('admin::sub_status::edit',['id' => $substatus->id]));
        $response->assertOk();
        $response->assertViewIs('admin.sub_status.create');
        $response->assertViewHas('page_title');
        $response->assertViewHas('subStatus');
        $response->assertViewHas('action');
       
});

test('update substatus returns an ok response', function () {  
        $substatus=SubStatus::orderBy('id','desc')->first();
        $data = [            
            'name' => $substatus->name,
            'id'=>$substatus->id,
        ];
        $response = $this->post(route('admin::sub_status::update'),$data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::sub_status::index'));
});

it('has index returns an ok response', function ()  {
   
        $response = $this->get(route('admin::sub_status::index'));
        $response->assertOk();
        $response->assertViewIs('admin.sub_status.index');
        $response->assertViewHas('page_title');
        $response->assertViewHas('tableBuilder');
       
});

test('row data returns an ok response', function () {  
        $substatus=SubStatus::orderBy('id','desc')->first();
        $data = [            
            'name' => $substatus->name,
            'flag' => 1,
            'id'=>$substatus->id,
        ];
        $response = $this->getJson(route('admin::sub_status::data'));
        $response->assertStatus(200);
});

test('delete label returns an ok response', function () {  
        $substatus=SubStatus::orderBy('id','desc')->first();
        $response = $this->post(route('admin::sub_status::delete', ['id' => $substatus->id]));
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::sub_status::index'));
});





