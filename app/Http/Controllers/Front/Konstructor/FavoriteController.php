<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BxDocumentDeal;
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


            $result = [
                'favorites' => $favorites
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
    public function get() {}
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
                'portalId' => $request->portalId,
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



            return APIController::getSuccess([

                'favorite' => $resultDeal,

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


                return APIController::getSuccess([

                    'favorite' => $result,

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
}
