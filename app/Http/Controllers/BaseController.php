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
}
