<?php

namespace App\Services\Document\Infoblock;

use App\Models\Garant\Infoblock;

class DocumentInfoblocksDataService
{

    protected $infoblocksOptions;
    protected $complectName;
    protected $productsCount;
    protected $region;
    protected $salePhrase;
    protected $withStamps;
    protected $priceFirst;
    protected $regions;
    protected $contract;
    protected $documentType;
    protected $complect;

    public function __construct(
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
    ) {
        $this->infoblocksOptions = $infoblocksOptions;
        $this->complectName = $complectName;
        $this->productsCount = $productsCount;
        $this->region = $region;
        $this->salePhrase = $salePhrase;
        $this->withStamps = $withStamps;
        $this->priceFirst = $priceFirst;
        $this->regions = $regions;
        $this->contract = $contract;
        $this->documentType = $documentType;
        $this->complect = $complect;
    }

    public function getInfoblocksData()
    {
        $infoblocksOptions = $this->infoblocksOptions;
        $complect = $this->complect;
        $complectName = $this->complectName;
        $productsCount = $this->productsCount;
        $region = $this->region;
        $salePhrase = $this->salePhrase;
        $withStamps = $this->withStamps;
        $priceFirst = $this->priceFirst;
        $regions = $this->regions;

        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];
        $itemsPerPage = $this->determineItemsPerPage($descriptionMode, $styleMode);

        $withPrice = false;
        $pages = [];
        $currentPage = [
            'groups' => [],
            'items' => []

        ];
        $currentPageItemsCount = 0;
        $erSubstring = "Пакет Энциклопедий решений";
        $allRegions = [];
        $allRegionsCount = 0;
        if (!empty($regions)) {

            foreach ($regions as $weightType) {
                foreach ($weightType as $rgn) {
                    array_push($allRegions, $rgn);
                }
            }
            $allRegionsCount = count($allRegions);
        }

        // Проверка наличия подстроки в строке без учета регистра

