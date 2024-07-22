<?php

namespace App\Http\Controllers\PortalInstall;

use App\Http\Controllers\Admin\FieldController;
use App\Http\Controllers\Admin\InfoblockController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InstallHelpers\GoogleInstallController;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\FItem;
use App\Models\Template;
use Google\Service\ServiceConsumerManagement\Http;

class InfoblockInstallController extends Controller
{


    public static function setIblocks($templateId, $token)
    {
        $result = null;
        $infoblocks = [];
        try {

            $googleData = GoogleInstallController::getData($token);

            if (!empty($googleData)) {
                if (!empty($googleData['infoblocks'])) {
                    $infoblocks = $googleData['infoblocks'];
                }
            }

            if(!empty($infoblocks)){
                $result = InfoblockController::setInfoBlocks($infoblocks);
                return APIController::getSuccess(
                  
                    ['infoblocks' => $result, 'googleData' => $googleData]
                );

            }

          
            return APIController::getError(
                'infoblocks not found',
                ['infoblocks' => $infoblocks, 'googleData' => $googleData]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
}
