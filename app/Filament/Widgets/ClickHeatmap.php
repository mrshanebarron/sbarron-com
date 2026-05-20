<?php

namespace App\Filament\Widgets;

use App\Models\PageInteraction;
use Filament\Widgets\Widget;

/**
 * Click + scroll heatmap for one public page.
 *
 * Clicks are bucketed into a 20x20 grid (each cell = 5% of page width
 * x 5% of viewport height) and rendered intensity-coloured, so the
 * hot zones of a page are visible. Scroll depth is summarised as the
 * average furthest point visitors reached.
 *
 * `$path` is the selected page; it is wired to a <select> in the view
 * via Livewire so switching pages re-renders without a full reload.
 * Humans only (is_bot = 0).
 */
class ClickHeatmap extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.click-heatmap';

    /** Grid resolution: 20 cells per axis -> each cell is 5%. */
    private const GRID = 20;

    /** Selected page path. Livewire-bound to the picker in the view. */
    public string $path = '/';

    /**
     * @return array{
     *   grid: array<int, array<int, int>>, max: int, clickTotal: int,
     *   scrollAvg: int, scrollViewers: int, paths: array<int, string>, path: string
     * }
     */
    protected function getViewData(): array
    {
        // Distinct pages that have human click data — the picker options.
        $paths = PageInteraction::query()
            ->where('type', 'click')
            ->where('is_bot', false)
            ->distinct()
            ->orderBy('path')
            ->pluck('path')
            ->all();

        // Default the selection to a path that actually has data.
        if ($paths !== [] && ! in_array($this->path, $paths, true)) {
            $this->path = $paths[0];
        }

        // grid[col][row] = click count; col/row are 0..GRID-1.
        $grid = [];
        for ($c = 0; $c < self::GRID; $c++) {
            $grid[$c] = array_fill(0, self::GRID, 0);
        }

        $clicks = PageInteraction::query()
            ->where('type', 'click')
            ->where('is_bot', false)
            ->where('path', $this->path)
            ->get(['x_pct', 'y_pct']);

        $max = 0;
        $clickTotal = 0;
        foreach ($clicks as $click) {
            $x = (int) $click->x_pct;
            $y = (int) $click->y_pct;
            // 0-100 -> 0..GRID-1; clamp the 100 edge into the last cell.
            $col = min(self::GRID - 1, intdiv(max(0, min(100, $x)) * self::GRID, 100));
            $row = min(self::GRID - 1, intdiv(max(0, min(100, $y)) * self::GRID, 100));
            $grid[$col][$row]++;
            $clickTotal++;
            if ($grid[$col][$row] > $max) {
                $max = $grid[$col][$row];
            }
        }

        // Scroll depth — average of the deepest point each visit reached.
        $scrollStats = PageInteraction::query()
            ->where('type', 'scroll')
            ->where('is_bot', false)
            ->where('path', $this->path)
            ->selectRaw('AVG(scroll_pct) as avg_pct, COUNT(*) as viewers')
            ->first();

        $scrollViewers = (int) ($scrollStats->viewers ?? 0);
        $scrollAvg = $scrollViewers > 0 ? (int) round((float) $scrollStats->avg_pct) : 0;

        return [
            'grid' => $grid,
            'max' => $max,
            'clickTotal' => $clickTotal,
            'scrollAvg' => $scrollAvg,
            'scrollViewers' => $scrollViewers,
            'paths' => $paths,
            'path' => $this->path,
        ];
    }
}
