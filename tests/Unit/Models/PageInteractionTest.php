<?php

namespace Tests\Unit\Models;

use App\Models\PageInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the PageInteraction model.
 *
 * Contract:
 *   - The click/scroll fields are mass-assignable (the capture endpoint
 *     creates rows from request input).
 *   - Numeric fields cast to int, is_bot casts to bool, created_at to a
 *     date — so consumers (the heatmap widget) get typed values.
 *   - No updated_at column (timestamps disabled).
 */
class PageInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_event_is_mass_assignable_and_persists(): void
    {
        $row = PageInteraction::create([
            'path' => '/writing',
            'type' => 'click',
            'x_pct' => 42,
            'y_pct' => 73,
            'viewport_w' => 1440,
            'ip_hash' => 'abc',
            'is_bot' => false,
        ]);

        $this->assertDatabaseHas('page_interactions', [
            'id' => $row->id,
            'path' => '/writing',
            'type' => 'click',
            'x_pct' => 42,
            'y_pct' => 73,
        ]);
    }

    public function test_scroll_event_is_mass_assignable_and_persists(): void
    {
        $row = PageInteraction::create([
            'path' => '/',
            'type' => 'scroll',
            'scroll_pct' => 88,
            'viewport_w' => 390,
            'ip_hash' => 'def',
            'is_bot' => false,
        ]);

        $this->assertDatabaseHas('page_interactions', [
            'id' => $row->id,
            'type' => 'scroll',
            'scroll_pct' => 88,
        ]);
    }

    public function test_numeric_and_boolean_fields_are_cast(): void
    {
        $row = PageInteraction::create([
            'path' => '/',
            'type' => 'click',
            'x_pct' => '10',
            'y_pct' => '20',
            'viewport_w' => '800',
            'is_bot' => 1,
        ]);

        $fresh = $row->fresh();

        $this->assertIsInt($fresh->x_pct);
        $this->assertIsInt($fresh->y_pct);
        $this->assertIsInt($fresh->viewport_w);
        $this->assertIsBool($fresh->is_bot);
        $this->assertTrue($fresh->is_bot);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->created_at);
    }
}
