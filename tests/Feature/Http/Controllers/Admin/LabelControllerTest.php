<?php

/**
 * Created by Reshma.
 * User: iocod
 */

use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Facades\DB;
use App\User;
use Faker\Factory;
use App\Label;

beforeEach(function ()
{
    $user=User::first();
    if ($user) {
        actingAs($user);
        $this->assertAuthenticated(); 
        $this->creator_id = $user->id;   
    }
});



it('has create label returns an ok response', function ()  {
   
       $response = $this->get(route('admin::label::create'));
       $response->assertOk();
       $response->assertViewIs('admin.label.create');
       $response->assertViewHas('page_title');
       $response->assertViewHas('action');
       $response->assertStatus(200);
       
});

test('store create label validation returns an ok response', function () {  

        $data = [            
            'name' =>'',
            'flag' => 0,
        ];
        $response = $this->post(route('admin::label::storeCreate'),$data);
        $response->assertStatus(302);
});

test('store create label returns an ok response', function () {  

    $labels=['MCA (Default)','Luther Sales','Insurance','Insurance 1','Insurance 2'];

    foreach ($labels as $key => $value) {
          $data = [            
            'name' => $value,
            'flag' => 0,
        ];
        $response = $this->post(route('admin::label::storeCreate'),$data);

    }
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::label::index'));
});

it('has edit label returns an ok response', function ()  {
   
        $lab=Label::orderBy('id','desc')->first();
        $response = $this->get(route('admin::label::edit', ['id' => $lab->id]));
        $response->assertOk();
        $response->assertViewIs('admin.label.create');
        $response->assertViewHas('page_title');
        $response->assertViewHas('label');
        $response->assertViewHas('action');
       
});

test('update label returns an ok response', function () {  
        $lab=Label::orderBy('id','desc')->first();
        $data = [            
            'name' => $lab->name,
            'flag' => 0,
            'id'=>$lab->id,
        ];
        $response = $this->post(route('admin::label::update'),$data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::label::index'));
});

it('has index returns an ok response', function ()  {
   
        $response = $this->get(route('admin::label::index'));
        $response->assertOk();
        $response->assertViewIs('admin.label.index');
        $response->assertViewHas('page_title');
        $response->assertViewHas('tableBuilder');
       
});

test('row data returns an ok response', function () {  
        $lab=Label::orderBy('id','desc')->first();
        $data = [            
            'name' => $lab->name,
            'flag' => 1,
            'id'=>$lab->id,
        ];
        $response = $this->getJson(route('admin::label::data'));
        $response->assertStatus(200);
});

test('delete label returns an ok response', function () {  
        $lab=Label::orderBy('id','desc')->first();
        $response = $this->post(route('admin::label::delete', ['id' => $lab->id]));
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('admin::label::index'));
});









       







