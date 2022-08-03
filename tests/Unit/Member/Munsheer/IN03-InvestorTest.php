<?php
ini_set('memory_limit', '-1');
use App\Library\Repository\RoleRepository;
use App\User;
use Faker\Factory;
use Illuminate\Http\Response;

beforeEach(function () {
    $user = User::first();
    $this->actingAs($user);
    $this->userId = $user->id;
});

it('has all accounts page', function () {
    $result = $this->get(route('admin::investors::index'));
    $result->assertStatus(200);
    $result->assertViewIs('admin.investors.index');
});

test('all accounts listing correctly', function () {
    $param = [
        'active_status' => 1,
        'active_status_companies' => 1,
        'notification_recurence' => 0
    ];
    $result = $this->postJson(route('admin::investors::index'), $param);
    $result->assertStatus(200);
});

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
        $data=[
            'name' =>$label,
            'flag' => 1
        ];
        $this
        ->actingAs($user)
        ->post(route('admin::label::storeCreate'),$data)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin::label::index'))
        ->assertStatus(Response::HTTP_FOUND)
        ;
    }
});
test('all accounts creation working correctly', function () {
    $role = new RoleRepository;
    $companies = $role->allSubAdmin();
 
    foreach($companies as $company) 
    {
        for($i = 0;$i < 2; $i++)
        {
            $faker = Factory::create();
            $this->password = $faker->password;
            $this->email = $faker->email;
            $this->name = $faker->name;
            $this->phone = $faker->phoneNumber;

            $param = [
                'name' => $this->name,
                'contact_person' => $this->name,
                'cell_phone' => $this->phone,
                'global_syndication' => '',
                's_prepaid_status' => 2,
                'management_fee' => '',
                'role_id' => User::INVESTOR_ROLE,
                'investor_type' => 1,
                'agreement_date' => '',
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password,
                'creator_id' => $this->userId,
                'company' => $company->id,
                'notification_email' => $this->email,
                'auto_syndicate_payment' => 0,
                'notification_recurence' => 1,
                'file_type' => 1,
                'label' => [3],
                'show_name_mid' => 'mid',
                'active_status' => 1,
                'login_board' => 'new'
            ];
            $result = $this->post(route('admin::investors::storeCreate'), $param);
            $result->assertSessionHasNoErrors();

            $createdUser = User::latest('id')->first();

            $result->assertStatus(302)
            ->assertRedirect(route('admin::investors::portfolio', $createdUser->id));

            $this->assertDatabaseHas('users', [
                'email' => $this->email,
                'active_status' => 1,
                'creator_id' => $this->userId,
                'name' => $this->name,
                'company' => $company->id,
                'cell_phone' => $this->phone
            ]);
            $this->assertDatabaseHas('user_details', [
                'user_id' => $createdUser->id,
                'liquidity' => 0,
                'liquidity_adjuster' => 0
            ]);
            $this->assertDatabaseHas('user_has_roles', [
                'model_id' => $createdUser->id,
                'role_id' => User::INVESTOR_ROLE,
                'model_type' => "App\\User",
            ]);   
        }
    }
    $investor_count = $role->countInvestors([]);
    expect($investor_count)->toBe(6);
});

it('will create agent fee role and overpayment accounts', function () {
    $role = new RoleRepository;
    $companies = $role->allSubAdmin();

    $faker = Factory::create();
    $this->password = $faker->password;
    $this->name = $faker->name;
    $this->phone = $faker->phoneNumber;

    $param = [
        'name' => 'Agent Fee account',
        'contact_person' => $this->name,
        'cell_phone' => $this->phone,
        'global_syndication' => '',
        's_prepaid_status' => 2,
        'management_fee' => '',
        'role_id' => User::AGENT_FEE_ROLE,
        'investor_type' => 1,
        'agreement_date' => '',
        'email' => 'agentfee@account.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
        'creator_id' => $this->userId,
        'company' => $companies[0]->id,
        'notification_email' => 'agentfee@account.com',
        'auto_syndicate_payment' => 0,
        'notification_recurence' => 1,
        'file_type' => 1,
        'label' => '',
        'show_name_mid' => 'mid',
        'active_status' => 1,
        'login_board' => 'new'
    ];
    $result = $this->post(route('admin::investors::storeCreate'), $param);
    $result->assertSessionHasNoErrors();
    $createdUser = User::latest('id')->first();

    $result->assertStatus(302)
    ->assertRedirect(route('admin::investors::portfolio', $createdUser->id));

    $this->assertDatabaseHas('users', [
        'email' => 'agentfee@account.com',
        'active_status' => 1,
        'creator_id' => $this->userId,
        'name' => 'Agent fee account',
        'company' => $companies[0]->id,
        'cell_phone' => $this->phone
    ]);
    $this->assertDatabaseHas('user_details', [
        'user_id' => $createdUser->id,
        'liquidity' => 0,
        'liquidity_adjuster' => 0
    ]);
    $this->assertDatabaseHas('user_has_roles', [
        'model_id' => $createdUser->id,
        'role_id' => User::AGENT_FEE_ROLE,
        'model_type' => "App\\User",
    ]);  
    
    $param = [
        'name' => 'Overpayment account',
        'contact_person' => $this->name,
        'cell_phone' => $this->phone,
        'global_syndication' => '',
        's_prepaid_status' => 2,
        'management_fee' => '',
        'role_id' => User::OVERPAYMENT_ROLE,
        'investor_type' => 1,
        'agreement_date' => '',
        'email' => 'overpayment@account.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
        'creator_id' => $this->userId,
        'company' => $companies[0]->id,
        'notification_email' => 'overpayment@account.com',
        'auto_syndicate_payment' => 0,
        'notification_recurence' => 1,
        'file_type' => 1,
        'label' => '',
        'show_name_mid' => 'mid',
        'active_status' => 1,
        'login_board' => 'new'
    ];
    $result = $this->post(route('admin::investors::storeCreate'), $param);
    $result->assertSessionHasNoErrors();
    $createdUser = User::latest('id')->first();

    $result->assertStatus(302)
    ->assertRedirect(route('admin::investors::portfolio', $createdUser->id));

    $this->assertDatabaseHas('users', [
        'email' => 'overpayment@account.com',
        'active_status' => 1,
        'creator_id' => $this->userId,
        'name' => 'Overpayment account',
        'company' => $companies[0]->id,
        'cell_phone' => $this->phone
    ]);
    $this->assertDatabaseHas('user_details', [
        'user_id' => $createdUser->id,
        'liquidity' => 0,
        'liquidity_adjuster' => 0
    ]);
    $this->assertDatabaseHas('user_has_roles', [
        'model_id' => $createdUser->id,
        'role_id' => User::OVERPAYMENT_ROLE,
        'model_type' => "App\\User",
    ]);
});
