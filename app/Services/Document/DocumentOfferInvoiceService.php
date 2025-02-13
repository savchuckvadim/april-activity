<?php

namespace App\Services\Document;

use App\Services\Document\DTO\OfferPrice\OfferPriceDTO;

class DocumentOfferInvoiceService
{

    public static function getDocument(
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

        $data = DocumentOfferInvoiceDataService::getDocumentData(
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
        );
        $generateDocumentService = new DocumentOfferInvoiceGenerateService('domain', $data);
        $result = $generateDocumentService->getGenerateDocumentFromTemplate();

        return $result;
    }
}
