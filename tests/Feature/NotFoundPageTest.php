<?php

namespace Tests\Feature;

use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    public function test_not_found_page_renders_branded_view(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Page not found', false);
        $response->assertSee('Go to homepage', false);
        $response->assertSee('Church sign in', false);
    }
}
