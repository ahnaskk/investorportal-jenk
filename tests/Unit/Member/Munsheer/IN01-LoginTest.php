<?php

use Faker\Factory;

beforeEach(function(){
    $this->adminEmail = adminCredentials()['email'];
    $this->adminPassword = adminCredentials()['password'];
});

it('will seed data for fresh database', function () {    
    $this->artisan('migrate:fresh --seed');
    $this->assertDatabaseCount('users', 2);
})->skip();

it('has login page')
    ->get('/login')
    ->assertStatus(200)
    ->assertSee('Login to your account')
    ->assertSee('E-Mail Id')
    ->assertSee('Password');

test('email validation working correctly', function (){
    $credentials = [
        'email' => null,
        'password' => $this->adminPassword
    ];
    $response = $this->post(route('login'), $credentials);
    $response->assertSessionHasErrors([
        'email' => 'The email field is required.'
    ]);
});

test('password validation working correctly', function (){
    $credentials = [
        'email' => $this->adminEmail,
        'password' => null
    ];
    $response = $this->post(route('login'), $credentials);
    $response->assertSessionHasErrors([
        'password' => 'The password field is required.'
    ]);
});

test('invalid credential validation working correctly', function (){
    $faker = Factory::create();
    $credentials = [
        'email' => $faker->email,
        'password' => $faker->password
    ];
    $response = $this->post(route('login'), $credentials);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login working correctly', function () {
    $credentials = [
        'email' => $this->adminEmail,
        'password' => $this->adminPassword
    ];
    $this->post(route('login'), $credentials)
                ->assertStatus(302)
                ->assertSessionHasNoErrors();

    $this->assertAuthenticated();
});
