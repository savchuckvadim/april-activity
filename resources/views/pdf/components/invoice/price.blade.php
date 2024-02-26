<style>
    .prices {
        margin-bottom: 0px;
    }

    .price-row,
    .price-cell {
        height: 30px;
    }

    .total-cell {
        text-align: center;
        vertical-align: middle;
        height: 20px;
    }

    .price-cell-first {
        padding: 5px;
        text-align: start;
        vertical-align: middle;
        width: 350px;
    }

    .price-cell-short {
        width: 65px;
    }


    .price-cell,
    .price-cell-number,
    .price-cell-short,
    .price-cell-head,
    {
    text-align: center;
    vertical-align: middle;
    }

    .price-cell-head {
        padding: 5px;
        vertical-align: middle;
    }

    .price-cell-number {
        width: 25px;
    }

    .total-area {
        margin-top: 15px;
        width: 100%;
        text-align: right;
    }

    .total {

        text-align: right;
    }

    .total-first-cell {
        height: 20px;
        border: 0px;
    }
</style>

<div class="prices page-content">


    @if ($isTable)

        <table class="price-table">
            <tr>
                <td class="price-cell-head price-cell-number">
                    <p class="text-small bold">
                        №
                    </p>

                </td>
                @foreach ($allPrices[0]['cells'] as $priceCell)
                    @php

                        $classname = 'price-cell-head price-cell-head';
                    @endphp

                    @switch($priceCell['code'])
                        @case('name')
                            @php
                                $classname = 'price-cell-first';
                            @endphp
                        @break

                        @case('quantity')
                            @php
                                $classname = 'price-cell-short';
                            @endphp
                        @break

                        @case('measure')
                            @php
                                $classname = 'price-cell-short';
                            @endphp
                        @break

                        @default
                        @break
                    @endswitch
                    <td class={{ $classname }}>
                        <p class="text-small bold">
                            {{ $priceCell['name'] }}
                        </p>
                    </td>
                @endforeach
            </tr>
            {{-- @foreach ([$allPrices['general'], $allPrices['alternative']] as $target)
                @if (is_array($target) && !empty($target)) --}}
            @foreach ($allPrices as $index => $product)
                @if ($product)
                    @if (is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells']))
                        <tr class="price-row">
                            <td class="price-cell-number">
                                <p class="text-small bold">
                                    {{ $index + 1 }}.
                                </p>


                            </td>
                            @foreach ($product['cells'] as $cell)
                                @php
                                    $value = $cell['value'];
                                    $classname = 'price-cell';
                                @endphp

                                @switch($cell['code'])
                                    @case('name')
                                        @php
                                            $classname = 'price-cell-first';
                                        @endphp
                                    @break

                                    @case('quantity')
                                        @php
                                            $classname = 'price-cell-short';
                                        @endphp
                                    @break

                                    @case('measure')
                                        @php
                                            $classname = 'price-cell-short';
                                        @endphp
                                    @break

                                    @case('discountprecent')
                                        @php
                                            $classname = 'price-cell-short';
                                            $cellValue = $cell['value'];
                                            $variableFloat = floatval($cellValue);
                                            $result = 100 - 100 * $variableFloat;
                                            $value = round($result, 2);

                                        @endphp
                                    @break

                                    @default
                                    @break
                                @endswitch
                                <td class={{ $classname }}>
                                    <p class="text-small">
                                        {{ $value }}
                                    </p>


                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endif
            @endforeach
            {{-- @endif
            @endforeach --}}
            {{-- @if ($withTotal) --}}
            @php
                $lastCellClassname = $classname;
                $lastCellValue = $value;
            @endphp
            <!-- Добавляем новый ряд с одной ячейкой, аналогичной последней в предыдущем ряду -->
            <tr class="price-row" style="height:20px;">
                <td class="total-first-cell" colspan="5"></td> {{-- Пропускаем первые 5 столбцов --}}
                @php
                    $totalValue = 0;
                @endphp
                @foreach ($allPrices as $totalProduct)
                    @foreach ($totalProduct['cells'] as $totalCell)
                        @if (isset($totalCell['code']))
                            @if ($totalCell['code'] === 'prepaymentsum')
                                @php
                                    $totalValue = $totalValue + $totalCell['value'];
                                @endphp
                            @endif
                        @endif
                    @endforeach
                @endforeach
                <td class="total-cell">
                    <p class="text-small bold">
                       {{ $totalValue}}
                    </p>
                </td>

            </tr>
            {{-- @endif --}}
        </table>

        {{-- @if ($withTotal) --}}
        @php
            $totalText = $total;
            // Заменяем строковые литералы "\n" на реальные символы переноса строки
            $totalText = str_replace("\\n", "\n", $totalText);
            $parts = preg_split('/<color>|<\/color>/', $totalText);
            $inHighlight = false;
        @endphp
        <div class='total-area'>

            <p class="text-large total">
                <span class="bold">Итого: </span>

                @foreach ($parts as $index => $part)
                    @php
                        $isLastPart = $index === count($parts) - 1;
                    @endphp

                    {{-- Замена \n на <br> и обертывание каждой части в span --}}
                    {!! $inHighlight ? '<span class="bold  text-normal">' : '<span class="text-normal italic">' !!}
                    {!! nl2br(e($part)) !!}
                    </span>

                    @if (!$isLastPart)
                        @php $inHighlight = !$inHighlight @endphp
                    @endif
                @endforeach
            </p>
        </div>
        {{-- @endif --}}
    @endif
</div>
