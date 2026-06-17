<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guests hitting the root are redirected to the login screen.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    /**
     * The login screen renders for guests.
     */
    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('Mugi Jaya');
    }
}
