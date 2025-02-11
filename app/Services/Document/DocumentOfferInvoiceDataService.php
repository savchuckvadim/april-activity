<?php

namespace App\Services\Document;

use App\Services\Document\Infoblock\DocumentInfoblocksDataService;
use App\Services\Document\Invoice\DocumentInvoicePriceDataService;
use App\Services\Document\Offer\DocumentOfferDataService;

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
            // 'contract' => $contract,
            // 'supply' => $documentType,
        ];

        return $data;
    }
}
