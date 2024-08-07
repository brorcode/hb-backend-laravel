<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testTheApplicationReturnsASuccessfulResponse(): void
    {
        $this->markTestIncomplete('This test is currently incomplete.');

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
