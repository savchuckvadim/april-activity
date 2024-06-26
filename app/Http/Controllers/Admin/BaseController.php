<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\CallingController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public static function initial($entityType, $parentType = null, $parentId = null)
    {

        try {
            switch ($entityType) {
                case 'infoblock':
                    return InfoblockController::getInitial();
                    break;
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

                    // case 'smart':
                case 'bitrixlist':
                    // case 'departament':
                case 'timezone':
                    // case 'counter':
                    $controllerName = ucfirst($entityType) . 'Controller';
                    $controllerClass = "App\\Http\\Controllers\\Admin" . $controllerName; // Предполагая, что все контроллеры находятся в каталоге App\Http\Controllers

                    if (class_exists($controllerClass)) {
                        $controller = app()->make($controllerClass);
                    }
                    return $controller->getInitial($parentId);

                    break;

                    // case 'callingGroup':

                    // return CallingController::getInitial();

                case 'provider':
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
        $all = $request->all();
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
                    // return FieldController::getInitialField();
                    break;

                case 'smart':
                case 'bitrixlist':
                case 'timezone':
                    // case 'counter':
                    $controllerName = ucfirst($entityType) . 'Controller';
                    $controllerClass = "App\\Http\\Controllers\\Admin" . $controllerName; // Предполагая, что все контроллеры находятся в каталоге App\Http\Controllers

                    if (class_exists($controllerClass)) {
                        $controller = app()->make($controllerClass);
                    }
                    return $controller->set($request);

                    break;

                    // case 'callingGroup':
                    //     return CallingController::set($request);
                    //     break;

                case 'departament':
                    return DepartamentController::set($request);
                    break;
                case 'rq':
                    return DepartamentController::set($request);
                    break;
                case 'item':
                default:
                    return APIController::getError(
                        'not found entity type',
                        [
                            'entityType' => $entityType,
                            'all' => $all
                        ]
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
    public static function update($entityType, $entityId, Request $request)
    {

        try {


            switch ($entityType) {
                case 'logo':
                case 'signature':
                case 'stamp':
                case 'qr':
                case 'file':
                    $fileController = new FileController;
                    return $fileController->updateFile($entityType, $entityId, $request);
                    break;
                case 'template':

                    // return TemplateController::initialTemplate();
                    break;
                case 'field':
                    // return FieldController::getInitialField();
                    break;

                case 'rq':
                    $qrController = new RqController;
                    return $qrController->update($entityType, $entityId, $request);
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
            if ($model && $modelId) {
                switch ($model) {
                    case 'logo':
                    case 'signature':
                    case 'stamp':
                    case 'qr':
                    case 'file':
                        return FileController::getFile($model, $modelId);

                    case 'template':

                        return TemplateController::getTemplate($modelId);

                    case 'field':
                        return FieldController::getField($modelId);


                    case 'smart':
                        // case 'bitrixlist':

                    case 'timezone':
                    case 'counter':
                        $controllerName = ucfirst($model) . 'Controller';
                        $controllerClass = "App\\Http\\Controllers\\Admin" . $controllerName; // Предполагая, что все контроллеры находятся в каталоге App\Http\Controllers

                        if (class_exists($controllerClass)) {
                            $controller = app()->make($controllerClass);
                        }
                        return $controller->get($modelId);

                    case 'departament':
                        return DepartamentController::get($modelId);
                        break;

                        // case 'callingGroup':

                        //     return CallingController::getCallingGroup($modelId);

                    case 'provider':
                    case 'item':
                        return FItemController::getFitem($modelId);

                    default:
                        return APIController::getError(
                            'model was not found',
                            ['model' => $model, 'modelId' => $modelId]
                        );
                }
            } else {
                return APIController::getError(
                    'model was not found',
                    ['model' => $model, 'modelId' => $modelId]
                );
            }
        } catch (\Throwable $th) {
            Log::error('Ошибка: ' . $model . '' . $th->getMessage());
            return APIController::getError(
                $th->getMessage(),
                ['model' => $model, 'modelId' => $modelId]
            );
        }
    }
    public static function getCollection($model)
    {

        try {
            if ($model) {
                switch ($model) {
                    case 'logos':
                    case 'signatures':
                    case 'stamps':
                    case 'qrs':
                    case 'files':
                        return FileController::getFiles($model);

                    case 'templates':
                        return TemplateController::getAllTemplates();

                    case 'fields':
                        return FieldController::getAllFields();

                    case 'items':
                        // return FItemController::getFitem($modelId);
                        // case 'callingGroups':
                        //     return CallingController::getAll();


                    case 'departaments':
                        return DepartamentController::getAll();

                    case 'bitrixlists':
                        return BitrixlistController::getAll();
                    case 'smarts':
                        return SmartController::getAll();


                    default:
                        return APIController::getError(
                            'model was not found',
                            ['model' => $model]
                        );
                }
            } else {
                return APIController::getError(
                    'model was not found',
                    ['model' => $model]
                );
            }
        } catch (\Throwable $th) {
            Log::error('Ошибка: ' . $model . '' . $th->getMessage());
            return APIController::getError(
                $th->getMessage(),
                ['model' => $model]
            );
        }
    }
    public static function delete($model, $modelId)
    {

        try {
            if ($model && $modelId) {
                switch ($model) {
                    case 'logo':
                    case 'signature':
                    case 'stamp':
                    case 'qr':
                    case 'file':
                        $fileController = new FileController;
                        return $fileController->deleteFile($model, $modelId);
                        break;
                    case 'smart':
                        return SmartController::delete($modelId);
                        break;

                    case 'departament':
                        return DepartamentController::delete($modelId);


                    case 'template':

                        // return TemplateController::getTemplate($modelId);

                    case 'field':
                        // return FieldController::getField($modelId);

                    case 'item':
                        // return FItemController::getFitem($modelId);



                    default:
                        return APIController::getError(
                            'model was not found',
                            ['model' => $model, 'modelId' => $modelId]
                        );
                }
            } else {
                return APIController::getError(
                    'model was not found',
                    ['model' => $model, 'modelId' => $modelId]
                );
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
