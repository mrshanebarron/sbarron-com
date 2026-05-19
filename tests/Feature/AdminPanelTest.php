<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament admin panel lives at /admin behind auth.
 * Contract: GET /admin redirects to /admin/login when unauthenticated,
 * and the login page renders the Barron AI Solutions brand name.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_redirects_to_login_when_unauthenticated(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_renders_with_brand_name(): void
    {
        $resp = $this->get('/admin/login');
        $resp->assertOk();
        $resp->assertSeeText('Barron AI Solutions');
    }

    public function test_authenticated_shane_lands_on_dashboard(): void
    {
        $shane = User::create([
            'name' => 'Shane Barron',
            'email' => 'mrshanebarron@gmail.com',
            'password' => bcrypt('Rat9chet!'),
        ]);

        $resp = $this->actingAs($shane)->get('/admin');
        $resp->assertOk();
    }
}
