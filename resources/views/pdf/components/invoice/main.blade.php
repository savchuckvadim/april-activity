<style>
    .invoice-main {
        margin-top: 35px;
    }

    .invoice-title {
        text-align: center;
    }
</style>


{{-- 'main' => [
    'rq' => $providerRq,
    'recipient' => $recipient,
    'number' => $invoiceNumber,
], --}}


<div class="invoice-main">

    <h3 class="invoice-title">{{ $number }}</h3>

    <table>
        <tr>
            <td>
                <p>Поставщик: {{ $rq['fullname'] }}, ИНН: {{ $rq['inn'] }}, {{ $rq['registredAdress'] }},
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
                    <p>Покупатель: {{ $recipient['companyName'] }}{{ $inn }} </p>
                </td>
            </tr>
        @endif

    </table>
</div>
