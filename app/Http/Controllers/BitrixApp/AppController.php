<?php

namespace App\Http\Controllers\BitrixApp;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Bitrix\BitrixAppSecret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;


class AppController extends Controller
{
    public function storeOrUpdate(Request $request)
    {

        try {
            $data = $request->validate([
                'code' => 'required|string',
                'group' => 'required|string',
                'type' => 'required|string',
                'client_id' => 'required|string',
                'client_secret' => 'required|string',

            ]);

            $data['client_id'] = Crypt::encryptString($data['client_id']);
            $data['client_secret']  = Crypt::encryptString($data['client_secret']);
            // 2. Найти или создать BitrixApp
            $bitrixApp = BitrixAppSecret::updateOrCreate(
                [

                    'code' => $data['code'],
                ],
                [
                    'group' => $data['group'],
                    'type' => $data['type'],
                    'client_id' => $data['client_id'],
                    'client_secret' => $data['client_secret'],
                ]
            );



            return APIController::getSuccess([
                'message' => 'Bitrix App  saved',
                'app_id' => $bitrixApp->id,
                'app' => $bitrixApp,
                'client_id' => $bitrixApp->client_id,
                'client_secret' => $bitrixApp->client_secret,

            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('Bitrix App failed', [

                'request' => $request,
                'details' => $errorMessages,
            ]);
        }
    }

    public function getByCode(Request $request)
    {

        try {
            $data = $request->validate([
                'code' => 'required|string',
            ]);

            $bitrixApp = BitrixAppSecret::where('code', $data['code'])->first();

            $clientId = null;
            $secretId = null;
            if (!empty($bitrixApp)) {
                if (!empty($bitrixApp->getClientId())) {
                    $clientId = $bitrixApp->getClientId();
                }
                if (!empty($bitrixApp->getSecret())) {
                    $secretId = $bitrixApp->getSecret();
                }
            }
            return APIController::getSuccess([
                'result' => [
                    'app' => $bitrixApp,
                    'client_id' => $clientId,
                    'client_secret' => $secretId,
                ]
            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('Bitrix App failed', [

                'request' => $request,
                'details' => $errorMessages,
            ]);
        }
    }
}
