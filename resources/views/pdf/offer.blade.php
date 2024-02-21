<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 25px 25px;
        }

        header {
            width: 100%;
            /* position: fixed; */
            top: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
            display: table;

        }

        .row {
            width: 100%;
            display: table;
        }

        .rowItem {
            margin: 0px;
            padding: 0px;
            display: table-cell;
            width: 1%;
            /* white-space: nowrap; */
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

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        .page-break {
            page-break-after: always;
        }

        main {
            margin-top: 100px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        p,
        span {
            margin: 0px;

        }

        /* text */
        .text-small {
            font-size: 10px;
        }

        .text-normal {
            font-size: 11px;
        }

        .color {
            font-weight: bold;
            color: rgb(40, 104, 212);
        }


        /* letter */
        .letter-rq-left {
            width: 300px;
        }

        .letter-rq-right {
            width: 300px;
            text-align: right;
        }
    </style>
</head>

<body>
    <header>

        @if ($headerData['isTwoLogo'])
        <!-- Если isTwoLogo истинно, отображаем изображения -->
        <div class="rowItem">
            <img class="logo" src="{{ $headerData['logo_2'] }}" alt="Logo">
        </div>


        @else
        <!-- Если isTwoLogo ложно, отображаем текст -->
        <div class="rowItem">
            <div class="rq-wrapper">
                <p class="text-small">{{ $headerData['rq'] }}</p>
            </div>

        </div>

        @endif
        <div style="display: table-cell;"></div>
        <div class="rowItem">
            <img class="logo" src="{{ $headerData['logo_1'] }}" alt="Logo">
        </div>




    </header>
    <footer>
        Это футер документа.
    </footer>

    <main>
        <div class="letter">
            @component('pdf.components.letter', ['letterData' => $letterData])

            @endcomponent
        </div>
        <div class="page-break"></div>
        <div class="infoblocks">
            @component('pdf.components.infoblocks', $infoblocksData)

            @endcomponent
        </div>
        <div class="page-break"></div>
        <div class="prices"> </div>
        <div class="page-break"></div>
        <div class="invoice"> </div>
        <div class="page-break"></div>
    </main>
</body>

</html>