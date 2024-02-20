<div class="letter">
    <div class="row letter-rq">
        <div class="rowItem letter-rq-left">
            <div class="rq_wrapper">
                <p class="text-small">
                    {{$letterData['documentNumber']}}
                </p>
            </div>

        </div>
        <div style="display: table-cell;"></div>
        <div class="rowItem letter-rq-right">
            @if ($letterData['companyName'] !== null)
            <p class="text-small">
                {{$letterData['companyName']}}
            </p>
            @endif
            @if ($letterData['inn'] !== null)
            <p class="text-small">
                {{$letterData['inn']}}
            </p>
            @endif
            @if ($letterData['positionCase'] !== null)
            <p class="text-small">
                {{$letterData['positionCase']}}
            </p>
            @endif
            @if ($letterData['recipientCase'] !== null)
            <p class="text-small">
                {{$letterData['recipientCase']}}
            </p>

            @endif
        </div>

    </div>
    <div class="letter-text">
        @if ($letterData['recipientCase'] !== null)
        <div class="letter-title-wrapper">
            <h2>
                {{$letterData['recipientName']}}
            </h2>
        </div>
        @endif

        <div class="letter-text-wrapper">
            @php
            $parts = preg_split('/<color>|<\ /color>/', $letterText);
                    $inHighlight = false;
                    @endphp

                    @foreach ($parts as $index => $part)
                    @php
                    $subparts = preg_split("/\r\n|\n|\r/", $part);
                    $isLastPart = $index === count($parts) - 1;
                    @endphp

                    @foreach ($subparts as $subpartIndex => $subpart)
                    <p class="{{ $inHighlight ? 'color text-small' : 'text-small' }}">
                        {{ $subpart }}
                    </p>

                    {{-- Убираем добавление разрыва строки для последней подстроки последней части --}}
                    @if (!($isLastPart && $subpartIndex === count($subparts) - 1))
                    <br>
                    @endif
                    @endforeach

                    {{-- Переключаем флаг выделения для следующей итерации --}}
                    @if (!$isLastPart)
                    @php $inHighlight = !$inHighlight @endphp
                    @endif
                    @endforeach

        </div>
    </div>
</div>