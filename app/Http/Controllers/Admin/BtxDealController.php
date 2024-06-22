<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\BtxDealResource;
use App\Models\BtxDeal;
use App\Models\Portal;
use Illuminate\Http\Request;

class BtxDealController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = BtxDeal::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }


    public static function store(Request $request)
    {
        $id = null;
        $portal = null;
        if (isset($request['id'])) {
            $id = $request['id'];
            $deal = BtxDeal::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $deal = new BtxDeal();
                $deal->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_deals,id',
            'name' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'portal_id' => 'required|string',
            
        ]);

        if ($deal) {
            // Создание нового Counter
            $deal->name = $validatedData['name'];
            $deal->title = $validatedData['title'];
            $deal->code = $validatedData['code'];
            $deal->portal_id = $validatedData['portal_id'];
        
           
            $deal->save(); // Сохранение Counter в базе данных
            $resultDeal = new BtxDealResource($deal);
            return APIController::getSuccess(
                ['deal' => $resultDeal, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }
    public static function get($dealId)
    {
        try {
            $deal = BtxDeal::find($dealId);

            if ($deal) {
                $resultDeal = new BtxDealResource($deal);
                return APIController::getSuccess(
                    ['deal' => $resultDeal]
                );
            } else {
                return APIController::getError(
                    'deal was not found',
                    ['deal' => $deal]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['smartId' => $dealId]
            );
        }
    }

    public static function delete($dealId)
    {
        $deal = BtxDeal::find($dealId);

        if ($deal) {
            // Получаем все связанные поля
            $deal->delete();
            return APIController::getSuccess($deal);
        } else {

            return APIController::getError(
                'deal not found',
                null
            );
        }
    }

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $deals = $portal->deals;
        if ($deals) {

            return APIController::getSuccess(
                ['deals' => $deals]
            );
        }


        return APIController::getError(
            'smarts was not found',
            ['portal id' => $portalId]

        );
    }
    public static function getCategories($dealId)
    {

        try {
            $deal = BtxDeal::find($dealId);

            if ($deal) {
                $categories = $deal->categories;
                return APIController::getSuccess(
                    ['categories' => $categories]
                );
            } else {
                return APIController::getError(
                    'deal was not found',
                    ['deal' => $deal]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['dealId' => $dealId]
            );
        }
    }

    public static function getFields($dealId)
    {

        try {
            $deal = BtxDeal::find($dealId);

            if ($deal) {
                $bitrixfields = $deal->bitrixfields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'deal was not found',
                    ['deal' => $deal]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['dealId' => $dealId]
            );
        }
    }
}
