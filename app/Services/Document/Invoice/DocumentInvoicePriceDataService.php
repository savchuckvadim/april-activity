<?php

namespace App\Services\Document\Invoice;

use App\Services\Document\General\ProccessPriceCellsService;
use morphos\Russian\MoneySpeller;
use morphos\Russian\TimeSpeller;

class DocumentInvoicePriceDataService
{
    protected $price;
    protected $salePhrase;
    // protected $withPrice;
    protected $isInvoice;
    protected $priceCellsService;

    public function __construct(
        $price,
        $salePhrase,
        // $withPrice = false,
        $isInvoice = null
    ) {
        $this->price = $price;
        $this->salePhrase = $salePhrase;
        // $this->withPrice = $withPrice;
        $this->isInvoice = $isInvoice;
        $this->priceCellsService = new ProccessPriceCellsService($price['cells'], true);

    }
    public function getInvoicePricesData($price, $isGeneral = true, $alternativeSetId)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->priceCellsService->getSortActivePrices();
        $allPrices =  $sortActivePrices['general'];



        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;


        foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }

        $foundCell = null;

        foreach ($price['cells']['total'][0]['cells'] as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }
        }


        $targetProducts = $sortActivePrices['general'];
        $totalCells = $price['cells']['total'][0]['cells'];


        if (!$isGeneral) {
            foreach ($price['cells']['alternative'][0]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }
            $targetProducts = $sortActivePrices['alternative'];
            $totalCells = $price['cells']['alternative'][$alternativeSetId]['cells'];
            $allPrices = [$sortActivePrices['alternative'][$alternativeSetId]];


            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }

            $foundCell = null;

            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $cell) {
                if ($cell['code'] === 'prepaymentsum') {
                    $foundCell = $cell;
                }
            }
        }


        $allProductForInvoice = [];
        foreach ($targetProducts as $productKey => $product) {
            if ($isGeneral) {
                array_push($allProductForInvoice, $product);
            } else {
                if ($productKey == $alternativeSetId) {
                    array_push($allProductForInvoice, $product);
                }
            }
        }




        foreach ($totalCells  as $cell) {

            if ($cell['code'] === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {

                    $qcount = $contract['prepayment'] * $cell['value'];

                    $quantityString =  TimeSpeller::spellUnit($qcount, TimeSpeller::MONTH);
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
            // 'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            // 'withPrice' => $withPrice,
            'withTotal' => true,
            'total' => $fullTotalstring,
            'contract' => $contract

        ];
    }

   

}
