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
        margin-top: 3px;
        padding-top: 10px;
        
    }
</style>

<div class="invoice-top">
    <table class="outertable" cellpadding="2" cellspacing="2">
        <tr>
            <td colspan="2" rowspan="2" style="min-height:45mm; width: 105mm;">
                <table  width="100%" class="innertable">
                    <tr>
                        <td valign="top">
                            <p class="text-normal">{{ $bank }}<br>{{ $bankAdress }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="cellBottom">
                            <p class="text-normal cellBottom">Банк получателя</p>
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
                    <tr >
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
                <table class="innertable" style="width: 105mm;">
                    <tr>
                        <td valign="top">
                            <p class="text-large">{{ $fullname }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="cellBottom" style="height: 3mm;">
                            <p class="text-large">Получатель</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
