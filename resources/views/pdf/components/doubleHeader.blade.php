<header>
    <style>
        .header-double {
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

    <div class="rowItem">
        <div class="rq-wrapper">
            <p class="text-small">
                {{ $doubleHeaderData['first'] }}
            </p>
        </div>

    </div>

    <div style="display: table-cell;"></div>
    <div class="rowItem">
        <div class="rq-wrapper">
            <p class="text-small">
                {{ $doubleHeaderData['second'] }}
            </p>
        </div>
    </div>




</header>
