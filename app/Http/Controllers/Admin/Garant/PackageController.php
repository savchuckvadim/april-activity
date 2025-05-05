<?php

namespace App\Http\Controllers\Admin\Garant;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Garant\PackageResource;
use App\Models\Garant\GarantPackage;
use App\Models\Garant\Infoblock;
use App\Models\Garant\InfoGroup;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public static function getInitial()
    {
        $initialData = GarantPackage::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {
        try {
            $request->merge([
                'withABS' => filter_var($request->input('withABS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'isChanging' => filter_var($request->input('isChanging'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withDefault' => filter_var($request->input('withDefault'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);

            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:garant_packages,id',
                'name' => 'required|string',
                'fullName' => 'required|string',
                'shortName' => 'required|string',
                'description' => 'sometimes|nullable|string',
                'code' => 'required|string',
                'type' => 'required|string',
                'color' => 'sometimes|nullable|string',
                'weight' => 'required|string',
                'abs' => 'sometimes|nullable|string',
                'number' => 'required|string',
                'productType' => 'required|string',
                'withABS' => 'required|boolean',
                'isChanging' => 'required|boolean',
                'withDefault' => 'required|boolean',
                'infoblock_id' => 'nullable|exists:infoblocks,id',
                'info_group_id' => 'nullable|exists:info_groups,id',
            ]);

            $currentPackage = null;
            if (!empty($validatedData['id'])) {
                $currentPackage = GarantPackage::find($validatedData['id']);
            }

            if (empty($currentPackage)) {
                $currentPackage = new GarantPackage($validatedData);
            }

            if (!empty($currentPackage)) {
                $currentPackage->save();
            }

            return APIController::getSuccess(
                ['package' => $currentPackage]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                'package was not updated',
                [$th->getMessage()]
            );
        }
    }

    public static function get($packageId)
    {
        try {
            $package = GarantPackage::find($packageId);

            if ($package) {
                $package = new PackageResource($package);
                return APIController::getSuccess(
                    ['package' => $package]
                );
            } else {
                return APIController::getError(
                    'package was not found',
                    ['package' => $package]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['packageId' => $packageId]
            );
        }
    }

    public static function getAll()
    {
        $packages = null;
        try {
            $packages = GarantPackage::all();

            return APIController::getSuccess(
                ['packages' => $packages]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['packages' => $packages]
            );
        }
    }

    public static function infoblocks($packageId)
    {
        try {
            $package = GarantPackage::with('infoblock')->find($packageId);

            if (!$package) {
                return APIController::getError('package was not found', ['packageId' => $packageId]);
            }

            $infoblocks = Infoblock::all();
            $linkedInfoblockId = $package->infoblock_id;

            $iblockFields = [];
            foreach ($infoblocks as $key => $infoblock) {
                array_push(
                    $iblockFields,
                    [
                        'id' => $infoblock->id,
                        'title' => $infoblock->name,
                        'entityType' => 'garant_packages',
                        'name' => $infoblock->code,
                        'apiName' => $infoblock->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => $infoblock->id === $linkedInfoblockId,
                        'value' => $infoblock->id === $linkedInfoblockId ? 'В пакете' : '-',
                        'isCanAddField' => false,
                        'isRequired' => true,
                        'isLinked' => $infoblock->id === $linkedInfoblockId,
                    ]
                );
            }

            return APIController::getSuccess([
                'package' => new PackageResource($package),
                'pinfoblocks' => $iblockFields,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['packageId' => $packageId]);
        }
    }

    public static function infoGroups($packageId)
    {
        try {
            $package = GarantPackage::with('infoGroup')->find($packageId);

            if (!$package) {
                return APIController::getError('package was not found', ['packageId' => $packageId]);
            }

            $infoGroups = InfoGroup::all();
            $linkedInfoGroupId = $package->info_group_id;

            $groupFields = [];
            foreach ($infoGroups as $key => $infoGroup) {
                array_push(
                    $groupFields,
                    [
                        'id' => $infoGroup->id,
                        'title' => $infoGroup->name,
                        'entityType' => 'garant_packages',
                        'name' => $infoGroup->code,
                        'apiName' => $infoGroup->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => $infoGroup->id === $linkedInfoGroupId,
                        'value' => $infoGroup->id === $linkedInfoGroupId ? 'В пакете' : '-',
                        'isCanAddField' => false,
                        'isRequired' => true,
                        'isLinked' => $infoGroup->id === $linkedInfoGroupId,
                    ]
                );
            }

            return APIController::getSuccess([
                'package' => new PackageResource($package),
                'pinfoGroups' => $groupFields,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['packageId' => $packageId]);
        }
    }

    public static function initRelations($packageId)
    {
        try {
            $package = GarantPackage::with(['infoblock', 'infoGroup'])->find($packageId);

            if (!$package) {
                return APIController::getError('package was not found', ['packageId' => $packageId]);
            }

            $infoblocks = Infoblock::all();
            $infoGroups = InfoGroup::all();

            $linkedInfoblockId = $package->infoblock_id;
            $linkedInfoGroupId = $package->info_group_id;

            $iblockFields = [];
            foreach ($infoblocks as $key => $infoblock) {
                array_push(
                    $iblockFields,
                    [
                        'id' => $infoblock->id,
                        'title' => $infoblock->name,
                        'entityType' => 'infoblock',
                        'name' => $infoblock->code,
                        'apiName' => $infoblock->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => $infoblock->id === $linkedInfoblockId,
                        'value' => $infoblock->id === $linkedInfoblockId,
                        'isCanAddField' => false,
                        'isRequired' => true,
                        'isLinked' => $infoblock->id === $linkedInfoblockId,
                    ]
                );
            }

            $groupFields = [];
            foreach ($infoGroups as $key => $infoGroup) {
                array_push(
                    $groupFields,
                    [
                        'id' => $infoGroup->id,
                        'title' => $infoGroup->name,
                        'entityType' => 'infoGroup',
                        'name' => $infoGroup->code,
                        'apiName' => $infoGroup->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => $infoGroup->id === $linkedInfoGroupId,
                        'value' => $infoGroup->id === $linkedInfoGroupId,
                        'isCanAddField' => false,
                        'isRequired' => true,
                        'isLinked' => $infoGroup->id === $linkedInfoGroupId,
                    ]
                );
            }

            $relation = [
                'apiName' => 'package',
                'title' => 'Пакет Гарант',
                'entityType' => 'entity',
                'groups' => [
                    [
                        'groupName' => 'Инфоблоки',
                        'apiName' => 'infoblock',
                        'entityType' => 'group',
                        'isCanAddField' => true,
                        'isCanDeleteField' => true,
                        'fields' => $iblockFields,
                        'relations' => [],
                    ],
                    [
                        'groupName' => 'Группы инфоблоков',
                        'apiName' => 'infoGroup',
                        'entityType' => 'group',
                        'isCanAddField' => true,
                        'isCanDeleteField' => true,
                        'fields' => $groupFields,
                        'relations' => [],
                    ]
                ]
            ];

            return APIController::getSuccess([
                'package' => new PackageResource($package),
                'relation' => $relation,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['packageId' => $packageId]);
        }
    }

    public function storeRelations(Request $request, int $packageId)
    {
        try {
            $package = GarantPackage::find($packageId);

            if (!$package) {
                return APIController::getError('package was not found', ['packageId' => $packageId]);
            }

            $relationGroups = $request->groups;
            $relationInfoblock = [];
            $relationInfoGroup = [];

            foreach ($relationGroups as $group) {
                if ($group['apiName'] === 'infoblock') {
                    foreach ($group['fields'] as $field) {
                        if ($field['value']) {
                            $package->infoblock_id = $field['id'];
                            array_push($relationInfoblock, $field);
                        }
                    }
                } elseif ($group['apiName'] === 'infoGroup') {
                    foreach ($group['fields'] as $field) {
                        if ($field['value']) {
                            $package->info_group_id = $field['id'];
                            array_push($relationInfoGroup, $field);
                        }
                    }
                }
            }

            $package->save();

            return APIController::getSuccess([
                'result' => [
                    'packageId' => $packageId,
                    'relationInfoblock' => $relationInfoblock,
                    'relationInfoGroup' => $relationInfoGroup,
                ]
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['packageId' => $packageId]);
        }
    }
}
