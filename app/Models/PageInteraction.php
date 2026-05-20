<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One click or scroll event captured on the public site, for the
 * click/scroll heatmap. See the create_page_interactions migration
 * for the column contract.
 */
class PageInteraction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'type',
        'x_pct',
        'y_pct',
        'scroll_pct',
        'viewport_w',
        'ip_hash',
        'is_bot',
        'created_at',
    ];

    protected $casts = [
        'x_pct' => 'integer',
        'y_pct' => 'integer',
        'scroll_pct' => 'integer',
        'viewport_w' => 'integer',
        'is_bot' => 'boolean',
        'created_at' => 'datetime',
    ];
}
