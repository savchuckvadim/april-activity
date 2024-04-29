<?php

namespace App\Http\Controllers;

use App\Http\Resources\BtxCompanyResource;
use App\Models\BtxCompany;
use App\Models\Portal;
use Illuminate\Http\Request;

class BtxCompanyController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = BtxCompany::getForm($portalId);
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
            $company = BtxCompany::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $company = new BtxCompany();
                $company->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_deals,id',
            'name' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'portal_id' => 'required|string',
            
        ]);

        if ($company) {
            // Создание нового Counter
            $company->name = $validatedData['name'];
            $company->title = $validatedData['title'];
            $company->code = $validatedData['code'];
            $company->portal_id = $validatedData['portal_id'];
        
           
            $company->save(); // Сохранение Counter в базе данных
            $resultcompany = new BtxCompanyResource($company);
            return APIController::getSuccess(
                ['company' => $resultcompany, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }
    public static function get($companyId)
    {
        try {
            $company = BtxCompany::find($companyId);

            if ($company) {
                $resultcompany = new BtxCompanyResource($company);
                return APIController::getSuccess(
                    ['company' => $resultcompany]
                );
            } else {
                return APIController::getError(
                    'lead was not found',
                    ['company' => $company]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId]
            );
        }
    }

    public static function delete($companyId)
    {
        $company = BtxCompany::find($companyId);

        if ($company) {
            // Получаем все связанные поля
            $company->delete();
            return APIController::getSuccess($company);
        } else {

            return APIController::getError(
                'company not found',
                null
            );
        }
    }

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $companies = $portal->companies;
        if ($companies) {

            return APIController::getSuccess(
                ['companies' => $companies]
            );
        }


        return APIController::getError(
            'companies was not found',
            ['portal id' => $portalId]

        );
    }

    public static function getFields($companyId)
    {

        try {
            $company = BtxCompany::find($companyId);

            if ($company) {
                $bitrixfields = $company->fields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'company was not found',
                    ['company' => $company]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId]
            );
        }
    }
}
