<style>
    .outertable {
        border-collapse: collapse;
        width: 100%;
    }

    .innertable,
    .innertable td {
        border: none;
        padding: 0;
        margin: 0;
        vertical-align: top;
    }

    .cellBottom {
        vertical-align: bottom;
    }


    /* Добавление отступов для более аккуратного выравнивания */
    .padding-top {
        margin-top: 8px;
        padding-top: 11px;

    }
</style>

<div class="invoice-top">
    <table class="outertable" cellpadding="2" cellspacing="2">
        <tr>
            <td colspan="2" rowspan="2" style="height:49px; width: 105mm;">
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
            <td style="vertical-align: center; width: 25mm;">
                <p class="text-large">БИK</p>

            </td>
            <td rowspan="2" style="height:50px; vertical-align: top; width: 60mm;">
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
            <td style="width: 50mm;">
                <p class="text-large">ИНН {{ $inn }}</p>
            </td>
            <td style="width: 55mm;">
                <p class="text-large">КПП {{ $kpp }}</p>
            </td>
            <td rowspan="2" style="vertical-align: top; width: 25mm;">
                <p class="text-large">Сч. №</p>
            </td>
            <td rowspan="2" style="vertical-align: top; width: 60mm;">
                <p class="text-large">{{ $rs }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="innertable" style="height:50px; width: 105mm;">
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
    <div style="width: 65px;" class="qr-code">
        <!-- Замените 'src' на путь к вашему QR-коду -->
        <p class="text-large bold">qr</p>
    </div>
</div>
