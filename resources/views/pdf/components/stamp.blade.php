<style>
    .table-container {
        margin-top: 100px;
        width: 100%;
        text-align: center;
    }

    .signature-container {
        text-align: center;
        /* Выравнивание содержимого по центру */
    }

    .signature-container img {
        display: block;
        /* Изображения отображаются как блочные элементы */
        margin: 0 auto;
        /* Отступы автоматические, для выравнивания по центру */
        padding-bottom: 10px;
        /* Небольшой отступ снизу для подписи */
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
            <img src={{ $signature }} alt="Подпись" height="100">
            <img src={{ $stamp }} alt="Печать" height="100">
        </td>

        <td class="text-cell text-normal bold">{{ $director }}</td>
    </tr>
</table>
