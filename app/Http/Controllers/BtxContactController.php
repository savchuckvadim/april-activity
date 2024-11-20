<?php

namespace App\Http\Controllers;

use App\Http\Resources\BtxContactResource;
use App\Models\BtxContact;
use App\Models\Portal;
use Illuminate\Http\Request;

class BtxContactController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = BtxContact::getForm($portalId);
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
            $contact = BtxContact::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $contact = new BtxContact();
                $contact->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_contacts,id',
            'name' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'portal_id' => 'required|string',
            
        ]);

        if ($contact) {
            // Создание нового Counter
            $contact->name = $validatedData['name'];
            $contact->title = $validatedData['title'];
            $contact->code = $validatedData['code'];
            $contact->portal_id = $validatedData['portal_id'];
        
           
            $contact->save(); // Сохранение Counter в базе данных
            $resultcontact = new BtxContactResource($contact);
            return APIController::getSuccess(
                ['contact' => $resultcontact, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }
    public static function get($contactId)
    {
        try {
            $contact = BtxContact::find($contactId);

            if ($contact) {
                $resultcontact = new BtxContactResource($contact);
                return APIController::getSuccess(
                    ['contact' => $contact]
                );
            } else {
                return APIController::getError(
                    'contact was not found',
                    ['contact' => $contact]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['contactId' => $contactId]
            );
        }
    }

    public static function delete($contactId)
    {
        $contact = BtxContact::find($contactId);

        if ($contact) {
            // Получаем все связанные поля
            $contact->delete();
            return APIController::getSuccess($contact);
        } else {

            return APIController::getError(
                'contact not found',
                null
            );
        }
    }

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $contacts = $portal->contacts;
        if ($contacts) {

            return APIController::getSuccess(
                ['contacts' => $contacts]
            );
        }


        return APIController::getError(
            'contacts was not found',
            ['portal id' => $portalId]

        );
    }

    public static function getFields($contactId)
    {

        try {
            $contact = BtxContact::find($contactId);

            if ($contact) {
                $bitrixfields = $contact->bitrixfields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'contact was not found',
                    ['contact' => $contact]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['contactId' => $contactId]
            );
        }
    }
}
