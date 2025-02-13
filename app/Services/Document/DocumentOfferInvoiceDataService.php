<?php

namespace App\Services\Document;

use App\Services\Document\Infoblock\DocumentInfoblocksDataService;
use App\Services\Document\Invoice\DocumentInvoicePriceDataService;
use App\Services\Document\Offer\DocumentOfferDataService;
use App\Services\Document\Offer\DocumentOfferPriceDataService;

class DocumentOfferInvoiceDataService
{

    public static function getDocumentData(
        $infoblocksOptions,
        $complectName,
        $productsCount,
        $region,
        $salePhrase,
        $withStamps,
        $priceFirst,
        $regions,
        $contract,
        $documentType,
        $complect,



        $domain,
        $providerRq,
        $isTwoLogo,
        $manager,
        $documentNumber,
        $fields, //template fields
        $recipient,


        $price,
        $alternativeSetId


    ) {
        $infoblockService = new DocumentInfoblocksDataService(
            $infoblocksOptions,
            $complectName,
            $productsCount,
            $region,
            $salePhrase,
            $withStamps,
            $priceFirst,
            $regions,
            $contract,
            $documentType,
            $complect
        );

        $offerService = new DocumentOfferDataService(
            $domain,
            $providerRq,
            $isTwoLogo,
            $manager,
            $documentNumber,
            $fields, //template fields
            $recipient,


        );
        $offerPriceService = new DocumentOfferPriceDataService(
            $price,
            $salePhrase,
            true,
            false
        );
        $invoiceService = new DocumentInvoicePriceDataService(
            $price,
            $salePhrase,
            true
        );

        $data = [
            'infoblock' => $infoblockService->getInfoblocksData(),
            'offer' => $offerService->getOfferData(),
            'invoice' => [
                'price' => $invoiceService->getInvoicePricesData($price,  true, $alternativeSetId)
            ],
            'price' => $offerPriceService->getPricesData()
            // 'contract' => $contract,
            // 'supply' => $documentType,
        ];

        return $data;
    }
}
