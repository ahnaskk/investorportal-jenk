<?php

namespace Tests\Unit\Http\Requests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see \App\Http\Requests\AdminCreateOfferRequest
 */
class AdminCreateOfferRequestTest extends TestCase
{
    /** @var \App\Http\Requests\AdminCreateOfferRequest */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \App\Http\Requests\AdminCreateOfferRequest();
    }

    /**
     * @test
     */
    public function authorize()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $actual = $this->subject->authorize();

        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function rules()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $actual = $this->subject->rules();

        $this->assertValidationRules([
            'offer' => [
                'required',
            ],
        ], $actual);
    }

    // test cases...
}