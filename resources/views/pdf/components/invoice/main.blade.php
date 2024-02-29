<style>
    .invoice-main {
        margin-top: 35px;
    }

    .invoice-title {
        text-align: center;
    }

    .invoice-main-table {
        border-collapse: collapse;
        width: 100%;
        border: none;
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
        <tr>
            <td>
                <p class="text-small"><span class="bold">Поставщик:</span> {{ $rq['fullname'] }}, ИНН:
                    {{ $rq['inn'] }}, {{ $rq['registredAdress'] }},
                    {{ $rq['phone'] }}</p>
            </td>
        </tr>
        @if ($recipient['companyName'])
            @php
                $inn = null;
                if ($recipient['inn']) {
                    $inn = ', ИНН: ' . $recipient['inn'];
                }

            @endphp
            <tr>
                <td>
                    <p class="text-small"><span class="bold">>Покупатель:</span>
                        {{ $recipient['companyName'] }}{{ $inn }} </p>
                </td>
            </tr>
        @endif

    </table>
</div>
