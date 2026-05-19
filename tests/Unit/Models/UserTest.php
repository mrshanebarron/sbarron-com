<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests pin Filament admin access: only the registered Shane email
 * gets into the panel even if some other User row is somehow created.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_shane_email_can_access_filament_admin_panel(): void
    {
        $shane = User::create([
            'name' => 'Shane Barron',
            'email' => 'mrshanebarron@gmail.com',
            'password' => bcrypt('whatever'),
        ]);

        $this->assertTrue($shane->canAccessPanel($this->fakePanel('admin')));
    }

    public function test_other_email_cannot_access_filament_admin_panel(): void
    {
        $other = User::create([
            'name' => 'Random',
            'email' => 'random@example.com',
            'password' => bcrypt('whatever'),
        ]);

        $this->assertFalse($other->canAccessPanel($this->fakePanel('admin')));
    }

    private function fakePanel(string $id): Panel
    {
        $panel = new Panel();
        $panel->id($id);
        return $panel;
    }
}
