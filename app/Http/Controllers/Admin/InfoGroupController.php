<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Garant\Infoblock;
use App\Models\Garant\InfoGroup;
use Illuminate\Http\Request;

class InfoGroupController extends Controller
{
    public static function getInitial()
    {

        $initialData = InfoGroup::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function setInfoGroups($infogroups)
    {
        $resultGroups = null;
        $resultCode = 1;
        $message = 'something wrong with infogroups online';
        $data = null;
        // $infoblocks = [
        //     ['code' => 'npa',
        //     'name' => 'НПА',
        //     'title' => 'Hohvfnbdy...',
        //     'description' => 'Hohvfnbdy...',
        //     'descriptionForSale' => 'Hohvfnbdy...',
        //     'shortDescription' => 'Hohvfnbdy...',],
        //     ...

        // ];


        foreach ($infogroups as $infogroup) {

            $newInfogroup = InfoGroup::where('code', $infogroup['code'])->first();
            if (!$newInfogroup) {
                $newInfogroup = new InfoGroup();
            }

            $newInfogroup['number'] = $infogroup['number'];
            $newInfogroup['code'] = $infogroup['code'];
            $newInfogroup['name'] = $infogroup['name'];
            $newInfogroup['title'] = $infogroup['title'];
            $newInfogroup['description'] = $infogroup['description'];
            $newInfogroup['descriptionForSale'] = $infogroup['descriptionForSale'];
            $newInfogroup['shortDescription'] = $infogroup['shortDescription'];
            $newInfogroup['type'] = $infogroup['type'];
            $newInfogroup['productType'] = $infogroup['productType'];

            $newInfogroup->save();
        }
        $resultGroups = InfoGroup::all();

        if ($resultGroups) {
            $resultCode = 0;
            $message = null;
            $data =  $resultGroups;
        }



        return response([
            'resultCode' => $resultCode,
            'data' => $data,
            'message' => $message
        ]);
    }

    public static function store(Request $request)
    {

        try {
            //code...

            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:info_groups,id',
                'name' => 'required|string',
                'title' => 'required|string',
                'code' => 'required|string',
                'type' => 'required|string',
                'description' => 'nullable|string',
                'number' => 'required|numeric',
                'descriptionForSale' => 'nullable|string',
                'shortDescription' => 'nullable|string',
                'productType' => 'required|string',


            ]);
            $infogroup =  null;
            if (!empty($validatedData['id'])) {
                $infogroup = InfoGroup::find($validatedData['id']);
            }

            if (empty($infogroup)) {
                $infogroup = new InfoGroup($validatedData);
            }

            if (!empty($infogroup)) {
                $infogroup->save();
            }


            return APIController::getSuccess(
                $infogroup

            );
        } catch (\Throwable $th) {
            //throw $th;
            return APIController::getError(
                'InfoGroup was not updated',
                [$th->getMessage()]

            );
        }
    }

    public static function get($infoGroupId)
    {
        try {

            $infogroup = InfoGroup::find($infoGroupId);


            if (!$infogroup) {
                return response([
                    'resultCode' => 1,
                    'infoGroupId' => $infoGroupId,
                    'message' => 'infoGroup not found'
                ]);
            }

            return APIController::getResponse(
                0,
                'success',
                ['infogroup' => $infogroup]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
            );
        }
    }

    public static function initRelations($infogroupId)
    {
        try {
            // Находим InfoGroup
            $infogroup = InfoGroup::with('infoblocks')->find($infogroupId);

            if (!$infogroup) {
                return APIController::getError('infogroup was not found', ['infogroupId' => $infogroupId]);
            }

            // Получаем **все** инфоблоки
            $infoblocks = Infoblock::all();

            // Получаем **связанные** инфоблоки (ID-шники)
            $linkedInfoblockIds = $infogroup->infoblocks->pluck('id')->toArray();

            // Формируем массив полей для фронта
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
                        'initialValue' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                        'value' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок

                        'isCanAddField' => false,
                        'isRequired' => true, // хотя бы одно поле в шаблоне должно быть
                        'isLinked' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                    ]
                );
            }
            $relation = [
                'apiName' => 'infogroup',
                'title' => 'Группа инфоблоков',
                'entityType' => 'entity',
                'groups' => [
                    [
                        'groupName' => 'Инфоблоки',
                        'apiName' => 'infoblock',
                        'entityType' => 'group',
                        'isCanAddField' => true,
                        'isCanDeleteField' => true,
                        'fields' =>  $iblockFields,

                        'relations' => [],

                    ]
                ]
            ];
            // Отправляем данные на фронт
            return APIController::getSuccess([
                'infogroup' =>  $infogroup,
                'relation' => $relation,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['infogroupId' => $infogroupId]
            );
        }
    }

    public function storeRelations(Request $request, int $infogroupId)
    {
        try {
            // Находим комплект
            $infogroup = InfoGroup::with('infoblocks')->find($infogroupId);

            if (!$infogroup) {
                return APIController::getError('infogroup was not found', ['infogroupId' => $infogroupId]);
            }
            $relationGroups = $request->groups;
            $relationInfoblock = [];
            foreach ($relationGroups as $group) {
                if ($group['apiName'] == 'infoblock') {
                    foreach ($group['fields'] as $field) {
                        $infoblock = Infoblock::find($field['id']);

                        if ($field['value']) {

                            array_push($relationInfoblock, $field);
                            $infogroup->infoblocks()->save($infoblock);  // Для hasMany связи в InfoGroup

                        } else {
                            $infoblock->group()->dissociate();  // Удаляем связь с группой
                            $infoblock->save();
                        }
                    }
                }
            }
            $infogroup = InfoGroup::with('infoblocks')->find($infogroupId);

            return APIController::getSuccess([
                'result' => [
                    'infogroupId' => $infogroupId,
                    'relationInfoblock' => $relationInfoblock,
                    'infoblocks' =>  $infogroup->infoblocks
                ]
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['infogroupId' => $infogroupId]);
        }
    }
}
