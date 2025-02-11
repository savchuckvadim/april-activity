<?php

namespace App\Services\Document;

class DocumentOfferInvoiceService
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

        $data = DocumentOfferInvoiceDataService::getDocumentData(  $infoblocksOptions,
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
        $alternativeSetId);

        return $data;
    }
}
