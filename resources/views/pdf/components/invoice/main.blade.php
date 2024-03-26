<style>
    .invoice-main {
        margin-top: 55px;
        margin-bottom: 30px;
    }

    .invoice-title {
        text-align: center;
    }

    .invoice-main-table,
    .invoice-main-row,
    .invoice-main-cell {
        border-collapse: collapse;
        width: 100%;
        border: 0px;
    }

    .invoice-main-table {
        margin-top: 30px;
    }
</style>


{{-- 'main' => [
    'rq' => $providerRq,
    'recipient' => $recipient,
    'number' => $invoiceNumber,
], --}}


<div class="invoice-main">

    <h3 class="invoice-title">{{ $number }}</h3>

    <table class="invoice-main-table">
        @if ($invoiceDate)
            <tr class="invoice-main-row">
                <td class="invoice-main-cell">
                    <p class="text-normal"><span class="bold">Срок оплаты: </span>{{ $invoiceDate }}</p>

                </td>
            </tr>
        @endif
        <tr class="invoice-main-row">
            <td class="invoice-main-cell">
                <p class="text-normal"><span class="bold">Поставщик:</span> {{ $rq['fullname'] }}, ИНН:
                    {{ $rq['inn'] }}, {{ $rq['registredAdress'] }},
                    {{ $rq['phone'] }}</p>
            </td>
        </tr>
        @if ($recipient['companyName'])
            @php
                $inn = null;
                if (isset($recipient['inn'])) {
                    if ($recipient['inn']) {
                        $inn = ', ИНН: ' . $recipient['inn'];
                    }
                }

            @endphp
            <tr class="invoice-main-row">
                <td class="invoice-main-cell">
                    <p class="text-normal"><span class="bold">Покупатель:</span>
                        {{ $recipient['companyName'] }}{{ $inn }} </p>
                </td>
            </tr>
        @endif

    </table>
</div>
