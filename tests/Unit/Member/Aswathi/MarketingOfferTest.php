<?php

use App\Library\Repository\RoleRepository;
use App\User;
use Faker\Factory;
use Illuminate\Http\Response;
use App\Merchant;
use App\Template;


beforeEach(function () {
    $user = User::first();
    $this->actingAs($user);
    $this->userId = $user->id;
});

it('has all market offers', function () {
    $result = $this->get(route('admin::merchantMarketOfferList'));
    $result->assertStatus(200);
    $result->assertViewIs('admin.market_offers.merchantOffersList');
});

it('it will create merchant market offers', function () {
$faker = Factory::create();
$Merchant = Merchant::first();
$merchant_id = $Merchant->id;
$template = Template::where('type','email')->first();
$param = [
    'offer_id' => '',
    'title' => $faker->title,
    'merchants' => [$merchant_id],
    'type' => 'email',
    'template' => $template->id,
    'offer' => "Offer",
    
];
$result = $this->post(route('admin::addUpdateMerchantMarketOffer'), $param);

$result->assertSessionHasNoErrors();
});


