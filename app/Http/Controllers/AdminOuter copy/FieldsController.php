<?php

namespace App\Http\Controllers\AdminOuter;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Http\Resources\PortalOuterResource;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\BtxCompany;
use App\Models\BtxDeal;
use App\Models\BtxLead;
use App\Models\BtxRpa;
use App\Models\Portal;
use App\Models\Smart;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FieldsController extends Controller
{

    static function setFields(
        Request $request


    ) {



        $btxSmart = null;
        $portalSmart = null;
        $responseData = null;
        try {
            $data = $request->all();
            $fields = $data['fields'];
            $entityType =  $data['entity_type']; //rpa company
            $domain =  $data['domain'];
            $isRewrite = $data['is_rewrite'];
            $smartId = $data['smart_id'];



            $portal = Portal::where('domain', $domain)->first();
            $group = 'sales';

            $portalDeal = $portal->deals->first();
            $portalLead = $portal->lead();
            $portalCompany = $portal->companies->first();
            $portalRPAs = $portal->rpas->all();
            // $portalsmart = $portal->smarts->where('bitrixId', $smartId)->first();
            Log::channel('telegram')->error("currentPortalField", [
                'portalRPAs' => $portalRPAs,


            ]);

            $portalDealFields = [];
            if ((!empty($portalDeal))) {
                // $portalDealFields = $portalDeal->bitrixfields;
                // if (!empty($isRewrite)) {
                //     $portalDeal->bitrixfields()->delete();
                //     $portalDeal->save();


                //     $portal = Portal::where('domain', $domain)->first();
                //     $portalDeal = $portal->deals->first();
                //     $portalDealFields = $portalDeal->bitrixfields;
                // }
            }

            if (!empty($portalLead)) {
                $portalLeadFields = $portalLead->fields;
            }
            // if (!empty($portalCompany)) {
            //     $portalCompanyFields = $portalCompany->fields;





            // }
            // if (!empty($portalsmart)) {
            //     $portalsmart = $portalsmart; //существующие fields в DB привязанные к данному смарт
            //     // Log::channel('telegram')->info("smart", [
            //     //     'portalportalsmartsFields' => $portalsmart,

            //     // ]);
            // }







            $portalEntityFields = null;
            if ($entityType === 'deal') {

                $portalDeal = $portal->deals->first();
                // if (!empty($isRewrite)) {
                //     $portalDeal->bitrixfields()->delete();
                //     $portalDeal->save();


                // }

                $portal = Portal::where('domain', $domain)->first();

                $portalDealFields = $portalDeal->bitrixfields;


                $parentClass = BtxDeal::class;
                $parentId = $portalDeal['id'];
                $portalEntityFields =  $portalDealFields;
            } else   if ($entityType === 'company') {
                $portal = Portal::where('domain', $domain)->first();
                $portalCompanyFields = $portalCompany->bitrixfields;
                // $portalEntityFields =  $portalCompanyFields;
                Log::channel('telegram')->info("smart", [
                    'portalCompany' => $portalCompany,
                ]);

                Log::channel('telegram')->info("smart", [
                    'portalCompanyFields' => $portalCompanyFields,
                ]);



                // if (!empty($isRewrite)) {
                //     $portalCompany->bitrixfields()->delete();
                //     $portalCompany->save();



                // }




                $parentClass = BtxCompany::class;
                $parentId = $portalCompany['id'];
                $portalEntityFields =  $portalCompanyFields;
            } else   if ($entityType === 'rpa') {


                $portal = Portal::where('domain', $domain)->first();
                $rpaCode = '';
                if(!empty($data['rpa_code'])){
                    $rpaCode = $data['rpa_code'];
                }
                $currentPRPA =  $portal->rpas->where('code', $rpaCode)->first();
                $portalRPAFields = $currentPRPA->bitrixfields;
                // $portalEntityFields =  $portalCompanyFields;
                Log::channel('telegram')->info("rpa", [
                    'currentPRPA' => $currentPRPA,
                ]);

                Log::channel('telegram')->info("rpa", [
                    'portalRPAFields' => $portalRPAFields,
                ]);



                // if (!empty($isRewrite)) {
                //     $portalCompany->bitrixfields()->delete();
                //     $portalCompany->save();



                // }




                $parentClass = BtxRpa::class;
                $parentId = $currentPRPA['id'];
                $portalEntityFields =  $portalRPAFields;
            }

            // else   if ($entityType === 'lead') {
            //     $parentClass = BtxLead::class;
            //     $parentId = $portalLead['id'];
            //     $portalEntityFields =  $portalLeadFields;
            // } else   if ($entityType === 'smart') {
            //     $parentClass = Smart::class;
            //     if (!empty($portalSmart)) {
            //         $portalEntityFields = $portalSmart->fields;
            //     }
            // }

            if ($entityType !== 'smart') {


                $responseData = FieldsController::createFieldsForEntities(
                    $entityType,
                    $fields,
                    $portalEntityFields,
                    $parentClass,
                    $parentId

                );
            } else {


                if (!empty($portalSmart)) {
                    // $responseData = FieldsController::createFieldsForSmartProcesses(
                    //     $hook,
                    //     $fields,
                    //     $group,
                    //     $portalSmart,
                    //     $parentClass,
                    //     $btxSmart,

                    // );
                }
            }

            $portal = Portal::where('domain', $domain)->first();
            $resultPortal = new PortalOuterResource($portal, $domain);
            return APIController::getSuccess(['updated_portal' => $resultPortal]);

            // };
        } catch (\Exception $e) {
            Log::error('Error in installSmart', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return APIController::getError('An error occurred during installation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }


        // VALUE - значение элемента списка. Поле является обязательным в случае, когда создается новый элемент.
        // SORT - сортировка.
        // DEF - если равно Y, то элемент списка является значением по-умолчанию. Для множественного поля допустимо несколько DEF=Y. Для не множественного, дефолтным будет считаться первое.
        // XML_ID - внешний код значения. Параметр учитывается только при обновлении уже существующих значений элемента списка.
        // ID - идентификатор значения. Если он указан, то считается что это обновление существующего значения элемента списка, а не создание нового. Имеет смысл только при вызове методов *.userfield.update.
        // DEL - если равно Y, то существующий элемент списка будет удален. Применяется, если заполнен параметр ID.
    }


    static function setSmartFields(
        $domain,
        $portalSmart,
        $fields


    ) {



        // $btxSmart = null;
        // $portalSmart = null;
        // $responseData = null;
        try {
            // $data = $request->all();
            // $fields = $data['fields'];
            // $entityType =  $data['entity_type']; //rpa company
            // $domain =  $data['domain'];
            // $isRewrite = $data['is_rewrite'];
            // $smartId = $data['smart_id'];



            $portal = Portal::where('domain', $domain)->first();
            $group = 'sales';

            $parentClass = Smart::class;
            $portalFields =  $portalSmart->fields->all();





            $portalEntityFields = null;
       


                if (!empty($portalSmart)) {
                    $responseData = FieldsController::createFieldsForEntities(
                        'smart',

                        $fields,
                     
                        $portalFields,
                        $parentClass,
                        $portalSmart->id

                    );
                }
            

            $portal = Portal::where('domain', $domain)->first();
            $resultPortal = new PortalOuterResource($portal, $domain);
            return APIController::getSuccess(['updated_portal' => $resultPortal]);

            // };
        } catch (\Exception $e) {
            Log::error('Error in installSmart', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return APIController::getError('An error occurred during installation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }


        // VALUE - значение элемента списка. Поле является обязательным в случае, когда создается новый элемент.
        // SORT - сортировка.
        // DEF - если равно Y, то элемент списка является значением по-умолчанию. Для множественного поля допустимо несколько DEF=Y. Для не множественного, дефолтным будет считаться первое.
        // XML_ID - внешний код значения. Параметр учитывается только при обновлении уже существующих значений элемента списка.
        // ID - идентификатор значения. Если он указан, то считается что это обновление существующего значения элемента списка, а не создание нового. Имеет смысл только при вызове методов *.userfield.update.
        // DEL - если равно Y, то существующий элемент списка будет удален. Применяется, если заполнен параметр ID.
    }

    public static function createFieldsForEntities(
        $entityType,
        $fields,
        $portalFields,
        $parentClass,
        $parentId,
    ) {



        $result_fields = [];
        $isRewrite = false;
        foreach ($fields as   $field) {
            // $currentBtxField = false;
            // $currentBtxFieldId = false;
            $currentPortalField = false;

            if ($field[$entityType]) {




                if (!empty($portalFields)) {

                    foreach ($portalFields as $portalField) {

                        if ($field['code'] === $portalField['code']) {
                            $currentPortalField = $portalField;
                        }
                    }
                }


                $type = $field['type'] ?? 'string';
                $multiple = 'N';

                if ($field['type'] == 'multiple') {
                    $multiple =  "Y";
                    $type = 'string';
                }


                if (empty($currentPortalField)) {
                    $appTypeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['appType']);


                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_type = $parentClass;
                    $currentPortalField->entity_id = $parentId;
                    $currentPortalField->parent_type = $appTypeBitrixId;
                }
                $type = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['type']);
                $name = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['name']);
                $code = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['code']);
                $fieldBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field[$entityType]);


                $currentPortalField->type = $type;
                $currentPortalField->title = $name;
                $currentPortalField->name = $name;
                $currentPortalField->code = $code;

                $currentPortalField->bitrixId = $fieldBitrixId;
                $currentPortalField->bitrixCamelId = 'ufCrm' . $fieldBitrixId;


                $currentPortalField->save();
                $field['type'] = preg_replace('/[\x00-\x1F\x7F]/', '', $field['type']);

                if ($field['type'] == 'enumeration') {
                    Log::channel('telegram')->info("enumeration", [
                        'field' => $field,

                    ]);
                    FieldsController::setFieldItems($field, $currentPortalField, $isRewrite);
                }
                array_push($result_fields, $currentPortalField);
            }

            // sleep(2);
        }
        return $result_fields;
    }

    public static function setFieldItems(
        // $currentBtxField,    //from btx
        $field,  //from google
        $currentPortalField, //from db
        $isRewrite = false
    ) {

        if (!empty($field['is_rewrite'])) {
            $isRewrite = $field['is_rewrite'];
        }

        $fieldItems =  $field['items'];
        if (!empty($currentPortalField)) {


            //DELETE ALL FIELD ITEMS IN DB
            if (!empty($isRewrite)) {
                $currentPortalField->items()->delete();
            }



            if (isset($currentPortalField->items)) {
                $portalFieldItems = $currentPortalField->items->toArray();
            }
        }


        $currentGooItem  = false;
        if (!empty($fieldItems)) {
            foreach ($fieldItems as $fieldItem) {  //btx items

                $fieldItemCode = preg_replace('/[\x00-\x1F\x7F]/', '', $fieldItem['code']);
                $fieldItemValue = preg_replace('/[\x00-\x1F\x7F]/', '', $fieldItem['value']);
                $currentPortalItem  = null;

                //ищем item в db
                if (!empty($portalFieldItems)) {
                    foreach ($portalFieldItems as $pitem) {

                        if ($fieldItemCode == $pitem['code']) {

                            if (!empty($pitem['id'])) {
                                $currentPortalItem  = BitrixfieldItem::find($pitem['id']);
                            } else {
                                $currentPortalItem  = $pitem;
                            }
                        }
                    }
                }
                Log::channel('telegram')->info('INSTALL', ['currentPortalItem' => $currentPortalItem]);


                if (empty($currentPortalItem)) {
                    $currentPortalItem  =  new BitrixfieldItem();
                    $currentPortalItem->bitrixfield_id = $currentPortalField['id'];

                    $currentPortalItem->code = $fieldItemCode;
                }

                $currentPortalItem->bitrixId = $fieldItem['bitrixId'];

                $currentPortalItem->name = $fieldItemValue;
                $currentPortalItem->title = $fieldItemValue;
                $currentPortalItem->save();
                $currentPortalField->save();
            }
        }
    }
}
