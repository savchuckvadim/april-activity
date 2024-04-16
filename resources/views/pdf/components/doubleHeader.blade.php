<style>
    .header-double {
        margin-top: 80px;
        margin-bottom: 0px;
        width: 100%;
        /* height: 50px; */
        display: table;
    }

    .double-rq-left,
    .double-rq-right {
        display: table-cell;
    }

    .double-rq-left {
        margin: 0px;
        padding: 0px;
        width: 300px;
    }

    .double-rq-right {
        margin: 0px;
        padding: 0px;
        width: 400px;
        text-align: right;
    }
</style>

<div class="header-double">
    <div class="rowItem">
        <div class="double-rq-left">
            @php
      
            $headerRqText = str_replace("\\n", "\n", $doubleHeaderData['first']);
            @endphp
            <p class="text-small">
                {!! nl2br(e($headerRqText)) !!}
            </p>
        </div>
        <div style="display: table-cell;"></div>
        <div class="double-rq-right">
            <p class="text-small">
                {{ $doubleHeaderData['phone'] }}
            </p>
            <p class="text-small">
                {{ $doubleHeaderData['email'] }}
            </p>
        </div>
    </div>
</div>
