<?php

namespace Tests\Unit\Http\Requests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see \App\Http\Requests\Api\PostChangeMerchantMerchantRequest
 */
class PostChangeMerchantMerchantRequestTest extends TestCase
{
    /** @var \App\Http\Requests\Api\PostChangeMerchantMerchantRequest */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \App\Http\Requests\Api\PostChangeMerchantMerchantRequest();
    }

    /**
     * @test
     */
    public function rules()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $actual = $this->subject->rules();

        $this->assertValidationRules([
            'merchant_id' => [
                'required',
            ],
        ], $actual);
    }

    // test cases...
}