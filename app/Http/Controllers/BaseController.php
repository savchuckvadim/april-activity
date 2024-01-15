<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public static function initial($entityType, $parentType = null, $parentId = null)
    {

        try {
            switch ($entityType) {
                case 'logo':
                case 'signature':
                case 'stamp':
                case 'qr':
                case 'file':
                    return FileController::getInitial();
                    break;
                case 'template':

                    return TemplateController::initialTemplate();
                    break;
                case 'field':
                    return FieldController::getInitialField();
                    break;
                case 'item':
                default:
                    return APIController::getError(
                        'not fount entity type',
                        ['entityType' => $entityType]
                    );
                    break;
            }
        } catch (\Throwable $th) {
            Log::error('Ошибка: ' . $entityType . '' . $th->getMessage());
            return APIController::getError(
                $th->getMessage(),
                ['entityType' => $entityType]
            );
        }
    }
    public static function setOrUpdate($entityType, $parentType, $parentId, Request $request)
    {

        try {


            switch ($entityType) {
                case 'logo':
                case 'signature':
                case 'stamp':
                case 'qr':
                case 'file':
                    $fileController = new FileController;
                    return $fileController->setFile($entityType, $parentType, $parentId, $request);
                    break;
                case 'template':

                    return TemplateController::initialTemplate();
                    break;
                case 'field':
                    return FieldController::getInitialField();
                    break;
                case 'item':
                default:
                    return APIController::getError(
                        'not found entity type',
                        ['entityType' => $entityType]
                    );
                    break;
            }
        } catch (\Throwable $th) {
            Log::error('Ошибка: ' . $entityType . '' . $th->getMessage());
            return APIController::getError(
                $th->getMessage(),
                ['entityType' => $entityType]
            );
        }
    }

    public static function get($model, $modelId)
    {

        try {
            switch ($model && $modelId) {
                case 'logo':
                case 'signature':
                case 'stamp':
                case 'qr':
                case 'file':
                    return FileController::getFile($model,$modelId);
                   
                case 'template':

                    return TemplateController::getTemplate($modelId);
                   
                case 'field':
                    return FieldController::getField($modelId);
                    
                case 'item':
                    return FItemController::getFitem($modelId);

                default:
                    return APIController::getError(
                        'model was not found',
                        ['model' => $model, 'modelId' => $modelId]
                    );
                    break;
            }
        } catch (\Throwable $th) {
            Log::error('Ошибка: ' . $model . '' . $th->getMessage());
            return APIController::getError(
                $th->getMessage(),
                ['model' => $model, 'modelId' => $modelId]
            );
        }
    }
}
