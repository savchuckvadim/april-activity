<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 100px 25px;
        }

        header {
            position: fixed;
            top: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        footer {
            position: fixed;
            bottom: -60px;
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
        <img src="{{ $headerData['logo_2'] }}" alt="Logo">

        @else
        <!-- Если isTwoLogo ложно, отображаем текст -->
        <p>Текст вместо логотипа</p>
        @endif

        <img src="{{ $headerData['logo_1'] }}" alt="Logo">
    </header>
    <footer>
        Это футер документа.
    </footer>

    <main>
        <div></div>
        Основной контент документа.
        <div class="page-break"></div>
        Следующая страница документа.
    </main>
</body>

</html>