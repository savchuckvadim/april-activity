<?php
declare(strict_types=1);
namespace App\Services\Document\General;

use App\Services\Document\DTO\OfferPrice\SortedProductDTO;

class ProccessPriceCellsService
{
    protected $allPrices;
    protected $isInvoice;



    public function __construct(
        $allPrices,
        $isInvoice

    ) {
        $this->allPrices = $allPrices;
        $this->isInvoice = $isInvoice;

    }
 
    /**
     * @return array<string, SortedProductDTO[]>
     */

    public function getSortActivePrices()
    {

        $result = [
            'general' => [],
            'alternative' => [],
            'total' => []
        ];
        foreach ($this->allPrices as $key => $target) {

            if ($target) {

                if (is_array($target) && !empty($target)) {
                    $result[$key] = $target;
                    foreach ($target as $index => $product) {

                        if ($product) {
                            $productContract = null;
                            foreach ($product['cells'] as $cell) {
                                if ($cell['code'] === 'contract') {
                                    if (!empty($cell['value'])) {
                                        if (!empty($cell['value']['contract'])) {
                                            if (!empty($cell['value']['contract']['productName'])) {
                                                if ($cell['value']['contract']['productName'] !== 'null') {
                                                    $productContract = $cell['value']['contract'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if (
                                is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells'])
                            ) {
                                if (!$this->isInvoice) {
                                    $filtredCells = array_filter($product['cells'], function ($prc) {
                                        return $prc['isActive'] == true;
                                    });
                                    usort($filtredCells, function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                } else {
                                    $filtredCells = [];
                                    usort($product['cells'], function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                    foreach ($product['cells'] as $cell) {
                                        $searchingCell = null;

                                        if ($cell['code'] === 'name') {
                                            $searchingCell = $cell;
                                            $searchingCell['contract'] =  $productContract;
                                        }

                                        if ($cell['code'] === 'current') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'quantity') {
                                            $cell['name'] = 'Кол-во';
                                            if (preg_match('/\d+/', (string)$cell['value'], $matches)) {
                                                $cell['value'] = $matches[0];
                                            }
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'measure') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'prepaymentsum') {
                                            $cell['name'] = 'Сумма';
                                            $searchingCell = $cell;
                                        }

                                        if ($searchingCell) {
                                            array_push($filtredCells, $searchingCell);
                                        }
                                    }
                                    // $filtredCells = array_filter($product['cells'], function ($prc) {
                                    //     return $prc['isActive'] == true || $prc['code'] == 'measure';
                                    // });
                                }
                                $result[$key][$index]['cells']  = $filtredCells;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

  
}
