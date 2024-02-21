<div class="prices page-content">
    <h3>PRICES</h3>
    @if ($isTable)

        <table>
            <tr>
                @foreach ($allPrices['general'][0]['cells'] as $priceCell)
                    <td class="price-cell-head">

                        {{ $priceCell['name'] }}
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
                                        @endphp

                                        @switch($cell['code'])
                                            @case('discountprecent')
                                                @php
                                                    $cellValue = $cell['value'];
                                                    $variableFloat = floatval($cellValue);
                                                    $result = 100 - 100 * $variableFloat;
                                                    $value = round($result, 2);

                                                @endphp
                                            @break

                                            @default
                                            @break
                                        @endswitch
                                        <td class="price-cell">
                                            {{ $value }}

                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach
        </table>

    @endif
</div>
