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
            margin-top: 90px;
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
            color: black;
            margin: 0px;

        }



        /* text */
        .text-xsmall {
            font-size: 9px;
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

        .blue {
            font-weight: bold;
            color: rgb(48, 115, 230);
            /* color: rgb(58, 102, 172);  garant */


        }

        .bold {
            font-weight: bold;

        }

        .italic {
            font-style: italic;
        }
    </style>
</head>

<body {{-- style="background-image: url('{{ asset('imgs/background.jpg') }}');" --}}>

    @component('pdf.components.header', ['headerData' => $headerData])
    @endcomponent
    @if ($withManager)
        @component('pdf.components.footer', $footerData)
        @endcomponent
    @endif

    @if ($headerData['isTwoLogo'])
        @component('pdf.components.doubleHeader', ['doubleHeaderData' => $doubleHeaderData])
        @endcomponent
    @endif

    <main>
        <div class="{{ !$headerData['isTwoLogo'] ? 'page-content' : 'letter' }}">

            {{-- @if (!empty($letterData['isLargeLetterText']))
                @component('pdf.components.letter-bigtext', ['letterData' => $letterData])
                @endcomponent
            @endif
            @if (empty($letterData['isLargeLetterText'])) --}}
                @component('pdf.components.letter', ['letterData' => $letterData])
                @endcomponent
            {{-- @endif --}}

            @if ($withStamps && !$isPriceFirst)
                <div class="stamp">
                    @component('pdf.components.stamp', $stampsData)
                    @endcomponent
                </div>
            @endif
            @if ($isPriceFirst)
                @if (!$infoblocksData['withPrice'])
                    <div class="page-break"></div>
                @endif
                <div class="prices">
                    @component('pdf.components.price', $pricesData)
                    @endcomponent

                </div>
            @endif
        </div>
        <div class="page-break"></div>
        <div class="infoblocks">

            @component('pdf.components.infoblocks', $infoblocksData)
            @endcomponent
        </div>

        @if (!$isPriceFirst)
            @if (!$infoblocksData['withPrice'])
                <div class="page-break"></div>
            @endif
            <div class="prices">
                @component('pdf.components.price', $pricesData)
                @endcomponent
                @if ($withStamps)
                    <div class="stamp">
                        @component('pdf.components.stamp', $stampsData)
                        @endcomponent
                    </div>
                @endif

            </div>
        @endif

        {{-- @if ($infoblocksData['descriptionMode'] === 3)
            <div class="page-break"></div>
            <div class="infoblocks">

                @component('pdf.components.description', $bigDescriptionData)
                @endcomponent
            </div>
        @endif --}}
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
