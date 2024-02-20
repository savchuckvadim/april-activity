<div class="letter">
    <div class="letter-rq">
        <div class="letter-rq-left">
            <p>
                {{$letterData['documentNumber']}}
            </p>
        </div>

        <div class="letter-rq-right">
            @if ($headerData['companyName'] !== null)
            <p>
                {{$letterData['companyName']}}
            </p>
            @endif
            @if ($headerData['inn'] !== null)
            <p>
                {{$letterData['inn']}}
            </p>
            @endif
            @if ($headerData['positionCase'] !== null)
            <p>
                {{$letterData['positionCase']}}
            </p>
            @endif
            @if ($headerData['recipientCase'] !== null)
            <p>
                {{$letterData['recipientCase']}}
            </p>

            @endif
        </div>

        <div class="letter-text">
            @if ($headerData['recipientCase'] !== null)
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