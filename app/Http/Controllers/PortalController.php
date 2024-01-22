<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortalResource;
use App\Http\Resources\ProviderCollection;
use App\Http\Resources\TemplateCollection;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PortalController extends Controller
{
    public static function setPortal($number, $domain, $key, $clientId, $secret, $hook)
    {
        try {
            $data = [
                'number' => $number,
                'domain' => $domain,
                'key' => $key,
                'C_REST_CLIENT_ID' => $clientId,
                'C_REST_CLIENT_SECRET' => $secret,
                'C_REST_WEB_HOOK_URL' => $hook,
            ];
            if (empty($domain) || empty($key) || empty($clientId) || empty($secret) || empty($hook)) {
                return response([
                    'resultCode' => 1, 'message' => "invalid data",
                    'data' => [
                        'number' => $number,
                        'domain' => $domain,
                        'key' => $key,
                        'C_REST_CLIENT_ID' => $clientId,
                        'C_REST_CLIENT_SECRET' => $secret,
                        'C_REST_WEB_HOOK_URL' => $hook,
                    ]
                ]);
            }

            $data['key'] = Crypt::encryptString($key);
            $data['C_REST_CLIENT_ID'] = Crypt::encryptString($clientId);
            $data['C_REST_CLIENT_SECRET']  = Crypt::encryptString($secret);
            $data['C_REST_WEB_HOOK_URL'] =   Crypt::encryptString($hook);

            $portal = Portal::firstOrCreate(
                ['domain' => $domain], // Условия для поиска
                $data // Значения по умолчанию, если создается новая запись
            );


            $portal->save();

            return response([
                'resultCode' => 0,
                'message' => 'success',
                '$number' => $number,
                'portal' => [
                    'id' => $portal->id,
                    'number' => $portal->number,
                    'domain' => $domain,
                    'key' => $portal->key,
                    'C_REST_CLIENT_ID' => $portal->C_REST_CLIENT_ID,
                    'C_REST_CLIENT_SECRET' => $portal->C_REST_CLIENT_SECRET,
                    'C_REST_WEB_HOOK_URL' => $portal->C_REST_WEB_HOOK_URL,
                ]

            ]);
        } catch (\Throwable $th) {
            return  APIController::getResponse(1, $th->getMessage(), $data);
        }
    }
    public static function getPortal($domain)
    {

        $portal = Portal::where('domain', $domain)->first();

        if (!$portal) {
            return response([
                'resultCode' => 1,
                'message' => 'portal does not exist!'
            ]);
        }

        return response([
            'resultCode' => 0,
            'portal' => [
                'id' => $portal->id,
                'domain' => $portal->number,
                'domain' => $domain,
                'key' => $portal->getKey(),
                'C_REST_CLIENT_ID' => $portal->getClientId(),
                'C_REST_CLIENT_SECRET' => $portal->getSecret(),
                'C_REST_WEB_HOOK_URL' => $portal->getHook(),
            ]

        ]);
    }
    public static function getPortalById($portalId)
    {

        $portal = Portal::find($portalId);

        if (!$portal) {
            return response([
                'resultCode' => 1,
                'portalId' => $portalId,
                'message' => 'portal not found'
            ]);
        }
        $resource = new PortalResource($portal);
        return APIController::getSuccess(['portal' => $resource]);
        // return response(

        //     [
        //         'resultCode' => 0,
        //         'message' => 'success',
        //         'portal' => [
        //             'id' => $portal->id,
        //             'number' => $portal->number,
        //             'domain' => $portal->domain,
        //             'key' => $portal->key,
        //             'C_REST_CLIENT_ID' => $portal->C_REST_CLIENT_ID,
        //             'C_REST_CLIENT_SECRET' => $portal->C_REST_CLIENT_SECRET,
        //             'C_REST_WEB_HOOK_URL' => $portal->C_REST_WEB_HOOK_URL,
        //             //     'key' => $portal->getKey(),
        //             //     'C_REST_CLIENT_ID' => $portal->getClientId(),
        //             //     'C_REST_CLIENT_SECRET' => $portal->getSecret(),
        //             //     'C_REST_WEB_HOOK_URL' => $portal->getHook(),
        //         ]

        //     ]
        // );
    }
    public static function getPortals()
    {

        $portals = Portal::all();

        if (!$portals) {
            return response([
                'resultCode' => 1,
                'message' => 'portals does not exist!'
            ]);
        }

        return response([
            'resultCode' => 0,
            'portals' => $portals
        ]);
    }

    public static function getInitial()
    {

        $initialPortal = Portal::getForm();
        $data = [
            'initial' => $initialPortal
        ];
        return APIController::getResponse(0, 'success', $data);
    }
    public static function deletePortal($portalId)
    {
        try {
            $portal = Portal::destroy($portalId);

            $portal = Portal::find($portalId);
            if (!$portal) {
                APIController::getResponse(0, 'success', ['portalId' => $portalId]);
            } else {
                return APIController::getResponse(1, 'error - portal was not deleted', $portal);
            }
        } catch (\Throwable $th) {
            return APIController::getResponse(1, $th->getMessage(), $portal);
        }
    }

    public static function innerGetPortal($domain)
    {

        $portal = Portal::where('domain', $domain)->first();

        if (!$portal) {
            return [
                'resultCode' => 1,
                'message' => 'portal does not exist!',
                'portal' => null
            ];
        }

        return [
            'resultCode' => 0,
            'portal' => [
                'id' => $portal->id,
                'number' => $portal->number,
                'domain' => $domain,
                'key' => $portal->getKey(),
                'C_REST_CLIENT_ID' => $portal->getClientId(),
                'C_REST_CLIENT_SECRET' => $portal->getSecret(),
                'C_REST_WEB_HOOK_URL' => $portal->getHook(),
            ]

        ];
    }

    public static function getProviders($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $providers = $portal->providers;
                if ($providers) {
                    $providersCollection = new ProviderCollection($providers);
                    if ($providersCollection) {

                        return APIController::getSuccess($providersCollection);
                    }
                }
            }
            return APIController::getError(

                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    public static function getTemplates($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $templates = $portal->templates;
                if ($templates) {
                    $templatesCollection = new TemplateCollection($templates);
                    if ($templatesCollection) {

                        return APIController::getSuccess($templatesCollection);
                    }
                }
            }
            return APIController::getError(

                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }

    public static function getSmarts($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $smarts = $portal->smarts;
                if ($smarts) {
                    return APIController::getSuccess(['smarts' => $smarts]);
                    // $smartsCollection = new TemplateCollection($smarts);
                    // if ($smartsCollection) {

                    //     return APIController::getSuccess($smartsCollection);
                    // }
                }
            }
            return APIController::getError(

                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    public static function getBitrixlists($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $bitrixlists = $portal->lists;
                if ($bitrixlists) {
                    return APIController::getSuccess(['bitrixlists' => $bitrixlists]);
                    // $smartsCollection = new TemplateCollection($smarts);
                    // if ($smartsCollection) {

                    //     return APIController::getSuccess($smartsCollection);
                    // }
                }
            }
            return APIController::getError(
                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    public static function getDepartaments($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $departaments = $portal->departaments;
                if ($departaments) {
                    return APIController::getSuccess(['departaments' => $departaments]);
                    // $smartsCollection = new TemplateCollection($smarts);
                    // if ($smartsCollection) {

                    //     return APIController::getSuccess($smartsCollection);
                    // }
                }
            }
            return APIController::getError(
                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    public static function getTimezones($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $timezones = $portal->timezones;
                if ($timezones) {
                    return APIController::getSuccess(['timezones' => $timezones]);
                    // $smartsCollection = new TemplateCollection($smarts);
                    // if ($smartsCollection) {

                    //     return APIController::getSuccess($smartsCollection);
                    // }
                }
            }
            return APIController::getError(
                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    public static function getCallingGroups($portalId)
    {
        try {
            $portal = Portal::find($portalId);

            if ($portal) {
                $callingGroups = $portal->callingGroups;
                if ($callingGroups) {
                    return APIController::getSuccess(['callingGroups' => $callingGroups]);
                    // $smartsCollection = new TemplateCollection($smarts);
                    // if ($smartsCollection) {

                    //     return APIController::getSuccess($smartsCollection);
                    // }
                }
            }
            return APIController::getError(
                'invalid data',
                ['portalId' => $portalId]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalId' => $portalId]
            );
        }
    }
    // public function getDomain()
    // {
    //     return Crypt::decryptString($this->domain);
    // }


}
