<style>
    .header-double {
        margin-top: 60px;
        margin-bottom: 0px;
        width: 100%;
        /* height: 50px; */
        display: table;
    }

    .rq-wrapper {
        margin: 0px;
        padding: 0px;
        width: 300px;
    }

    .double-rq-right {
        
        text-align: end
    }
</style>

<div class="header-double">
    <div class="rowItem">
        <div class="rq-wrapper">
            <p class="text-small">
                {{ $doubleHeaderData['first'] }}
            </p>
        </div>

    </div>

    <div style="display: table-cell;"></div>
    <div class="rowItem">
        <div class="rq-wrapper double-rq-right">
            <p class="text-small">
                {{ $doubleHeaderData['second'] }}
            </p>
        </div>
    </div>
</div>
