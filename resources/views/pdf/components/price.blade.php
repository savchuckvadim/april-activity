<style>
    .prices {
        margin-bottom: 0px;
    }

    .price-content {
        margin-top: 40px;
        vertical-align: top;
    }

    .price-row,
    .price-cell {
        height: 30px;
    }

    .price-cell-first {
        padding: 2px;
        text-align: start;
        vertical-align: middle;
        width: 200px;
    }

    .price-cell-price {
        padding: 2px;
        text-align: center;
        vertical-align: middle;
        width: 55px;
    }

    .price-cell-short {
        width: 20px;
    }

    .price-cell-long-quantity {
        width: 95px;
    }

    .price-cell,
    .price-cell-short,
    .price-cell-head,
    .price-cell-long-quantity {
        text-align: center;
        vertical-align: middle;
    }

    .price-cell-head {
        padding: 2px;
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

    .sale-phrase-wrapper {
        margin-top: 15px;
    }
</style>

<div class="{{ $withPrice ? 'prices price-content' : 'prices page-content' }}">

    <h3>Цена за комплект</h3>
    @if ($isTable)

        <table class="price-table">
            <tr>
                @foreach ($allPrices['general'][0]['cells'] as $priceCell)
                    @php
                        $headValue = $priceCell['name'];
                        $classname = 'price-cell-head';
                    @endphp

                    @switch($priceCell['code'])
                        @case('name')
                            @php
                                $classname = 'price-cell-first';
                            @endphp
                        @break

                        @case('quantity')
                            @php

                                if (
                                    $priceCell['name'] == 'При заключении договора от' ||
                                    $priceCell['name'] == 'При внесении предоплаты от'
                                ) {
                                    $classname = 'price-cell-long-quantity';
                                } else {
                                    $headValue = 'Кол-во';
                                    // $classname = 'price-cell-short';
                                }

                            @endphp
                        @break

                        @case('defaultmonth')
                            @php
                                $classname = 'price-cell-price';
                            @endphp
                        @break

                        @case('default')
                            @php
                                $classname = 'price-cell-price';
                            @endphp
                        @break

                        {{-- @case('current')
                            @php
                                $classname = 'price-cell-price';
                            @endphp
                        @break --}}
                        @case('discountprecent')
                            @php
                                $classname = 'price-cell-short';

                            @endphp
                        @break

                        @case('measure')
                            @php
                                $headValue = 'Ед.';
                                // $classname = 'price-cell-short';
                            @endphp
                        @break

                        @default
                        @break
                    @endswitch
                    <td class={{ $classname }}>
                        <p class="text-small bold">
                            {{ $headValue }}
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

                                            {{-- @case('current')
                                                @php
                                                    $classname = 'price-cell-price';
                                                @endphp
                                            @break --}}
                                            @case('defaultmonth')
                                                @php
                                                    $classname = 'price-cell-price';
                                                @endphp
                                            @break

                                            @case('default')
                                                @php
                                                    $classname = 'price-cell-price';
                                                @endphp
                                            @break

                                            @case('quantity')
                                                @php
                                                    if (
                                                        $priceCell['name'] == 'При заключении договора от' ||
                                                        $priceCell['name'] == 'При внесении предоплаты от'
                                                    ) {
                                                        $classname = 'price-cell-long-quantity';
                                                    } else {
                                                        // $classname = 'price-cell-short';
                                                    }

                                                @endphp
                                            @break

                                            @case('measure')
                                                @php
                                                    // $classname = 'price-cell-short';
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
                        {!! $inHighlight ? '<span class="red text-normal">' : '<span class="text-normal italic">' !!}
                        {!! nl2br(e($part)) !!}
                        </span>

                        @if (!$isLastPart)
                            @php $inHighlight = !$inHighlight @endphp
                        @endif
                    @endforeach
                </p>
            </div>
        @endif
        @php
            $salePhraseText = str_replace("\\n", "\n", $salePhrase);

            // Разбиение текста на части с сохранением разделителей
            $parts = preg_split(
                '/(<red>|<\/red>|<blue>|<\/blue>|<bold>|<\/bold>)/',
                $salePhraseText,
                -1,
                PREG_SPLIT_DELIM_CAPTURE,
            );

            $formattedText = '';
            $currentTags = [];

            foreach ($parts as $part) {
                switch ($part) {
                    case '<red>':
                    case '<blue>':
                    case '<bold>':
                        array_push($currentTags, trim($part, '<>'));
                        break;
                    case '</red>':
                    case '</blue>':
                    case '</bold>':
                        array_pop($currentTags);
                        break;
                    default:
                        // Обработка звездочек в начале строки и после переносов
                        $part = preg_replace("/(^|\n)\*/m", "$1•", $part);
                        if (!empty($currentTags)) {
                            $class = implode(' ', $currentTags);
                            $formattedText .= '<span class="' . $class . '">' . e($part) . '</span>';
                        } else {
                            $formattedText .= e($part);
                        }
                        break;
                }
            }

            // Добавление переносов строк
            $formattedText = nl2br($formattedText);
        @endphp
        <div class="sale-phrase-wrapper">
            <p class="text-large">
                {!! $formattedText !!}
            </p>
        </div>

    @endif
</div>
