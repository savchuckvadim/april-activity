<?php

namespace App\Http\Controllers\PortalInstall;

use App\Http\Controllers\Admin\FieldController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InstallHelpers\GoogleInstallController;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\FItem;
use App\Models\Template;
use Google\Service\ServiceConsumerManagement\Http;

class TempalteFieldsInstallController extends Controller
{


    public static function setFields($templateId, $token)
    {
        $resultField = null;
        $results = [];
        try {

            $googleData = GoogleInstallController::getData($token);

            if (!empty($googleData)) {
                if (!empty($googleData['fields'])) {
                    $fields = $googleData['fields'];
                }
            }


            if (!empty($fields) && is_array($fields)) {

                $template = Template::find($templateId);

                if ($template) {
                    if (!empty($fields)) {

                        if (is_array($fields)) {

                            foreach ($fields as $fieldData) {
                                # code...
                                sleep(0.5);
                                $fieldController = new FieldController;
                                $resultFields = $fieldController->processFields([$fieldData], $template);
                                if ($resultFields && is_array($resultFields)) {
                                    $resultField = $resultFields[0];
                                    array_push($results, [
                                        'templateId' => $templateId,
                                        'field' => $resultField,
                                        'fieldData' => $fieldData
                                    ]);
                                } else {
                                    array_push($results, [
                                        'message' => 'Field was not creating',
                                        'resultFields' => $resultFields,
                                        'template' => $template,
                                        'fieldData' => $fieldData
                                    ]);
                                }

                               
                            }

                            return APIController::getSuccess(
                                [
                                    'results' => $results
                                ]
                            );
                        }
                    }
                }
            }
            return APIController::getError(
                'Template not found',
                ['template' => $template, 'fields' => $fields]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
}
