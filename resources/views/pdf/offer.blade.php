<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 25px 25px;
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

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        .page-content {
            margin-top: 80px;
            vertical-align: top;
        }

        .page-break {
            page-break-after: always;
        }



        body {
            font-family: DejaVu Sans, sans-serif;
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

        .bold {
            font-weight: bold;

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
    @component('pdf.components.header', ['headerData' => $headerData])
    @endcomponent
    <footer>
        Это футер документа.
    </footer>

    <main>
        <div class="letter page-content">
            @component('pdf.components.letter', ['letterData' => $letterData])
            @endcomponent
        </div>
        <div class="page-break"></div>
        <div class="infoblocks">

            @component('pdf.components.infoblocks', $infoblocksData)
            @endcomponent
        </div>
        <div class="page-break"></div>
        <div class="prices">
            @component('pdf.components.prices', $infoblocksData)
            @endcomponent
        </div>

    </main>
</body>

</html>
