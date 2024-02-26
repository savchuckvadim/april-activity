<style>
    .qr-table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid;
    }

    .cell-inn,
    .cell-kpp,
    .cell-large,
    .outertable,
    .cell-small,
    .cell-medium,
    .outertable td {
        border: 1px solid;
        border-collapse: collapse;
        /* width: 500px; */
    }

    .qr-table {
        /* padding-left: 20px; */
        /* Отступ от основной таблицы */
    }

    .innertable,
    .innercell,
    .innertable td {
        border: none;
        padding: 0;
        margin: 0;
        vertical-align: top;
    }


    .cell-large {
        /* width: 105mm; */
        /* width: 80px; */
    }

    .cell-small {
        width: 25mm;
        width: 9px;
    }

    .cell-medium {
        width: 60mm;
        /* width: 10px; */
    }

    /* .cell-xsmall {
        width: 20mm;
    } */

    /* Предположим, что это для QR-кода */
    .cell-inn {
        /* width: 50mm; */
        width: 45px;
    }

    .cell-kpp {
        /* width: 55mm; */
        width: 45px;
    }


    .cellBottom {
        min-height: 35px;
        vertical-align: bottom;
    }



    .padding-top {

        margin-top: 8px;
        padding-top: 11px;

    }

    .qr-img {
        width: 130px;
        max-height: 250px;
        height: auto;
        margin-left: 4px;
    }
</style>



<table cellpadding="0" cellspacing="0">
    <tr>
        <td class="innercell">
            <div class="invoice-top">


                <table class="outertable" cellpadding="2" cellspacing="2">
                    <tr>
                        <td class="cell-large" colspan="2" rowspan="2" style="height:49px;">
                            <table width="100%" class="innertable">
                                <tr>
                                    <td valign="top">
                                        <p class="text-normal"><span
                                                class="bold">{{ $bank }}</span><br>{{ $bankAdress }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height: 25px;" class="cellBottom">
                                        <p style="margin-top:12px;" class="text-small">Банк получателя</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="cell-small" style="vertical-align: center;">
                            <p class="text-large">БИK</p>

                        </td>
                        <td class="cell-medium" rowspan="2" style="height:50px; vertical-align: top;">
                            <table class="innertable">
                                <tr>
                                    <td>
                                        <p class="text-large">{{ $bik }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cellBottom ">
                                        <p class="text-large padding-top">{{ $ks }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="text-large">Сч. №</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-inn">
                            <p class="text-large">ИНН {{ $inn }}</p>
                        </td>
                        <td class="cell-kpp">
                            <p class="text-large">КПП {{ $kpp }}</p>
                        </td>
                        <td class="cell-small" rowspan="2" style="vertical-align: top;">
                            <p class="text-large">Сч. №</p>
                        </td>
                        <td class="cell-medium" rowspan="2" style="vertical-align: top;">
                            <p class="text-large">{{ $rs }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="cell-large" class="innertable" style="height:50px;">
                                <tr>
                                    <td valign="top">
                                        <p class="text-large">{{ $fullname }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cellBottom" style="height: 25px;">
                                        <p style="margin-top:10px;" class="text-small">Получатель</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>




            </div>
        </td>
        <td class="innercell">
            <!-- Замените 'src' на путь к вашему QR-коду -->
            <img class="qr-img" src="{{ asset('imgs/background.jpg') }}" alt="QR Code">
            {{-- <p class="text-large bold">qr</p> --}}
        </td>
    </tr>
</table>
