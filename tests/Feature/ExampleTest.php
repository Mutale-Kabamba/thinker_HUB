<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Thinker Hub');
    }
}
