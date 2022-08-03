<?php
ini_set('memory_limit', '-1');
use App\User;
use Faker\Factory;
use Illuminate\Http\UploadedFile;

beforeEach(function (){
    $user = User::first();
    $this->actingAs($user);
    $this->userId = $user->id;
});

it('has company listing page', function () {
    $response = $this->get(route('admin::sub_admins::index'));
    $response->assertOk();
    $response->assertViewIs('admin.sub_admins.index');
    $response->assertViewHas('page_title');
    $response->assertViewHas('tableBuilder');
});

test('create-company working successfully', function () {
    for($i=0; $i < 1; $i++)
    {
        $faker = Factory::create();
        $password = $faker->password();
        $companyName = $faker->company();
        $companyEmail = $faker->companyEmail();
    
        $data = [
            'logo' => UploadedFile::fake()->create('test.png'),
            'name' => $companyName,
            'email' => $companyEmail,
            'password' => $password,
            'password_confirmation' => $password,
            'brokerage' => $faker->numberBetween(0, 100),
            'management_fee' => '',
            'merchant_permission' => 1,
            'company_status' => 1,
            'creator_id' => $this->userId,
            'syndicate' => 0
        ];
        $response = $this->post(route('admin::sub_admins::storeCreate'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'New Company created!');

        $createdUser = User::where('email', $companyEmail)->first();
        $this->assertDatabaseHas('users', [
            'email' => $companyEmail,
            'name' => $companyName
        ]);
        $this->assertDatabaseHas('user_has_roles', [
            'model_id' => $createdUser->id,
            'role_id' => 6
        ]);
    }
});

test('points to correct edit URL', function () {
    $lastData = User::latest('id')->first();
    $lastId = $lastData->id;
    expect(route('admin::sub_admins::edit', $lastId))->toBe(url('/admin/sub_admins/edit/'.$lastId));
});

it('has company edit page', function () {
    $lastData = User::latest('id')->first();
    $lastId = $lastData->id;
    $response = $this->get(route('admin::sub_admins::edit', $lastId));
    $response->assertOk();
    $response->assertViewIs('admin.sub_admins.create');
    $response->assertSee('Edit Companies');
});

test('update company working correctly', function () {
    $lastData = User::latest('id')->first();
    $lastId = $lastData->id;
    // Before updating
    $faker = Factory::create();
    $companyName = $faker->company();
    $companyEmail = $faker->companyEmail();
    $data = [
        'name' => $companyName,
        'email' => $companyEmail,
        'brokerage' => $faker->numberBetween(0, 100),
        'merchant_permission' => 0,
        'company_status' => 0,
        'syndicate' => 1
    ];
    $response = $this->post(route('admin::sub_admins::update', $lastId), $data);
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('message', 'Company Updated');
    //After editing
    $this->assertDatabaseMissing('users', [
        'email' => $lastData->email,
        'name' => $lastData->name
    ]);
    $this->assertDatabaseHas('users', [
        'email' => $companyEmail,
        'name' => $companyName
    ]);
    $response->assertStatus(302)
    ->assertRedirect(route('admin::sub_admins::index'));
});

test('delete company working as expected', function(){
    $lastData = User::latest('id')->first();
    $lastId = $lastData->id;
    $response = $this->post(route('admin::sub_admins::delete', $lastId));
    $response->assertSessionHas('message', 'Company Deleted!');
    $this->assertSoftDeleted($lastData);
})->skip();
