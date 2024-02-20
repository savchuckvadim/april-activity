<div class="letter">
    <div class="letter-rq">
        <div class="letter-rq-left">
            <p>
                {{$letterData['documentNumber']}}
            </p>
        </div>

        <div class="letter-rq-right">
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

        <div class="letter-text">
            @if ($letterData['recipientCase'] !== null)
            <div class="letter-title-wrapper">
                <h2>
                    {{$letterData['recipientName']}}
                </h2>
            </div>
            @endif

            <div class="letter-text-wrapper">
                <p class="text-small">
                    {{$letterData['text']}}
                </p>
            </div>
        </div>

    </div>

</div>