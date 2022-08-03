<?php

namespace Tests\Feature\Auth;
use App\User;


test('adminlogin', function () {
    expect(true)->toBeTrue();
});

it('admin login', function () {
   $response = $this->get('/login');
   $response->assertOk()->assertStatus(200);
});

it('label create', function () {
   $user = User::find(1);
   $this->actingAs($user);
   $response = $this->get('/admin/label');
   $response->assertOk()->assertStatus(200);
  
});


