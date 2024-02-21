<div class="infoblocks">
    <style>

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

            <table>
                <tr>
                    <td> {{-- Левая колонка --}}
                        @foreach ($leftColumnItems as $item)
                            @if ($descriptionMode === 0)
                                <p class="text-normal">{{ $item['name'] }}</p>
                            @elseif ($descriptionMode === 1)
                                <p class="text-normal color">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-small">
                                    {{ $item['shortDescription'] }}
                                </p>
                            @else
                                <p class="text-normal color">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-small">
                                    {{ $item['descriptionForSale'] }}
                                </p>
                            @endif
                        @endforeach
                    </td>
                    <td> {{-- Правая колонка --}}
                        @foreach ($rightColumnItems as $item)
                            <p class="text-normal">{{ $item['name'] }}</p>
                            {{-- Дополнительный контент для правой колонки --}}
                        @endforeach
                    </td>
                </tr>
            </table>

        @endif
    </div>
