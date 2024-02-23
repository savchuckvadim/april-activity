<style>

</style>

<div class="invoice page-content">
    @component('pdf.components.invoice.top', $pricesData)
    @endcomponent

    @component('pdf.components.invoice.price', $pricesData)
    @endcomponent

    <div class="stamp">
        @component('pdf.components.stamp', $stampsData)
        @endcomponent
    </div>

</div>
