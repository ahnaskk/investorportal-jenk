<?php

it('has adminlogin page', function () {
    $response = $this->get('/adminlogin');

    $response->assertStatus(200);
});
