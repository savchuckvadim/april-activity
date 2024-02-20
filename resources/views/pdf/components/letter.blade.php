<div class="letter">
    <div class="letter-rq">
        <div class="letter-rq-left">
            <p>
                {{$letterData['documentNumber']}}
            </p>
        </div>

        <div class="letter-rq-right">
            @if ($letterData['companyName'] !== null)
            <p>
                {{$letterData['companyName']}}
            </p>
            @endif
            @if ($letterData['inn'] !== null)
            <p>
                {{$letterData['inn']}}
            </p>
            @endif
            @if ($letterData['positionCase'] !== null)
            <p>
                {{$letterData['positionCase']}}
            </p>
            @endif
            @if ($letterData['recipientCase'] !== null)
            <p>
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

            <div class="letter-title-wrapper">
                <h2>
                    {{$letterData['text']}}
                </h2>
            </div>
        </div>

    </div>

</div>