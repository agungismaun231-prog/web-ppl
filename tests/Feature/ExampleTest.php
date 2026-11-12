<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_can_open_the_login_form(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_account_page_requires_login(): void
    {
        $this->get('/akun')->assertRedirect(route('login'));
    }
}
