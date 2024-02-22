<div class="infoblocks">
    <style>
        .infoblocks-wrapper,


        h3 {
            margin-bottom: 10px
        }

        .infoblocks-column {
            width: 50%
        }

        .infoblocks-column-big-title {
            text-align: center;
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


        }

        .infoblock-table-big-single {
            text-align: left
        }

        .infoblock-list-group-title {
            margin-top: 20px;
            margin-bottom: 5px;
            text-align: center
        }

        .infoblock--title {
            margin-top: 5px;
            margin-bottom: 5px;

        }
    </style>
    <div class="infoblocks-wrapper">
        @php
            $lastGroupName = null;
        @endphp
        @if ($styleMode == 'list')
            @foreach ($pages as $index => $page)
                <div class="page-content">
                    @if ($index == 0)
                        <h3>Информационное наполнение</h3>
                    @endif
                    @foreach ($page['groups'] as $group)
                        @if ($group['name'] !== $lastGroupName)
                            <p
                                class="{{ $descriptionMode !== 0 ? 'text-large infoblock-list-group-title color' : 'text-normal color' }}">
                                {{ $group['name'] }}
                            </p>
                            @php
                                $lastGroupName = $group['name'];
                            @endphp
                        @endif





                        @foreach ($group['items'] as $infoblock)
                            <p
                                class="{{ $descriptionMode !== 0 ? 'text-normal infoblock--title bold' : 'text-normal ' }}">
                                {{ $infoblock['name'] }}
                            </p>

                            @if ($descriptionMode === 1)
                                <p class="text-normal small">
                                    {{ $infoblock['shortDescription'] }}
                                </p>
                            @elseif ($descriptionMode === 2 || $descriptionMode === 3)
                                <p class="text-normal small">
                                    {{ $infoblock['descriptionForSale'] }}
                                </p>
                            @endif
                        @endforeach
                    @endforeach

                    @if (!$loop->last)
                        <div class="page-break"></div>
                    @endif

                </div>
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
                                @foreach ($page['groups'] as $group)
                                    @foreach ($group['items'] as $index => $item)
                                        @if ($index < count($group['items']) / 2)
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
                                @endforeach
                            </td>
                            <td class="infoblocks-column">
                                @foreach ($page['groups'] as $group)
                                    @foreach ($group['items'] as $index => $item)
                                        @if ($index >= count($group['items']) / 2)
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
                            @if ($item['shortDescription'])
                                <tr>
                                    <td class="infoblocks-column-big-title">

                                        <div
                                            class="{{ $descriptionMode === 1 || $descriptionMode > 1 ? 'text-normal bold' : 'text-normal infoblock-table-big-single' }}">
                                            {{ $item['name'] }}
                                        </div>


                                    </td>

                                </tr>

                                @if ($descriptionMode === 1)
                                    <tr>
                                        <td class="infoblocks-column-big">
                                            <div class="text-small">{{ $item['shortDescription'] }}</div>
                                        </td>

                                    </tr>
                                @elseif ($descriptionMode > 1)
                                    <tr>
                                        <td class="infoblocks-column-big">
                                            <div class="text-small">{{ $item['descriptionForSale'] }}</div>
                                        </td>

                                    </tr>
                                @endif
                            @endif
                        @endforeach
                    </table>
                </div>
                @if (!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach



        @endif
    </div>
