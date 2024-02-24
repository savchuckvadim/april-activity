<style>
    .outertable {
        padding: 2px;
        border-collapse: collapse;
        border: 1mm;
    }

    .innertable {
        margin: 1px;
        padding: 0;
        vertical-align: middle;
        border-collapse: collapse;
        border: 0px;
    }

    .cellBottom {
        vertical-align: bottom;
    }
</style>

<div class="invoice-top">
    <table width="100%" class="outertable" style="border-collapse: collapse; width: 100%;" cellpadding="2" cellspacing="2">
        <tr style="">
            <td colspan="2" rowspan="2" style="min-height:45mm; width: 105mm;">
                <table width="100%" class="innertable" cellpadding="0" cellspacing="0">
                    <tr class="innertable">
                        <td class="innertable" valign="top">
                            <p class="text-normal bold">
                                {{ $bank }}
                                <br>{{ $bankAdress }}
                            </p>
                        </td>
                    </tr>
                    <tr class="innertable" style="min-height:23mm;">
                        <td class="innertable cellBottom" valign="bottom" style="height: 10mm;">
                            <p class="text-normal">Банк получателя</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: center; height: 5mm; width: 25mm;">
                <table class="innertable">
                    <tr class="innertable">
                        <td class="innertable">
                            <p class="text-large">БИK</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td rowspan="2" style="vertical-align: top;  height: 30px; width: 60mm;">
                <table class="innertable">
                    <tr class="innertable">
                        <td class="innertable">
                            <p class="text-large">
                                {{ $bik }}</p>
                        </td>
                    </tr>
                    <tr class="innertable">
                        <td class="innertable cellBottom" style="height: 30px;">
                            <p class="text-large" style="margin-top: 25px; line-height: 15mm; vertical-align: middle;">
                                {{ $ks }}
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        {{-- <tr>
            <td style="width: 25mm;">
                <table class="innertable">
                    <tr class="innertable">
                        <td class="innertable">
                            <p class="text-large">Сч. №</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr> --}}
        <tr>
            <td style="min-height:6mm; height:auto; width: 50mm;">
                <p class="text-large">ИНН {{ $inn }}</p>
            </td>
            <td style="min-height:6mm; height:auto; width: 55mm;">
                <p class="text-large">КПП {{ $kpp }} </p>
            </td>
            <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 25mm;">
                <table class="innertable">
                    <tr class="innertable">
                        <td class="innertable">
                            <p class="text-large">Сч. №</p>
                        </td>
                    </tr>
                </table>

            </td>
            <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 60mm;">
                <table class="innertable">
                    <tr class="innertable">
                        <td class="innertable">
                            <p class="text-large">{{ $rs }}</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td colspan="2" style="min-height:13mm; height:auto;">

                <table class="innertable" cellpadding="0" cellspacing="0" style="height: 23mm; width: 105mm;">
                    <tr class="innertable">
                        <td class="innertable" valign="top">
                            <p class="text-large">{{ $fullname }}</p>
                        </td>
                    </tr>
                    <tr class="innertable">
                        <td class="innertable" valign="bottom" style="height: 3mm;">
                            <p class="text-large">Получатель</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</div>
