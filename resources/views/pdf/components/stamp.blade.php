<style>
    .table-container {
        margin-top: 100px;
        width: 100%;
        text-align: left;

    }



    .signature-container {
        position: relative;
        text-align: center;
        /* Выравнивание содержимого по центру */
    }

    .signature-container img {
        position: absolute;
        left: 50%;
        top: 20px;
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
        height: 130px;
        vertical-align: middle;
        /* Выравнивание текста по вертикали в середине */
        font-weight: bold;
    }

    .signature-container img.signature {
        z-index: 2;
        /* Подпись будет над печатью */
        top: 50px;
        /* Смещение от верха контейнера */
    }

    .signature-container img.stamp {
        z-index: 1;
        /* Печать будет под подписью */
        top: 150px;
        /* Смещение от верха контейнера */
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
