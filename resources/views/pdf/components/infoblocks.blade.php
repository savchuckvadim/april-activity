<div class="infoblocks">
    <style>
        .infoblocks-column {
            width: 300px
        }
    </style>
    <div class="infoblocks-wrapper">
        @if ($styleMode == 'list')
            @foreach ($complect as $group)
                @foreach ($group['value'] as $infoblock)
                    @if (array_key_exists('code', $infoblock))
                        @php
                            $currentInfoblock = $infoblocks->get($infoblock['code']);
                        @endphp
                        @if ($currentInfoblock)
                            @if ($descriptionMode === 0)
                                @if (!empty($infoblock['code']) && ($currentInfoblock = $infoblocks->get($infoblock['code'])))
                                    <p class="text-small">
                                        {{ $currentInfoblock['name'] }}
                                    </p>
                                @endif
                            @elseif ($descriptionMode === 1)
                                <h2>
                                    {{ $currentInfoblock['name'] }}
                                </h2>
                                <p>
                                    {{ $currentInfoblock['shortDescription'] }}
                                </p>
                            @else
                                <h2>
                                    {{ $currentInfoblock['name'] }}
                                </h2>
                                <p>
                                    {{ $currentInfoblock['descriptionForSale'] }}
                                </p>
                            @endif
                        @endif
                    @endif
                @endforeach
            @endforeach
        @elseif ($styleMode == 'table')
            @php
                $halfTotal = $totalCount['infoblocks'] / 2;
                $count = 0;
                $leftColumnItems = [];
                $rightColumnItems = [];

                $itemsPerColumn = 10; // Количество элементов на странице
                $leftColumnCount = count($leftColumnItems);
                $rightColumnCount = count($rightColumnItems);
            @endphp

            @foreach ($complect as $group)
                @foreach ($group['value'] as $infoblock)
                    @if (array_key_exists('code', $infoblock) && $infoblocks->has($infoblock['code']))
                        @php
                            $currentInfoblock = $infoblocks->get($infoblock['code']);
                            if ($count < $halfTotal) {
                                $leftColumnItems[] = $currentInfoblock;
                            } else {
                                $rightColumnItems[] = $currentInfoblock;
                            }
                            $count++;
                        @endphp
                    @endif
                @endforeach
            @endforeach


            @foreach ([$leftColumnItems, $rightColumnItems] as $columnIndex => $columnItems)
                {{-- Начало новой "страницы" для каждой колонки --}}
                @php
                    $pageCount = ceil(count($columnItems) / $itemsPerColumn); // Вычисляем количество "страниц"
                @endphp

                @for ($page = 0; $page < $pageCount; $page++)
                    <div class="page-content">
                        <table>
                            <tr>
                                <td class="infoblocks-column">
                                    @foreach ($columnItems as $index => $item)
                                        @if ($index >= $page * $itemsPerColumn && $index < ($page + 1) * $itemsPerColumn)
                                            {{-- Вывод элементов текущей "страницы" --}}
                                            <div
                                                class="{{ $descriptionMode === 1 || $descriptionMode > 1 ? 'text-normal color' : 'text-normal' }}">
                                                {{ $item['name'] }}
                                            </div>
                                            @if ($descriptionMode === 1)
                                                <div class="text-small">{{ $item['shortDescription'] }}</div>
                                            @elseif ($descriptionMode > 1)
                                                <div class="text-small">{{ $item['descriptionForSale'] }}</div>
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                    </div>
                    @if ($page < $pageCount - 1)
                        <div class="page-break"></div> {{-- Добавляем разрыв страницы между "страницами" --}}
                    @endif
                @endfor
            @endforeach

        @endif
    </div>