        foreach ($complect as $group) {

            if (stripos($group['groupsName'], $erSubstring) === false) {
                $groupItems = [];
                foreach ($group['value'] as $infoblock) {
                    if (!array_key_exists('code', $infoblock)) {
                        continue;
                    }

                    $infoblockData = Infoblock::where('code', $infoblock['code'])->first();
                    if ($infoblock['code'] == 'reg') {
                        $infoblockData['name'] = $region['infoblock'];

                        // Извлечение названия региона из заголовка
                        $regionName = trim(str_replace("Законодательство", "", $region['infoblock']));

                        // Замена в тексте
                        $infoblockData['descriptionForSale'] = preg_replace("/органов власти регионов/u", "органов $regionName", $infoblockData['descriptionForSale']);
                        $infoblockData['shortDescription'] = preg_replace("/местного законодательства/u", "$regionName", $infoblockData['shortDescription']);

                        if ($allRegionsCount > 1) {
                            $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . '\\n А также законодательство регионов: \\n';
                            $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . '\\n А также законодательство регионов: \\n';
                            $regFirstCount = 0;
                            foreach ($allRegions as $index => $rgn) {


                                if ($rgn['infoblock'] === $infoblock['title']) {
                                    $regFirstCount += 1;
                                }
                                if ($rgn['infoblock'] !== $infoblock['title']) {
                                    $title = $rgn['title'];


                                    if ($index > $regFirstCount) {
                                        $title = ', ' . $rgn['title'];
                                    }


                                    $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . $title;
                                    $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . $title;
                                }




                                if ($descriptionMode == 0) {


                                    // Log::channel('console')->info('tst infoblock', ['rgn' => $rgn]);
                                    // Log::channel('console')->info('tst infoblock', ['infoblock' => $infoblock['title']]);

                                    if ($rgn['infoblock'] !== $infoblock['title']) {
                                        // $infoblockDataRegion = Infoblock::where('code', $rgn['code'])->first();
                                        $rgn['name'] = $rgn['infoblock'];
                                        $groupItems[] = $rgn;
                                        array_push($currentPage['items'], $rgn);
                                    }
                                }
                            }
                        }
                    }
                    if ($infoblockData) {
                        $groupItems[] = $infoblockData;
                        array_push($currentPage['items'], $infoblockData);
                    }
                }

                // Распределение элементов группы по страницам
                while (!empty($groupItems)) {
                    $spaceLeft = $itemsPerPage - $currentPageItemsCount; // Сколько элементов помещается на страницу

                    if ($spaceLeft == 0) {
                        // Если на текущей странице нет места, переходим к следующей
                        $pages[] = $currentPage;
                        $currentPage = [
                            'groups' => [],
                            'items' => []

                        ];
                        $currentPageItemsCount = 0;
                        $spaceLeft = $itemsPerPage;
                    }

                    $itemsToAdd = array_splice($groupItems, 0, $spaceLeft); // Элементы, которые поместятся на страницу
                    if (!empty($itemsToAdd)) {
                        // Добавляем часть группы на текущую страницу

                        $currentPage['groups'][] = [
                            'name' => $group['groupsName'],
                            'items' => $itemsToAdd
                        ];
                        $currentPageItemsCount += count($itemsToAdd);
                        if ($group['groupsName'] == 'Нормативно-правовые акты') {
                            if ($group['groupsName'] == 'Нормативно-правовые акты') {
                                // Log::channel('console')->info('ITEMS', ['items' => $itemsToAdd]);
                                if ($allRegionsCount > 10) {

                                    $currentPageItemsCount += 1;
                                }
                                if ($allRegionsCount > 20) {

                                    $currentPageItemsCount += 1;
                                    if ($styleMode == 'table') {
                                        $currentPageItemsCount += 2;
                                    }
                                    //
                                }
                                if ($allRegionsCount > 30) {

                                    $currentPageItemsCount += 1;
                                }
                                if ($allRegionsCount > 30) {

                                    $currentPageItemsCount += 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Добавляем последнюю страницу, если она содержит элементы
        if (!empty($currentPage['groups'])) {
            $pages[] = $currentPage;
        }


        $withPrice = $this->getWithPrice($pages, $descriptionMode, $styleMode);
        $result = [
            'styleMode' => $styleMode,
            'descriptionMode' => $descriptionMode,
            'pages' => $pages,
            'withPrice' => $withPrice,
            'complectName' => $complectName,
            'infoblocks' => $this->getAllInfoblocks()


        ];

        return $result;
    }

    protected function getAllInfoblocks()
    {

        $resultGroups = [];
        $erSubstring = "Пакет Энциклопедий решений";
        $allRegions = [];
        $allRegionsCount = 0;
        $descriptionMode = $this->infoblocksOptions['description']['id'];

        if (!empty($regions)) {

            foreach ($regions as $weightType) {
                foreach ($weightType as $rgn) {
                    array_push($allRegions, $rgn);
                }
            }
            $allRegionsCount = count($allRegions);
        }
        foreach ($this->complect as $group) {

            if (stripos($group['groupsName'], $erSubstring) === false) {
                $groupItems = [];
                foreach ($group['value'] as $infoblock) {
                    if (!array_key_exists('code', $infoblock)) {
                        continue;
                    }

                    $infoblockData = Infoblock::where('code', $infoblock['code'])->first();
                    if ($infoblock['code'] == 'reg') {
                        $infoblockData['name'] = $this->region['infoblock'];

                        // Извлечение названия региона из заголовка
                        $regionName = trim(str_replace("Законодательство", "", $this->region['infoblock']));

                        // Замена в тексте
                        $infoblockData['descriptionForSale'] = preg_replace("/органов власти регионов/u", "органов $regionName", $infoblockData['descriptionForSale']);
                        $infoblockData['shortDescription'] = preg_replace("/местного законодательства/u", "$regionName", $infoblockData['shortDescription']);

                        if ($allRegionsCount > 1) {
                            $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . '\\n А также законодательство регионов: \\n';
                            $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . '\\n А также законодательство регионов: \\n';
                            $regFirstCount = 0;
                            foreach ($allRegions as $index => $rgn) {


                                if ($rgn['infoblock'] === $infoblock['title']) {
                                    $regFirstCount += 1;
                                }
                                if ($rgn['infoblock'] !== $infoblock['title']) {
                                    $title = $rgn['title'];


                                    if ($index > $regFirstCount) {
                                        $title = ', ' . $rgn['title'];
                                    }


                                    $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . $title;
                                    $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . $title;
                                }


                                if ($descriptionMode == 0) {

                                    if ($rgn['infoblock'] !== $infoblock['title']) {
                                        // $infoblockDataRegion = Infoblock::where('code', $rgn['code'])->first();
                                        $rgn['name'] = $rgn['infoblock'];
                                        $groupItems[] = $rgn;
                                    }
                                }
                            }
                        }
                    }
                    if ($infoblockData) {
                        $groupItems[] = $infoblockData;
                    }
                }
                $resultGroup = $group;
                $resultGroup['infoblocks'] = $groupItems;
                $resultGroups[] = $resultGroup;
            }
        }

        return $resultGroups;
    }
    protected function determineItemsPerPage($descriptionMode, $styleMode)
    {
        $itemsPerPage = 20;

        if ($styleMode === 'list') {

            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 32;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 10;
            } else if ($descriptionMode === 2) {
                $itemsPerPage = 8;
            }
        } else if ($styleMode === 'table') {
            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 60;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 16;
            } else  if ($descriptionMode === 2) {
                $itemsPerPage = 8;
            }
        } else {
            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 24;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 9;
            } else if ($descriptionMode === 2) {
                $itemsPerPage = 8;
            }
        }

        return $itemsPerPage;
    }




    protected function getWithPrice(
        $pages,
        $descriptionMode,
        $styleMode

    ) {
        $productsCount = $this->productsCount;
        $salePhrase = $this->salePhrase;
        $priceFirst = $this->priceFirst;
        $isWithPrice = false;
        $salePhraseLength = mb_strlen($salePhrase, "UTF-8");
        $entersCount = substr_count($salePhrase, "\n");


        $lastPageItemsCount = 0;
        $lastPage = end($pages);
        if (is_array($lastPage) && isset($lastPage['groups']) && is_array($lastPage['groups'])) {
            $lastPageGroups = $lastPage['groups'];
            foreach ($lastPageGroups as  $lastPageGroup) {
                if (isset($lastPageGroup['items']) && is_array($lastPageGroup['items'])) {
                    $currentGrupItems = $lastPageGroup['items'];
                    $currentGrupItemsCount = count($currentGrupItems);
                    $lastPageItemsCount = $lastPageItemsCount + $currentGrupItemsCount;
                }
            }
        }


        if (!$priceFirst) {
            if ((
                $productsCount < 4 && $salePhraseLength < 150 && $entersCount < 3
            ) || ($productsCount < 3 && $salePhraseLength <= 400 && $entersCount < 4)) {

                if ($styleMode === 'list') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 20) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 5) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 5) {
                            $isWithPrice = true;
                        }
                    }
                } else if ($styleMode === 'table') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 38) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 11) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 9) {
                            $isWithPrice = true;
                        }
                    }
                } else {
                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 10) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 6) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 6) {
                            $isWithPrice = true;
                        }
                    }
                }
            } else if ($productsCount < 5 && ($salePhraseLength < 500 || $entersCount < 4)) {    //если товаров больше или текст описания большой


                if ($styleMode === 'list') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 10) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 3) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 2) {
                            $isWithPrice = true;
                        }
                    }
                } else if ($styleMode === 'table') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 14) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 9) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 4) {
                            $isWithPrice = true;
                        }
                    }
                } else {
                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 7) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 6) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 3) {
                            $isWithPrice = true;
                        }
                    }
                }
            }
        } else {
            if ($productsCount < 4 || ($productsCount < 5 && $entersCount < 4) || ($productsCount < 6 && $entersCount < 1)) {

                $isWithPrice = true;
            }
        }




        return $isWithPrice;
    }
}
