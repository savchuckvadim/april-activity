<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
use App\Http\Resources\TemplateCollection;
use App\Models\Field;
use App\Models\Portal;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

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


    public static function initialTemplate($domain)
    {

        // создает новый template 
        // берет fields isgeneral
        // в итоге возвращает филды и id нового template

        // если по итогу пользователь save то еще создаются связи с созданным template 
        // и c выбранными (или еще и созданными) филдс
        //
        // $portal = Portal::where('domain', $domain)->first();

        // if ($portal) {
        // $template = new Template;
        // $template['portalId'] = $portal->id;
        // $template['type'] = $type;
        // $template['name'] = $name;
        // $uid = Uuid::uuid4()->toString();
        // $template['code'] = $domain . '' . '' . $type . '' . $uid;
        // $generalFields = Field::where('isGeneral', true)->get();
        // $generalFieldsCollection = new FieldCollection($generalFields);
        // $template->save();

        // $templates = Template::get();
        $initialData = Template::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getResponse(0, 'success', $data);
        // } else {
        //     return response([
        //         'resultCode' => 1,
        //         'message' => 'portal was not found',
        //         '$portal' => $portal,
        //         '$domain' => $domain,
        //         '$type' => $type,
        //         '$name' => $name

        //     ]);
        // }
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
        $template = new Template();
        $template->name = $name;
        $template->type = $type;

        // Находим портал по домену и связываем
        $portal = Portal::where('domain', $domain)->first();
        if ($portal) {
            $template->portal()->associate($portal);
        }

        $template->save();

        // Обработка связанных полей
        $this->processFields($relations['field'], $template);

        // Дополнительная логика...

        return response()->json(['message' => 'Шаблон успешно создан', 'template' => $template]);
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


    public static function deleteTemplate($templateId)
    {
        // Найти шаблон по ID
        $template = Template::find($templateId);

        if ($template) {
            // Удалить все связи с полями
            $template->fields()->detach();

            // Удалить сам шаблон
            $template->delete();

            $templates = Template::get();
            return response([
                'resultCode' => 0,
                'templates' => $templates,
                'message' => 'Template and its relations have been deleted successfully',
            ]);
        } else {
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


    //UTILS
    protected function processFields(array $fields, Template $template)
    {
        foreach ($fields as $fieldData) {
            $field = $this->createOrUpdateField($fieldData);

            // Связываем поле с шаблоном
            $template->fields()->attach($field->id);
        }
    }

    protected function createOrUpdateField(array $fieldData)
    {
        $field = new Field();

        // Заполнение полей модели Field данными
        // Замените это на соответствующий код заполнения
        foreach ($fieldData as $key => $value) {
            if ($key !== 'img') {
                $field->$key = $value;
            }
        }

        // Если это поле с изображением, сохраняем файл и устанавливаем initialValue
        if (isset($fieldData['img']) && $fieldData['img'] instanceof UploadedFile) {
            $filePath = $fieldData['img']->store('public/images');
            $field->initialValue = Storage::url($filePath);
        }

        $field->save();

        return $field;
    }
}
