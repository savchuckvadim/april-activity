<style>
    header {
        width: 100%;
        position: fixed;
        top: 0px;
        left: 0px;
        right: 0px;
        height: 50px;
        display: table;
    }

    .rq-wrapper {
        margin: 0px;
        padding: 0px;
        width: 340px;
    }

    .logo {
        width: 150px;
        height: auto;
    }
</style>
<header>
    <div class="rowItem">
        @if ($headerData['isTwoLogo'])
            <!-- Если isTwoLogo истинно, отображаем изображения -->
            <div class="cellItem">
                <img class="logo" src="{{ $headerData['logo_2'] }}" alt="Logo">
            </div>
        @else
            <!-- Если isTwoLogo ложно, отображаем текст -->
            <div class="cellItem">
                <div class="rq-wrapper">
                    <p class="text-small">{{ $headerData['rq'] }}</p>
                </div>

            </div>
        @endif
        <div style="display: table-cell;"></div>
        <div class="cellItem">
            <img class="logo" src="{{ $headerData['logo_1'] }}" alt="Logo">
        </div>
    </div>

</header>
