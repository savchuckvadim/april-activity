<style>
    .prices {
        margin-bottom: 100px;
    }
</style>

<div class="prices page-content">
    <style>
        .price-cell-first {
            width: 250px;
        }

        .price-cell-short {
            width: 25px;
        }

        .price-cell,
        .price-cell-short,
        .price-cell-head {
            text-align: center;
        }

        .price-cell-head {
            padding: 5px;
            vertical-align: center;
        }
    </style>
    <h3>Цена за комплект</h3>
    @if ($isTable)

        <table>
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
                                <tr>
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
                @endif
            @endforeach
        </table>

        @if ($withTotal)
            <p class="text-lrge bold">
                {{ $total }}
            </p>
        @endif
    @endif
</div>
