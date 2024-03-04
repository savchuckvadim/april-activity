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

<body
    {{-- style="background-image: url('{{ asset('imgs/background.jpg') }}');" --}}
 >
    @component('pdf.components.header', ['headerData' => $headerData])
    @endcomponent
    @component('pdf.components.footer', $footerData)
    @endcomponent
    @if ($headerData['isTwoLogo'])
        @component('pdf.components.doubleHeader', ['doubleHeaderData' => $doubleHeaderData])
        @endcomponent
    @endif

    <main>
        <div class="{{ !$headerData['isTwoLogo'] ? 'page-content' : 'letter' }}">

            @component('pdf.components.letter', ['letterData' => $letterData])
            @endcomponent
            <div class="stamp">
                @component('pdf.components.stamp', $stampsData)
                @endcomponent
            </div>
        </div>
        <div class="page-break"></div>
        <div class="infoblocks">

            @component('pdf.components.infoblocks', $infoblocksData)
            @endcomponent
        </div>
        @if (!$infoblocksData['withPrice'])
            <div class="page-break"></div>
        @endif

        <div class="prices">
            @component('pdf.components.price', $pricesData)
            @endcomponent
            <div class="stamp">
                @component('pdf.components.stamp', $stampsData)
                @endcomponent
            </div>
        </div>


        {{-- <div class="page-break"></div>

        @component('pdf.components.invoice.invoice', $invoiceData)
        @endcomponent
        <div class="stamp">
            @component('pdf.components.stamp', $stampsData)
            @endcomponent
        </div> --}}


    </main>
</body>

</html>
