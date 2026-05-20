<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Click &amp; scroll heatmap
        </x-slot>

        <x-slot name="description">
            Where visitors click and how far they scroll, by page. Humans only.
        </x-slot>

        @if (empty($paths))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No click data yet. Once visitors interact with the public site, it shows up here.
            </p>
        @else
            {{-- Page picker --}}
            <div class="mb-4 flex items-center gap-2">
                <label for="heatmap-path" class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    Page
                </label>
                <select
                    id="heatmap-path"
                    wire:model.live="path"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                >
                    @foreach ($paths as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Scroll depth summary --}}
            <div class="mb-5">
                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                    <span>Average scroll depth</span>
                    <span>
                        {{ $scrollAvg }}%
                        @if ($scrollViewers > 0)
                            &middot; {{ number_format($scrollViewers) }} visit{{ $scrollViewers === 1 ? '' : 's' }}
                        @endif
                    </span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $scrollAvg }}%;"></div>
                </div>
            </div>

            {{-- Click grid --}}
            @if ($clickTotal === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No clicks recorded on this page yet.
                </p>
            @else
                <div class="overflow-x-auto"
                     title="Each cell is 5% of page width by 5% of viewport height">
                    <table style="border-collapse: separate; border-spacing: 2px;">
                        <tbody>
                            @for ($row = 0; $row < 20; $row++)
                                <tr>
                                    @for ($col = 0; $col < 20; $col++)
                                        @php
                                            $count = $grid[$col][$row] ?? 0;
                                            $ratio = $max > 0 ? $count / $max : 0;
                                            $step = $count === 0 ? 0 : (int) ceil($ratio * 4);
                                            $bg = [
                                                0 => 'background-color: rgb(243 244 246);',  // gray-100 — no clicks
                                                1 => 'background-color: rgb(254 215 170);',  // orange-200
                                                2 => 'background-color: rgb(251 146 60);',   // orange-400
                                                3 => 'background-color: rgb(234 88 12);',    // orange-600
                                                4 => 'background-color: rgb(154 52 18);',    // orange-900
                                            ][$step];
                                        @endphp
                                        <td class="rounded-[2px]"
                                            style="width: 22px; height: 22px; {{ $bg }}"
                                            title="{{ $count }} click{{ $count === 1 ? '' : 's' }}"></td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                    <span>Fewer</span>
                    <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(243 244 246);"></span>
                    <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(254 215 170);"></span>
                    <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(251 146 60);"></span>
                    <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(234 88 12);"></span>
                    <span class="inline-block rounded-sm" style="width:14px;height:14px;background-color:rgb(154 52 18);"></span>
                    <span>More</span>
                    <span class="ml-auto">{{ number_format($clickTotal) }} click{{ $clickTotal === 1 ? '' : 's' }} on this page</span>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
