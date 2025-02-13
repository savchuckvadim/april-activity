<?php

namespace App\Services\Document\Offer;

use App\Services\Document\DTO\OfferPrice\OfferPriceDTO;
use App\Services\Document\General\ProccessPriceCellsService;
use morphos\Russian\MoneySpeller;
use morphos\Russian\TimeSpeller;

class DocumentOfferPriceDataService
{
    protected  $price;
    protected $salePhrase;
    protected $withPrice;
    protected $isInvoice;

    protected $priceCellsService;

    public function __construct(
        $price,
        $salePhrase,
        $withPrice = false,
        $isInvoice = null
    ) {
        $this->price = $price;
        $this->salePhrase = $salePhrase;
        $this->withPrice = $withPrice;
        $this->isInvoice = $isInvoice;
        $this->priceCellsService = new ProccessPriceCellsService($price['cells'], false);
    }
    public function getPricesData()
    {
        $price = $this->price;
        $salePhrase = $this->salePhrase;
        $withPrice = $this->withPrice;
        $isInvoice = $this->isInvoice;

        $isTable = $price['isTable'];

        
        /** @var OfferPriceDTO $comePrices */
        $comePrices =  OfferPriceDTO::fromArray($price['cells']);
       
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS

        $sortActivePrices = $this->priceCellsService->getSortActivePrices();
        $allPrices =  $sortActivePrices;


        //IS WITH TOTAL 
        $withTotal = $this->getWithTotal($comePrices);

        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;

        /** @var GroupCellsDTO $totalGroup */
        $totalGroup = $comePrices->total[0];

        foreach ($totalGroup->cells as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }
        // if ($withTotal) {
        $foundCell = null;
        foreach ($comePrices->total[0]->cells as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }

            if ($cell->code === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima' && $contract['shortName'] !== 'lic' && $contract['shortName'] !== 'key') {

                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    // $licQuantity = $contract['prepayment'] * $quantity;
                    $licQuantity = $contract['prepayment'];

                    $quantityString =  TimeSpeller::spellUnit($licQuantity, TimeSpeller::MONTH);
                } else  if ($contract['shortName'] === 'lic' || $contract['shortName'] === 'key') {

                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $licQuantity = $contract['prepayment'] * $quantity;
                    $quantityString =  TimeSpeller::spellUnit($licQuantity, TimeSpeller::MONTH);
                } else {
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                }
            }

            if ($cell['code'] === 'measure' && $cell['value']) {
                if ($cell['isActive']) {
                    // foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
                    //     if ($contractCell['code'] === 'contract') {
                    //         $measureString = $contractCell['value']['measureFullName'];
                    //     }
                    // }

                }
            }
        }

        $quantityMeasureString = '\n За ' . '<color>' . $quantityString . '</color>';


        if ($foundCell) {
            $totalSum = $foundCell['value'];
            $totalSum = MoneySpeller::spell($totalSum, MoneySpeller::RUBLE, MoneySpeller::SHORT_FORMAT);
            $total = '<color>' . $totalSum . '</color> ';
        }

        $result = MoneySpeller::spell($foundCell['value'], MoneySpeller::RUBLE);
        $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
        $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






        $text = ' (' . $firstChar . $restOfText . ') без НДС';
        $textTotalSum = $text;

        $fullTotalstring = $total . ' ' . $textTotalSum . $quantityMeasureString;

        return [
            'isTable' => $isTable,
            'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            'withPrice' => $withPrice,
            'withTotal' => $withTotal,
            'total' => $fullTotalstring,
            'salePhrase' => $salePhrase

        ];
    }



    protected function getWithTotal(
       OfferPriceDTO $allPrices

    ) {

        $result = false;
        $alternative =  $allPrices->alternative;
        $general =  $allPrices->general;



        if (is_array($alternative) && is_array($general)) {

            if (empty($alternative)) { // если нет товаров для сравнения


                if (count($general) > 1) {  //если больше одного основного товара
                    $result = true;
                }

                foreach ($general[0]['cells'] as $key => $cell) {
                    if ($cell['code'] === 'quantity') {            //если есть количество
                        if ($cell['value'] > 1) {
                            $result = true;
                        }
                    }

                    if ($cell['code'] === 'contract') {              //если какой-нибудь навороченный контракт
                        if (isset($cell['value']) && isset($cell['value']['shortName'])) {
                            if ($cell['value']['shortName'] !== 'internet' && $cell['value']['shortName'] !== 'proxima') {
                                $result = true;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
