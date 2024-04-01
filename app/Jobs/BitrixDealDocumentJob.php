<?php

namespace App\Jobs;

use App\Services\BitrixDealDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BitrixDealDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $domain;
    protected $providerRq;
    protected $documentNumber;
    protected $data;
    protected $invoiceDate;
    protected $headerData;
    protected $doubleHeaderData;
    protected $footerData;
    protected $letterData;
    protected $infoblocksData;
    protected $bigDescriptionData;
    protected $pricesData;
    protected $stampsData;
    protected $isTwoLogo;
    protected $isGeneralInvoice;
    protected $isAlternativeInvoices;
    protected $dealId;
    protected $withStamps;


    public function __construct(
        $domain,
        $providerRq,
        $documentNumber,
        $data,
        $invoiceDate,
        $headerData,
        $doubleHeaderData,
        $footerData,
        $letterData,
        $infoblocksData,
        $bigDescriptionData,
        $pricesData,
        $stampsData,
        $isTwoLogo,
        $isGeneralInvoice,
        $isAlternativeInvoices,
        $dealId,
        $withStamps



    ) {
        $this->domain =  $domain;
        $this->providerRq =  $providerRq;
        $this->documentNumber = $documentNumber;
        $this->data = $data;
        $this->invoiceDate = $invoiceDate;
        
        $this->headerData =  $headerData;
        $this->doubleHeaderData = $doubleHeaderData;
        $this->footerData = $footerData;
        $this->letterData =  $letterData;
        $this->infoblocksData =  $infoblocksData;
        $this->bigDescriptionData =  $bigDescriptionData;
        $this->pricesData =  $pricesData;
        $this->stampsData =  $stampsData;
        $this->isTwoLogo =  $isTwoLogo;
        $this->isGeneralInvoice =  $isGeneralInvoice;
        $this->isAlternativeInvoices =  $isAlternativeInvoices;
        $this->dealId =  $dealId;
        $this->withStamps = $withStamps;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $documentService = new BitrixDealDocumentService(
            $this->domain,
            $this->providerRq,
            $this->documentNumber,
            $this->data,
            $this->invoiceDate,
            $this->headerData,
            $this->doubleHeaderData,
            $this->footerData,
            $this->letterData,
            $this->infoblocksData,
            $this->bigDescriptionData,
            
            $this->pricesData,
            $this->stampsData,
            $this->isTwoLogo,
            $this->isGeneralInvoice,
            $this->isAlternativeInvoices,
            $this->dealId,
            $this->withStamps
        );
        $documents = $documentService->getDocuments();
    }
}
