<?php

namespace App\Http\Controllers\BitrixApp;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Bitrix\BitrixApp;
use App\Models\Bitrix\BitrixToken;
use App\Models\Portal;
use Illuminate\Http\Request;


class BitrixAppController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $domain = null;
        try {
            $data = $request->validate([
                'domain' => 'required|string',
                'code' => 'required|string',
                'group' => 'required|string',
                'type' => 'required|string',
                'status' => 'required|string',

                'token.client_id' => 'required|string',
                'token.client_secret' => 'required|string',
                'token.access_token' => 'required|string',
                'token.refresh_token' => 'required|string',
                'token.expires_at' => 'required|date',
                'token.application_token' => 'nullable|string',
            ]);

            $domain = $data['domain'];
            // 1. Найти портал
            $portal = Portal::where('domain', $domain)->firstOrFail();

            // 2. Найти или создать BitrixApp
            $bitrixApp = BitrixApp::updateOrCreate(
                [
                    'portal_id' => $portal->id,
                    'code' => $data['code'],
                ],
                [
                    'group' => $data['group'],
                    'type' => $data['type'],
                    'status' => $data['status'],
                ]
            );

            // 3. Обновить или создать токен
            BitrixToken::updateOrCreate(
                ['bitrix_app_id' => $bitrixApp->id],
                [
                    'client_id' => $data['token']['client_id'],
                    'client_secret' => $data['token']['client_secret'],
                    'access_token' => $data['token']['access_token'],
                    'refresh_token' => $data['token']['refresh_token'],
                    'expires_at' => $data['token']['expires_at'],
                    'application_token' => $data['token']['application_token'] ?? null,
                ]
            );

            return APIController::getSuccess([
                'message' => 'Bitrix App and Token saved',
                'app_id' => $bitrixApp->id,
                'app' => $bitrixApp,
                'domain' => $domain,
            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('Bitrix App and Token failed', [
                'domain' => $domain,
                'request' => $request,
                'details' => $errorMessages,
            ]);
        }
    }

    public function chek(Request $request)
    {
        $domain = null;
        try {
            $data = $request->validate([
                'domain' => 'required|string',
                'code' => 'required|string',
                'group' => 'required|string',
                'type' => 'required|string',
                'status' => 'required|string',

                'token.client_id' => 'required|string',
                'token.client_secret' => 'required|string',
                'token.access_token' => 'required|string',
                'token.refresh_token' => 'required|string',
                'token.expires_at' => 'required|date',
                'token.application_token' => 'nullable|string',
            ]);

          

            return APIController::getSuccess([
                'message' => 'Bitrix App setup check',
                'data' => $data,
                'request' => $request,
                'domain' => $domain,
            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('Bitrix App and Token check fail', [
                'request' => $request,
                'domain' => $domain,
                'details' => $errorMessages,
            ]);
        }
    }
}
