<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 25px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
        }

        p,
        span {
            margin: 0px;

        }

        table {
            vertical-align: top;
            margin: 0px;
            width: 100%;
            border-collapse: collapse;
            /* Убирает двойные бордеры между ячейками */
        }

        th,
        td {
            vertical-align: top;
            border: 1px solid black;
            padding: 3px;


        }

        .row {
            width: 100%;
            display: table;
        }

        .rowItem {
            margin: 0px;
            padding: 0px;
            display: table-row;
            width: 1%;
            /* white-space: nowrap; */
        }

        .cellItem {
            margin: 0px;
            padding: 0px;
            display: table-cell;
            width: 1%;
            /* white-space: nowrap; */
        }



        .page-content {
            margin-top: 90px;
            vertical-align: top;
        }

        .page-break {
            page-break-after: always;
        }





        /* text */
        .text-xsmall {
            font-size: 7px;
        }

        .text-small {
            font-size: 10px;
        }

        .text-normal {
            font-size: 11px;
        }

        .text-large {
            font-size: 12px;
        }

        .color {
            font-weight: bold;
            color: rgb(48, 115, 230);
            /* color: rgb(58, 102, 172);  garant */
        }

        .shadow {
            /* font-weight: bold; */
            color: rgba(38, 62, 104, 0.775);

        }

        .red {
            font-weight: bold;
            color: rgb(220, 26, 33);
        }

        .bold {
            font-weight: bold;

        }

        .italic {
            font-style: italic;
        }
    </style>
</head>

<body>
    @component('pdf.components.header', ['headerData' => $headerData])
    @endcomponent

    {{-- @if ($headerData['isTwoLogo'])
        @component('pdf.components.doubleHeader', ['doubleHeaderData' => $doubleHeaderData])
        @endcomponent
    @endif --}}

    <main>

        @component('pdf.components.invoice.invoice', $invoiceData)
        @endcomponent
        @if ($withStamps)
            <div class="stamp">
                @component('pdf.components.stamp', $stampsData)
                @endcomponent
            </div>
        @endif



    </main>
</body>

</html>
