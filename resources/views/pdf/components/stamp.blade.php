<style>
    .table-container {
        margin-top: 100px;
        width: 100%;
        text-align: center;
    }

    .signature-container img {
        display: block;
        margin: 0 auto;
        /* Выравнивание изображений по центру */
    }

    .text-cell,
    .signature-container {
        vertical-align: middle;
        /* Выравнивание содержимого ячеек по вертикали */
        padding: 15px;
        /* Отступы вокруг содержимого */
    }
</style>

<table class="table-container">
    <tr>

        <td class="text-cell text-normal bold">{{ $position }}</td>


        <td class="signature-container">
            <img src={{ $signature }} alt="Подпись" height="100">
            <img src={{ $stamp }} alt="Печать" height="100">
        </td>

        <td class="text-cell text-normal bold">{{ $director }}</td>
    </tr>
</table>
