<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
use App\Http\Resources\TemplateCollection;
use App\Http\Resources\TemplateResource;
use App\Models\Field;
use App\Models\Portal;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class TemplateController extends Controller
{
    public static function setTemplates($templates)
    {
        $result = [];
        $portals = [];
        $comePortals = [];
        foreach ($templates as $template) {

            //PORTAL RELATION
            $portal = Portal::where('number',  $template['portalNumber'])->first();

            array_push($portals, $portal);
            array_push($comePortals, $template['portal']);
            if ($portal) {
                $template['portalId'] = $portal->id;

                //TEMPLATE
                $searchingTemplate = Template::updateOrCreate(
                    ['code' => $template['code']], // Условие для поиска
                    $template // Данные для обновления или создания
                );



                //FIELDS RELATION
                $searchingTemplateFields = $searchingTemplate->fields();

                if ($searchingTemplateFields->count() < 1) {
                    $generalFields = Field::where('isGeneral', true)->get();

                    $fieldIds = $generalFields->pluck('id');
                    $searchingTemplate->fields()->attach($fieldIds);
                }


                //SAVE
                $searchingTemplate->save();
                $result[] = $searchingTemplate;
            }
        }




        return response([
            'resultCode' => 0,
            'come$templates' => $templates,
            '$comePortals' => $comePortals,
            'templates' => $result,
            'portals' =>  $portals,
            '$searchingTemplateFields' => $searchingTemplateFields,
            '$generalFields' => $generalFields
        ]);
    }

    public static function getTemplates($domain)
    {
        $templates = [];

        if ($domain == 'all') {

            $templates = Template::all();
        } else {

            $portal = Portal::where('domain', $domain)->first();
            if ($portal) {
                $templates = $portal->templates()->get();
            }
        }


        $templatesCollection = new TemplateCollection($templates);

        return response([
            'resultCode' => 0,
            'templates' => $templatesCollection,
            'isCollection' => true,
        ]);
    }

    public static function getAllTemplates()
    {
        try {
            $templates = [];
            $templates = Template::all();
            $templatesCollection = new TemplateCollection($templates);
            return APIController::getResponse(0, 'success', $templatesCollection);
        } catch (\Throwable $th) {
            $templates = [];
            $templates = Template::all();
            $templatesCollection = new TemplateCollection($templates);

            return APIController::getResponse(1, $th->getMessage(), 0);
        }
    }

    public static function initialTemplate()
    {


        $initialData = Template::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
    public static function getTemplate($templateId)
    {
        try {

            $template = Template::find($templateId);
            $templateResource = new TemplateResource($template);
            if ($template) {
                return APIController::getResponse(0, 'success', ['template' => $templateResource]);
            } else {
                return APIController::getResponse(1, 'Template not found', $templateResource);
            }
        } catch (\Throwable $th) {


            return APIController::getResponse(1, $th->getMessage(), null);
        }
    }
    public static function getCounters($templateId)
    {
        $counters = [];
        $template = Template::find($templateId);
        if ($template) {
            $counters = $template->counters;
            if ($counters) {
                // $collectionFields = new FieldCollection($fields);
                return APIController::getSuccess(
                    ['counters' => $counters]
                );
            }
        }
        return APIController::getError('invalid template id', ['templateId' => $templateId]);
    }

    public static function getProviders($templateId)
    {
        $counters = [];
        $template = Template::find($templateId);
        if ($template) {
            $portal = $template->portal;
            if ($portal) {
                $providers = $portal->providers;
                return APIController::getSuccess(
                    ['providers' => $providers]
                );
            }
        }
        return APIController::getError('invalid template id', ['templateId' => $templateId]);
    }
    // public static function setTemplate($domain, $fieldIds, $type, $name, $file)
    // {

    //     $template = new Template;
    //     $domain = json_decode($domain, true);
    //     $type = json_decode($type, true);
    //     $name = json_decode($name, true);


    //     $portal = Portal::where('domain', $domain)->first();

    //     $template['name'] = $name;
    //     $template['type'] = $type;
    //     $uid = Uuid::uuid4()->toString();
    //     $template['code'] = $domain . '' . '' . $type . '' . $uid;


    //     if ($portal) {
    //         $template['portalId'] = $portal->id;
    //     } else {
    //         return response([
    //             'resultCode' => 1,
    //             'message' => 'something wrong with portal',
    //             '$portal' => $portal,
    //             '$domain' => $domain

    //         ]);
    //     }

    //     $template->save();

    //     $fieldIds = json_decode($fieldIds, true);

    //     if (json_last_error() === JSON_ERROR_NONE && is_array($fieldIds)) {
    //         $length = count($fieldIds);
    //         if (!$length) {

    //             $fieldIds = Field::where('isGeneral', true)->pluck('id');
    //         }
    //         $template->fields()->attach($fieldIds);
    //     } else {
    //         return response([
    //             'resultCode' => 1,
    //             'message' => 'something wrong with fields ids'

    //         ]);
    //     }
    //     $fields = $template->fields;
    //     $domain = $template->portal->domain;
    //     $type = $template->type;
    //     $name = $template->name;

    //     $uploadFile = FileController::uploadTemplate($file, $domain, $type, $name);
    //     $template['link'] =  $uploadFile['file'];
    //     $template->save();
    //     $templates = Template::get();




    //     return response([
    //         'resultCode' => 0,
    //         'fields' => $fields,
    //         'templates' => $templates,
    //         'template' => $template,
    //         '$template->fields();' =>  $fields,
    //         '$template->portal();' =>  $domain,
    //         '$type= $template->type;' =>  $type,
    //         '$name= $template->name;' =>  $name,
    //         '$uploadFile' => $uploadFile,
    //         '$portal' =>  $portal
    //     ]);
    // }
    public function setTemplate($domain, $type, $name, $relations)
    {
        //name
        //type inovoice | offer | contract | report
        // relations -> field as Array field->formData
        //domain
        // Создаем или находим шаблон
        try {
            $template = new Template();
            $template->name = $name;
            $template->type = $type;

            $template->code = Str::uuid()->toString();
            // Находим портал по домену и связываем
            $portal = Portal::where('domain', $domain)->first();
            if ($portal) {
                $template->portal()->associate($portal);
            }

            $template->save();

            // Обработка связанных полей
            if (isset($relations['field']) && is_array($relations['field']) && count($relations['field']) > 0) {
                $this->processFields($relations['field'], $template);
            }


            // Дополнительная логика...
            $responseData = [
                'template' => $template, 'relations' => $relations
            ];
            return APIController::getResponse(0, 'Шаблон успешно создан', $responseData);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return APIController::getResponse(1, 'something wrong with save template: ' . $message, null);
        }

        // $template = new Template;
        // $domain = json_decode($domain, true);
        // $type = json_decode($type, true);
        // $name = json_decode($name, true);
        // foreach ($relations['field'] as $index => $data) {
        //     if (isset($data['img']) && $data['img'] instanceof UploadedFile) {
        //         // Сохраняем файл
        //         $filePath = $data['img']->store('public/template/images/test/' . $domain . '/' . $type . '/' . $name);
        //         $fileUrl = Storage::url($filePath);

        //         // Обновляем значение img до URL
        //         $data['img'] = $fileUrl;
        //     }

        //     // Здесь ваша логика для создания/обновления модели с данными $data
        // }

        //PORTAL
        // $portal = Portal::where('domain', $domain)->first();

        // $template['name'] = $name;
        // $template['type'] = $type;
        // $uid = Uuid::uuid4()->toString();
        // $template['code'] = $domain . '' . '' . $type . '' . $uid;


        // if ($portal) {
        //     $template['portalId'] = $portal->id;
        // } else {
        //     return response([
        //         'resultCode' => 1,
        //         'message' => 'something wrong with portal',
        //         '$portal' => $portal,
        //         '$domain' => $domain

        //     ]);
        // }

        // $template->save();



        // if (json_last_error() === JSON_ERROR_NONE && is_array($fieldIds)) {
        //     $length = count($fieldIds);
        //     if (!$length) {

        //         $fieldIds = Field::where('isGeneral', true)->pluck('id');
        //     }
        //     $template->fields()->attach($fieldIds);
        // } else {
        //     return response([
        //         'resultCode' => 1,
        //         'message' => 'something wrong with fields ids'

        //     ]);
        // }
        // $fields = $template->fields;
        // $domain = $template->portal->domain;
        // $type = $template->type;
        // $name = $template->name;

        // $uploadFile = FileController::uploadTemplate($file, $domain, $type, $name);
        // $template['link'] =  $uploadFile['file'];
        // $template->save();
        // $templates = Template::get();




        // return response([
        //     'resultCode' => 0,
        //     'type' => $type,
        //     'name' => $name,
        //     'relations' => $relations,
        //     'domain' => $domain,
        //     'portal' => $portal,
        //     // 'fields' => $fields,
        //     // 'templates' => $templates,
        //     // 'template' => $template,
        //     // '$template->fields();' =>  $fields,
        //     // '$template->portal();' =>  $domain,
        //     // '$type= $template->type;' =>  $type,
        //     // '$name= $template->name;' =>  $name,
        //     // '$uploadFile' => $uploadFile,
        //     // '$portal' =>  $portal
        // ]);
    }
    public function updateTemplate($templateId, $templateData)
    {
        //name
        //type inovoice | offer | contract | report
        //code
        //link
        try {
            $template = Template::find($templateId);
            $template->name = $templateData['name'] ?? $template->name;
            $template->type = $templateData['type'] ?? $template->type;
            $template->code = $templateData['code'] ?? $template->code;
            $template->link = $templateData['link'];
            $template->save();



            $responseData = [
                'template' => $template,

            ];
            return APIController::getSuccess($responseData);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return APIController::getResponse(1, 'something wrong with save template: ' . $message, null);
        }
    }

    public static function deleteTemplate($templateId)
    {
        $template = Template::find($templateId);

        if ($template) {
            // Получаем все связанные поля
            $fields = $template->fields()->get();

            foreach ($fields as $field) {
                // Если тип поля - img и в value хранится путь к файлу
                if ($field->type === 'img' && !empty($field->value)) {
                    // Удаляем файл
                    $file = public_path($field->value);
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                // Удаление поля
                $field->delete();
            }

            // Удаляем все связи с полями
            $template->fields()->detach();

            // Удалить сам шаблон
            $template->delete();
            return response([
                'resultCode' => 0,
                // 'templates' => $templates,
                'message' => 'Template and its relations have been deleted successfully',
            ]);
            // Дополнительный код для ответа...
        } else {
            // Код для случая, когда шаблон не найден...
            return response([
                'resultCode' => 1,
                'message' => 'Template not found',
            ]);
        }
    }




    //APRIL_KP
    public static function getClientTemplate($code)
    {
        $templates = [];


        $template = Template::where('code', $code)->first();
        $templatesResource = new TemplateCollection($template);

        return response([
            'resultCode' => 0,
            'templates' => $templatesResource,
            'isCollection' => true,
        ]);
    }
}
