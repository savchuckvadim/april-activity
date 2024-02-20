<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 25px 25px;
        }

        header {
            position: fixed;
            /* top: -60px; */
            left: 0px;
            right: 0px;
            height: 50px;
            display: table;

        }

        .rowItem{
            display: table-cell; vertical-align: middle
        }

        .logo {
            width: 200px;
            height: auto;
        }

        footer {
            position: fixed;
            /* bottom: -60px; */
            left: 0px;
            right: 0px;
            height: 50px;
        }

        .page-break {
            page-break-after: always;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
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
            <p>Текст вместо логотипа</p>
        </div>

        @endif
        <div class="rowItem">
            <img class="logo" src="{{ $headerData['logo_1'] }}" alt="Logo">
        </div>




    </header>
    <footer>
        Это футер документа.
    </footer>

    <main>
        <div class="letter"> </div>
        <div class="page-break"></div>
        <div class="infoblocks"> </div>
        <div class="page-break"></div>
        <div class="prices"> </div>
        <div class="page-break"></div>
        <div class="invoice"> </div>
        <div class="page-break"></div>
    </main>
</body>

</html>