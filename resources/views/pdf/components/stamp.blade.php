<style>
    .table-container {
        margin-top: 100px;
        width: 100%;
        text-align: left;

    }
    .table-container tr{
       height:200px

    }

    .signature-container {
        position: relative;
        text-align: center;
        /* Выравнивание содержимого по центру */
    }

    .signature-container img {
        position: absolute;
        left: 50%;
        /* Центрируем изображение по горизонтали */
        transform: translateX(-50%);
    }

    /* Если нужно, чтобы печать была больше по размеру */
    .signature-container img.stamp {
        height: 120px;
        /* Пример увеличения размера печати */
    }

    .text-cell {
        width: 300px;

        vertical-align: middle;
        /* Выравнивание текста по вертикали в середине */
        font-weight: bold;
    }

    
</style>

<table class="table-container">
    <tr>

        <td class="text-cell text-normal bold">{{ $position }}</td>


        <td class="signature-container">
            <img src={{ $signature }} class="signature" alt="Подпись" height="100">
            <img src={{ $stamp }} class="stamp" alt="Печать" height="100">
        </td>

        <td class="text-cell text-normal bold">{{ $director }}</td>
    </tr>
</table>
