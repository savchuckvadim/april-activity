<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .header {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 0px;
            text-align: center;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>

    <!-- <div class="header">
    <h1>{{ $title }}</h1>
</div> -->
    <div>
        <h2>
            {{$custom}}
        </h2>
    </div>
    <p>Date: {{ $date }}</p>

    <table>
        <thead>
            <tr>
                <th>Header 1</th>
                <th>Header 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
            <tr>
                <td>Data 3</td>
                <td>Data 4</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a custom footer.</p>
    </div>

</body>

</html>