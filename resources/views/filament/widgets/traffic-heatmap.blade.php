<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Traffic heatmap
        </x-slot>

        <x-slot name="description">
            Human pageviews by day and hour, last 30 days. Darker = busier.
        </x-slot>

        @if ($total === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No pageviews in the last 30 days yet.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full" style="border-collapse: separate; border-spacing: 3px; min-width: 640px;">
                    <thead>
                        <tr>
                            <th class="text-left" style="width: 44px;"></th>
                            @for ($h = 0; $h < 24; $h++)
                                <th class="text-[10px] font-normal text-gray-400 dark:text-gray-500 text-center">
                                    {{ $h % 3 === 0 ? str_pad((string) $h, 2, '0', STR_PAD_LEFT) : '' }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $dow => $dayLabel)
                            <tr>
                                <td class="text-[11px] font-medium text-gray-500 dark:text-gray-400 pr-3 text-right"
                                    style="width: 44px; white-space: nowrap;">
                                    {{ $dayLabel }}
                                </td>
                                @for ($h = 0; $h < 24; $h++)
                                    @php
                                        $count = $grid[$dow][$h] ?? 0;
                                        // Intensity 0..1 relative to the busiest cell.
                                        $ratio = $max > 0 ? $count / $max : 0;
                                        // Five steps so empty is clearly distinct from light traffic.
                                        $step = $count === 0 ? 0 : (int) ceil($ratio * 4);
                                        $bg = [
                                            0 => 'background-color: rgb(241 245 249);',   // slate-100 — empty
                                            1 => 'background-color: rgb(191 219 254);',   // blue-200
                                            2 => 'background-color: rgb(96 165 250);',    // blue-400
                                            3 => 'background-color: rgb(37 99 235);',     // blue-600
                                            4 => 'background-color: rgb(30 58 138);',     // blue-900
                                        ][$step];
                                    @endphp
                                    <td class="rounded-sm"
                                        style="height: 26px; {{ $bg }}"
                                        title="{{ $dayLabel }} {{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}:00 — {{ $count }} view{{ $count === 1 ? '' : 's' }}">
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                <span>Less</span>
                <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(241 245 249);"></span>
                <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(191 219 254);"></span>
                <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(96 165 250);"></span>
                <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(37 99 235);"></span>
                <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(30 58 138);"></span>
                <span>More</span>
                <span class="ml-auto">{{ number_format($total) }} views &middot; busiest hour {{ number_format($max) }}</span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
