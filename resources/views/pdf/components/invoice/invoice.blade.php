<style>

</style>

<div class="invoice page-content">
    @component('pdf.components.invoice.top', $rq)
    @endcomponent
    @component('pdf.components.invoice.main', $main)
    @endcomponent
    @component('pdf.components.invoice.price', $pricesData)
    @endcomponent


</div>
