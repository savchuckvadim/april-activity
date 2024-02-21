<div class="infoblocks">
    <style>
        .infoblocks-wrapper,


        h3 {
            margin-bottom: 10px
        }

        .infoblocks-column {
            width: 50%
        }

        table {
            vertical-align: top;
            margin: 0px;
            width: 100%;
            border-collapse: collapse;
            /* Убирает двойные бордеры между ячейками */
        }

        th,
        td {
            vertical-align: top;
            border: 1px solid black;
            padding: 3px;
            text-align: left;

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
            @foreach ($pages as $index => $page)
                <div class="page-content">
                    @if ($index == 0)
                        <h3>Информационное наполнение</h3>
                    @endif
                    <table>
                        <tr>
                            <td class="infoblocks-column">
                                @foreach ($page as $index => $item)
                                    @if ($index < count($page) / 2)
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
                            <td class="infoblocks-column">
                                @foreach ($page as $index => $item)
                                    @if ($index >= count($page) / 2)
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
                @if (!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach
        @elseif ($styleMode == 'tableWithGroup')
            @foreach ($pages as $index => $page)
                <div class="page-content">
                    @if ($index == 0)
                        <h3>Информационное наполнение</h3>
                    @endif
                    <table>
                        @foreach ($page as $index => $item)
                            <tr>
                                <td class="infoblocks-column-big">

                                    <div
                                        class="{{ $descriptionMode === 1 || $descriptionMode > 1 ? 'text-normal color' : 'text-normal' }}">
                                        {{ $item['name'] }}
                                    </div>
                                    @if ($descriptionMode === 1)
                                        <div class="text-small">{{ $item['shortDescription'] }}</div>
                                    @elseif ($descriptionMode > 1)
                                        <div class="text-small">{{ $item['descriptionForSale'] }}</div>
                                    @endif

                                </td>

                            </tr>
                        @endforeach
                    </table>
                </div>
                @if (!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach



        @endif
    </div>
