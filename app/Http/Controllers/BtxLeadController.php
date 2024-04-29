<?php

namespace App\Http\Controllers;

use App\Http\Resources\BtxLeadResource;
use App\Models\BtxLead;
use App\Models\Portal;
use Illuminate\Http\Request;

class BtxLeadController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = BtxLead::getForm($portalId);
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
            $lead = BtxLead::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $lead = new BtxLead();
                $lead->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_deals,id',
            'name' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'portal_id' => 'required|string',
            
        ]);

        if ($lead) {
            // Создание нового Counter
            $lead->name = $validatedData['name'];
            $lead->title = $validatedData['title'];
            $lead->code = $validatedData['code'];
            $lead->portal_id = $validatedData['portal_id'];
        
           
            $lead->save(); // Сохранение Counter в базе данных
            $resultLead = new BtxLeadResource($lead);
            return APIController::getSuccess(
                ['lead' => $resultLead, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }
    public static function get($leadId)
    {
        try {
            $lead = BtxLead::find($leadId);

            if ($lead) {
                $resultDeal = new BtxLeadResource($lead);
                return APIController::getSuccess(
                    ['lead' => $resultDeal]
                );
            } else {
                return APIController::getError(
                    'lead was not found',
                    ['lead' => $lead]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['leadId' => $leadId]
            );
        }
    }

    public static function delete($leadId)
    {
        $lead = BtxLead::find($leadId);

        if ($lead) {
            // Получаем все связанные поля
            $lead->delete();
            return APIController::getSuccess($lead);
        } else {

            return APIController::getError(
                'lead not found',
                null
            );
        }
    }

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $leads = $portal->leads;
        if ($leads) {

            return APIController::getSuccess(
                ['leads' => $leads]
            );
        }


        return APIController::getError(
            'leads was not found',
            ['portal id' => $portalId]

        );
    }
    public static function getCategories($leadId)
    {

        try {
            $lead = BtxLead::find($leadId);

            if ($lead) {
                $categories = $lead->categories;
                return APIController::getSuccess(
                    ['categories' => $categories]
                );
            } else {
                return APIController::getError(
                    'lead was not found',
                    ['lead' => $lead]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['leadId' => $leadId]
            );
        }
    }

    public static function getFields($leadId)
    {

        try {
            $lead = BtxLead::find($leadId);

            if ($lead) {
                $bitrixfields = $lead->fields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'lead was not found',
                    ['lead' => $lead]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['leadId' => $leadId]
            );
        }
    }
}
