<?php

namespace Tests\Unit\Providers\Filament;

use Tests\TestCase;

/**
 * Smoke check: AdminPanelProvider class exists and is registered.
 * Behavior (redirect / brand / dashboard render) is covered in
 * tests/Feature/AdminPanelTest.php.
 */
class AdminPanelProviderTest extends TestCase
{
    public function test_provider_class_exists_and_extends_panel_provider(): void
    {
        $this->assertTrue(class_exists(\App\Providers\Filament\AdminPanelProvider::class));
        $this->assertTrue(
            is_subclass_of(
                \App\Providers\Filament\AdminPanelProvider::class,
                \Filament\PanelProvider::class
            )
        );
    }
}
