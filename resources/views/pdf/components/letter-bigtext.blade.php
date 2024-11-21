<style>
    .letter {
        margin-top: 12px;
        margin-bottom: 20px;
        vertical-align: top;
    }

    .letter-rq-left {
        width: 300px;
    }

    .letter-rq-right {
        width: 300px;
        text-align: right;
    }

    .recipientName {
        margin-bottom: 5px;
        text-align: center
    }

    .letter-text {
        margin-top: 20px;
    }

    .text-xsmall {
        font-size: 9px;
    }

    .text-small {
        font-size: 10px;
    }

    .text-normal {
        font-size: 10px;
    }

    .text-large {
        font-size: 10px;
    }

    .color {
        font-weight: bold;
        color: rgb(48, 115, 230);
        /* color: rgb(58, 102, 172);  garant */


    }

    .shadow {
        /* font-weight: bold; */
        color: rgba(38, 62, 104, 0.775);

    }

    .red {
        /* font-weight: bold; */
        color: rgb(220, 26, 33);
    }

    .blue {
        /* font-weight: bold; */
        color: rgb(48, 115, 230);
        /* color: rgb(58, 102, 172);  garant */


    }

    .bold {
        font-weight: bold;

    }

    .text-xsmall,
    .text-small,
    .text-normal,
    .text-large,
    .color,
    .shadow,
    .red,
    .blue,
    .bold {
        line-height: 1px !important;
        /* Немного больше, чем размер шрифта */

    }
</style>


<div class="letter">
    <div class="row letter-rq">
        <div class="rowItem">
            <div class="cellItem letter-rq-left">
                <div class="rq_wrapper">
                    <p class="text-small">
                        {{ $letterData['documentNumber'] }}
                    </p>
                    <p class="text-small">
                        {{ $letterData['documentDate'] }}
                    </p>
                </div>

            </div>
            <div class="cellItem"></div>
            <div class="cellItem letter-rq-right">
                @if ($letterData['positionCase'] !== null)
                    <p class="text-small">
                        {{ $letterData['positionCase'] }}
                    </p>
                @endif
                @if ($letterData['companyName'] !== null)
                    <p class="text-small">
                        {{ $letterData['companyName'] }}
                    </p>
                @endif
                @if (isset($letterData['inn']))
                    @if ($letterData['inn'] !== null)
                        <p class="text-small">
                            {{ $letterData['inn'] }}
                        </p>
                    @endif
                @endif

                @if ($letterData['recipientCase'] !== null)
                    <p class="text-small">
                        {{ $letterData['recipientCase'] }}
                    </p>
                @endif

            </div>
        </div>


    </div>
    <div class="letter-text">
        @if ($letterData['appeal'] !== null)
            <div class="letter-title-wrapper">
                <p class='text-large bold recipientName'>
                    {{ $letterData['appeal'] }}
                </p>
            </div>
        @endif

        <div class="letter-text-wrapper">
            @php
                $letterText = $letterData['text'];
                $isLargeLetterText = $letterData['isLargeLetterText'];
                $baseClass = 'text-normal';
                if (!$isLargeLetterText) {
                    $baseClass = 'text-large';
                }
                $letterText = str_replace("\\n", "\n", $letterText);

                // Разбиваем по тегам, сохраняя их в результате
                $parts = preg_split(
                    '/(<color>|<\/color>|<red>|<\/red>|<bold>|<\/bold>)/',
                    $letterText,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE,
                );
                $inHighlight = false;
                $inBold = false;
                $inRed = false;
            @endphp

            @foreach ($parts as $part)
                @php
                    if ($part == '<color>') {
                        $inHighlight = true;
                        continue; // Пропускаем сам тег
                    } elseif ($part == '</color>') {
                        $inHighlight = false;
                        continue; // Пропускаем сам тег
                    }
                    if ($part == '<red>') {
                        $inRed = true;
                        continue; // Пропускаем сам тег
                    } elseif ($part == '</red>') {
                        $inRed = false;
                        continue; // Пропускаем сам тег
                    } elseif ($part == '<bold>') {
                        $inBold = true;
                        continue; // Пропускаем сам тег
                    } elseif ($part == '</bold>') {
                        $inBold = false;
                        continue; // Пропускаем сам тег
                    }

                    // Определяем классы для текущего фрагмента
                    $class = $baseClass;
                    if ($inHighlight) {
                        $class .= ' color';
                    }
                    if ($inBold) {
                        $class .= ' bold'; // Добавляем класс для жирного текста
                    }
                    if ($inRed) {
                        $class .= ' red'; // Добавляем класс для жирного текста
                    }
                @endphp

                {{-- Выводим содержимое $part с применением nl2br и экранированием --}}
                <span class="{{ $class }}">
                    {!! nl2br(e($part)) !!}
                </span>
            @endforeach
        </div>
    </div>
</div>
