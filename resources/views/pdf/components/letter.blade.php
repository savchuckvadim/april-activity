<style>
    .letter {
        margin-top: 7px;
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
</style>


<div class="letter">
    <div class="row letter-rq">
        <div class="rowItem">
            <div class="cellItem letter-rq-left">
                <div class="rq_wrapper">
                    <p class="text-small">
                        {{ $letterData['documentNumber'] }}
                    </p>
                </div>

            </div>
            <div class="cellItem"></div>
            <div class="cellItem letter-rq-right">
                @if ($letterData['companyName'] !== null)
                    <p class="text-small">
                        {{ $letterData['companyName'] }}
                    </p>
                @endif
                @if ($letterData['inn'] !== null)
                    <p class="text-small">
                        {{ $letterData['inn'] }}
                    </p>
                @endif
                @if ($letterData['positionCase'] !== null)
                    <p class="text-small">
                        {{ $letterData['positionCase'] }}
                    </p>
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
        @if ($letterData['recipientCase'] !== null)
            <div class="letter-title-wrapper">
                <p class='text-large bold recipientName'>
                    {{ $letterData['recipientName'] }} !
                </p>
            </div>
        @endif

        <div class="letter-text-wrapper">
            @php
                $letterText = $letterData['text'];
                $parts = preg_split('/<color>|<\/color>/', $letterText);
                $inHighlight = false;
            @endphp

            @foreach ($parts as $index => $part)
                @php
                    $isLastPart = $index === count($parts) - 1;
                @endphp

                {{-- Замена \n на <br> и обертывание каждой части в span --}}
                {!! $inHighlight ? '<span class="color text-normal">' : '<span class="text-normal">' !!}
                {!! nl2br(e($part)) !!}
                </span>

                {{-- Переключаем флаг выделения для следующей итерации --}}
                @if (!$isLastPart)
                    @php $inHighlight = !$inHighlight @endphp
                @endif
            @endforeach
        </div>
    </div>
</div>
