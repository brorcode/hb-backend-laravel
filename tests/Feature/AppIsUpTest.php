<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppIsUpTest extends TestCase
{
    public function testTheApplicationReturnsASuccessfulResponse(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
