<style>
    .table-container {
        margin-top: 30px;
        width: 100%;
        text-align: left;
    }


    .signature-container {
        position: relative;
        text-align: center;
    }


    .signature-container img {
        position: absolute;
        left: 50%;
        top: 20px;
        transform: translateX(-50%);

    }

    /* Если нужно, чтобы печать была больше по размеру */
    .signature-container img.stamp {
        height: 120px;

    }

    .text-cell {
        width: 250px;
        height: 130px;
        vertical-align: middle;
        /* Выравнивание текста по вертикали в середине */
        font-weight: bold;
    }

    .signature-container img.signature {
        z-index: 2;
        /* Подпись будет над печатью */
        top: 15px;
        /* Смещение от верха контейнера */
    }

    .signature-container img.stamp {
        /* width: 150px; */
        height: auto;
        z-index: 1;
        /* Печать будет под подписью */
        top: 5px;
        /* Смещение от верха контейнера */
    }

    .table-container,
    .table-container tr,
    .table-container td,
    .table-container th {
        border: none;
    }
</style>

<table class="table-container">
    <tr>

        <td class="text-cell text-normal bold">{{ $position }}</td>


        <td class="signature-container">
            @if ($signature)
                <img src={{ $signature }} class="signature" alt="Подпись" height="100">
            @endif
            @if ($stamp)
                <img src={{ $stamp }} class="stamp" alt="Печать" height="100">
            @endif


        </td>

        <td class="text-cell text-normal bold">{{ $director }}</td>
    </tr>
</table>
