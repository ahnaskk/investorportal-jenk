<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Merchant;
use App\User;
use Faker\Factory;
use Tests\TestCase;

class FundingControllerTest extends TestCase
{
    /**
     * @test
     */
    public function about_us_returns_an_ok_response()
    {
        $response = $this->get('/fundings/about-us');
        $response->assertOk();
        $response->assertViewIs('funding.about-us')->assertStatus(200);
    }

    /**
     * @test
     */
    public function check_login_returns_an_ok_response()
    {
        $faker = Factory::create();
        $user = User::factory()->create(['name' => $faker->firstName, 'cell_phone' => $faker->phoneNumber, 'password' => 'secret', 'company' => 284, 'investor_type' => 5, 'active_status' => 0, 'notification_recurence' => 3, 'management_fee' => 2, 'global_syndication' => 2, 's_prepaid_status' => 2, 'file_type' => 1, 'display_value' => 'mid']);
        $response = $this->post('fundings/login', ['email' => $user->email, 'password' => 'secret']);
        //redirecting to bank as no bank is attached to the newly crated account otherwise would redirect to fundnigs homepage
        $response->assertRedirect('/fundings/updatebank')->assertStatus(302);
    }

    /**
     * @test
     */
    public function contact_us_returns_an_ok_response()
    {
        $response = $this->get('/fundings/contact-us');
        $response->assertOk();
        $response->assertViewIs('funding.contact-us')->assertStatus(200);
    }

    /**
     * @test
     */
    public function do_signup_returns_an_ok_response()
    {
        $faker = Factory::create();
        $response = $this->post('/fundings/signup', ['name' => $faker->firstName, 'cell_phone' => $faker->phoneNumber, 'email' => $faker->email, 'password' => 'secret', 'company' => 284, 'investor_type' => 5, 'active_status' => 0, 'notification_recurence' => 3, 'management_fee' => 2, 'global_syndication' => 2, 's_prepaid_status' => 2, 'file_type' => 1, 'display_value' => 'mid', '_token'=>csrf_token()]);
        $response->assertRedirect('/fundings/updatebank')->assertStatus(302);
    }

    /**
     * @test
     */
    public function forgot_password_returns_an_ok_response()
    {
        $response = $this->get('/fundings/forgot-password');
        $response->assertOk();
        $response->assertViewIs('funding.forgot-password')->assertStatus(200);
    }

    /**
     * @test
     */
    public function how_it_works_returns_an_ok_response()
    {
        $response = $this->get('fundings/how-it-works');
        $response->assertOk();
        $response->assertViewIs('funding.how-it-works')->assertStatus(200);
    }

    /**
     * @test
     */
    public function index_returns_an_ok_response()
    {
        $response = $this->get('/fundings');
        $response->assertOk();
        $response->assertViewIs('funding.index')->assertStatus(200);
        $response->assertViewHas('merchants');
    }

    /**
     * @test
     */
    public function login_returns_an_ok_response()
    {
        $response = $this->get('/fundings/login');
        $response->assertOk();
        $response->assertViewIs('funding.login')->assertStatus(200);
    }

    /**
     * @test
     */
    public function logout_returns_an_ok_response()
    {
        $response = $this->get('/fundings/logout');
        $response->assertRedirect('/fundings/login')->assertStatus(302);
    }

    /**
     * @test
     */
    public function marketplace_returns_an_ok_response()
    {
        $response = $this->get('/fundings/marketplace/{industry}');
        $response->assertOk();
        $response->assertViewIs('funding.marketplace');
        $response->assertViewHas('merchants');
        $response->assertViewHas('industry_filter');
        $response->assertViewHas('factor_rate_filter');
        $response->assertViewHas('monthly_revenue_filer');
        $response->assertViewHas('post_data');
        $response->assertViewHas('factor_rate');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function marketplace_details_returns_an_ok_response()
    {
        $merchants = Merchant::leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->select('merchants.*')->where('active_status', 1)->where('marketplace_status', 1)->where('merchants.sub_status_id', '=', 1)->distinct()->first();
        $response = $this->get("/fundings/$merchants->id/marketplace-details");
        $response->assertOk();
        $response->assertViewIs('funding.marketplace-details');
        $response->assertViewHas('id');
        $response->assertViewHas('merchant');
        $response->assertViewHas('fundings');
        $response->assertViewHas('hasFunded');
        $response->assertViewHas('merchant_market_data');
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function privacy_policy_returns_an_ok_response()
    {
        $response = $this->get('/fundings/privacy-policy');
        $response->assertOk();
        $response->assertViewIs('funding.privacy-policy')->assertStatus(200);
    }

    /**
     * @test
     */
    public function profile_returns_an_ok_response()
    {
        $investor = User::select('users.*')->rightJoin('bank_details', 'bank_details.investor_id', 'users.id')
                        ->where('users.investor_type', 5)
                        ->where('users.company', 284)
                        ->where('users.active_status', 1)
                        ->first();
        $response = $this->actingAs($investor)->get('/fundings/profile');
        $response->assertOk();
        $response->assertViewIs('funding.profile')->assertStatus(200);
    }

    /**
     * @test
     */
    public function signup_returns_an_ok_response()
    {
        $response = $this->get('/fundings/signup');
        $response->assertOk();
        $response->assertViewIs('funding.signup')->assertStatus(200);
    }

    /**
     * @test
     */
    public function terms_and_condition_returns_an_ok_response()
    {
        $response = $this->get('/fundings/terms-and-condition');
        $response->assertOk();
        $response->assertViewIs('funding.terms-and-condition')->assertStatus(200);
    }

    /**
     * @test
     */
    public function update_bank_returns_an_ok_response()
    {
        $investor = User::select('users.*')->rightJoin('bank_details', 'bank_details.investor_id', 'users.id')
                        ->where('users.investor_type', 5)
                        ->where('users.company', 284)
                        ->where('users.active_status', 1)
                        ->first();
        $response = $this->actingAs($investor)->get('/fundings/updatebank');
        $response->assertOk();
        $response->assertViewIs('funding.update_bank')->assertStatus(200);
    }
}
