<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\DTO\DocumentContract\RowDTO;
use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BxDocumentDeal;
use App\Models\DealDocumentOption;
use App\Models\Portal;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    //

    public function getFavorites($domain, $userId)
    {
        $favorites = [];
        try {
            $portal = Portal::where('domain', $domain)->first();
            $favorites = BxDocumentDeal::where('portalId', $portal->id)
                ->where('userId', $userId)
                ->where('isFavorite', true)->get();
            $resultFavorites = [];

            foreach ($favorites as $favorite) {
                $lightFavorite = $this->getLightFavorite($favorite);

                array_push($resultFavorites, $lightFavorite);
            }
            $result = [
                'favorites' => $resultFavorites
            ];
            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorites get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }

    public function get($id)
    {
        $favorite = null;
        try {
            $favorite = BxDocumentDeal::find($id);
            $options = $favorite->options;
            $favorite['options'] = $options;
            $result = [
                'favorite' => $favorite
            ];
            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorite get', [
                'error' =>  $errorMessages,
                'id' => $id,
                'favorite' => $favorite,

            ]);
        }
    }
    public function store(Request $request)
    {
        $resultDeal = null;
        try {
            $favoriteId = $request->favoriteId;
            $deal = [
                'app' => $request->app,
                // 'consalting' => $request->consalting,
                'contract' => $request->contract,
                'currentComplect' => $request->currentComplect,


                'dealName' => $request->dealName,
                'domain' => $request->domain,


                'global' => $request->global,
                // 'legalTech' => $request->legalTech,
                'od' => $request->od,
                'result' => $request->result,
                'rows' => $request->rows,

                'userId' => $request->userId,
                'isFavorite' => true

                // 'product' => $request->product,

            ];
            if (isset($request->regions)) {
                $deal['regions'] = $request->regions;
            }




            //search portal
            $searchingPortal = null;
            $searchingDeal = null;
            if (!empty($favoriteId)) {
                $searchingDeal = BxDocumentDeal::find($favoriteId);
            }



            if (!empty($searchingDeal)) {
                $searchingDeal->update($deal);
                $searchingDeal->save();
                $resultDeal =  $searchingDeal;
            } else {
                //search portal
                $searchingPortal = Portal::where('domain', $request->domain)
                    ->first();
                if ($searchingPortal) {
                    $newDeal = new BxDocumentDeal([...$deal, 'portalId' => $searchingPortal->id]);
                    $newDeal->save();
                    $resultDeal = $newDeal;
                }
            }

            $lightFavorite = $this->getLightFavorite($resultDeal);

            return APIController::getSuccess([
                'favoriteId' => $favoriteId,
                '$request->domain' => $request->domain,
                'favorite' => $lightFavorite,
                'searchingDeal' => $searchingDeal

            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorite store', [
                'error' =>  $errorMessages,
                'deal' => $resultDeal,

                'searchingDeal' => $searchingDeal
            ]);
        }
    }
    public function saveName(Request $request)
    {
        $resultDeal = null;
        try {
            $favoriteId = $request->id;
            $title = $request->name;
            if (!empty($favoriteId)) {
                $searchingDeal = BxDocumentDeal::find($favoriteId);
                $searchingDeal->title = $title;
                $searchingDeal->save();
                $result = BxDocumentDeal::find($favoriteId);

                $lightFavorite = $this->getLightFavorite($result);

                return APIController::getSuccess([

                    'favorite' => $lightFavorite,

                ]);
            } else {
                return APIController::getError(
                    'favorite was not found',
                    [
                        'id' => $favoriteId,

                    ]
                );
            }
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorite save name', [
                'error' =>  $errorMessages,
                'searchingDeal' => $resultDeal
            ]);
        }
    }


    public function delete($id)
    {

        try {
            $favorite = BxDocumentDeal::find($id);
            $favorite->delete();
            $favoriteResult = BxDocumentDeal::find($id);
            $result = [
                'favorite' => $favoriteResult
            ];
            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorites get', [
                'error' =>  $errorMessages,
                'id' => $id,
                'favorite' => $favorite,

            ]);
        }
    }

    protected function parseJson(string $json, array $default = []): array
    {
        $decoded = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    protected function getLightFavorite($favorite)
    {
        $rows = $this->parseJson($favorite['rows']);
        $total = new RowDTO($rows['sets']['general'][0]['total'][0]);
        $alternatives = $rows['sets']['alternative'];
        $comprasions = [];
        $salePhrase = '';
        $options = DealDocumentOption::where('dealDocumentFavoriteId', $favorite->id)->first();
        if (!empty($options)) {
            if (!empty($options['salePhrase'])) {
                $salePhraseData = $this->parseJson($options['salePhrase']);
                if (!empty($salePhraseData)) {
                    if (!empty($salePhraseData['value'])) {
                        $salePhrase = $salePhraseData['value'];
                    }
                }
            }
        }

        foreach ($alternatives as $alternative) {
            $alt = new RowDTO($alternative['total'][0]);
            $comprasions[] =
                '•  ' . $alt->name .
                ' • ' . $alt->product->supplyName .
                ' • Договор: ' . $alt->product->contract->aprilName .
                ' • Единица: ' . $alt->product->measureFullName;
        }
        return [
            'id' => $favorite->id,
            'title' => $favorite->title,
            'productName' => $total->name,
            'portalId' => $favorite->portalId,
            'alternative' => $comprasions,
            'userId' => $favorite->userId,
            'options' => $options,
            'salePhrase' => $salePhrase
        ];
    }
}
