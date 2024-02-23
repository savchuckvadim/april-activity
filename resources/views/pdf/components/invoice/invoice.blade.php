<style>

</style>

<div class="invoice page-content">
    @component('pdf.components.invoice.top', $invoiceData['rq'])
    @endcomponent
    @component('pdf.components.invoice.main', $invoiceData)
    @endcomponent
    @component('pdf.components.invoice.price', $invoiceData['pricesData'])
    @endcomponent


</div>
