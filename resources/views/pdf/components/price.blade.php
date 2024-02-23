<style>
    .prices {
        margin-bottom: 20px;
    }

    .price-row,
    .price-cell {
        height: 30px;
    }

    .price-cell-first {
        text-align: start;
        vertical-align: middle;
        width: 250px;
    }

    .price-cell-short {
        width: 25px;
    }

    .price-cell,
    .price-cell-short,
    .price-cell-head {
        text-align: center;
        vertical-align: middle;
    }

    .price-cell-head {
        padding: 5px;
        vertical-align: middle;
    }

    .total-area {
        margin-top: 15px;
        width: 100%;
        text-align: right;
    }

    .total {

        text-align: right;
    }
</style>

<div class="prices page-content">

    <h3>Цена за комплект</h3>
    @if ($isTable)

        <table class="price-table">
            <tr>
                @foreach ($allPrices['general'][0]['cells'] as $priceCell)
                    @php

                        $classname = 'price-cell-head';
                    @endphp

                    @switch($priceCell['code'])
                        @case('name')
                            @php
                                $classname = 'price-cell-head  price-cell-first';
                            @endphp
                        @break

                        @case('quantity')
                            @php
                                $classname = 'price-cell-head  price-cell-short';
                            @endphp
                        @break

                        @case('measure')
                            @php
                                $classname = 'price-cell-head  price-cell-short';
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
            @foreach ([$allPrices['general'], $allPrices['alternative']] as $target)
                @if (is_array($target) && !empty($target))
                    @foreach ($target as $product)
                        @if ($product)
                            @if (is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells']))
                                <tr class="price-row">
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
                                                    $classname = $classname . ' price-cell-short';
                                                @endphp
                                            @break

                                            @case('measure')
                                                @php
                                                    $classname = $classname . ' price-cell-short';
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
                @endif
            @endforeach
        </table>

        @if ($withTotal)
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
                        {!! $inHighlight ? '<span class="red text-normal">' : '<span class="text-normal">' !!}
                        {!! nl2br(e($part)) !!}
                        </span>

                        @if (!$isLastPart)
                            @php $inHighlight = !$inHighlight @endphp
                        @endif
                    @endforeach
                </p>
            </div>
        @endif
    @endif
</div>
