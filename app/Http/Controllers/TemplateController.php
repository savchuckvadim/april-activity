<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
use App\Http\Resources\TemplateCollection;
use App\Models\Field;
use App\Models\Portal;
use App\Models\Template;

use Illuminate\Http\Request;
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
            '$searchingTemplateFields' =>$searchingTemplateFields,
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

    public static function initialTemplate($domain,  $type, $name)
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
        $generalFields = Field::where('isGeneral', true)->get();
        $generalFieldsCollection = new FieldCollection($generalFields);
        // $template->save();

        // $templates = Template::get();

        return response([
            'resultCode' => 0,
            'generalFields' => $generalFieldsCollection,
            // 'template' => $template,
            // 'templates' => $templates,
            'message' => ''
        ]);
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


    public static function setTemplate($domain, $fieldIds, $type, $name, $file)
    {

        $template = new Template;
        $domain = json_decode($domain, true);
        $type = json_decode($type, true);
        $name = json_decode($name, true);


        $portal = Portal::where('domain', $domain)->first();

        $template['name'] = $name;
        $template['type'] = $type;
        $uid = Uuid::uuid4()->toString();
        $template['code'] = $domain . '' . '' . $type . '' . $uid;


        if ($portal) {
            $template['portalId'] = $portal->id;
        } else {
            return response([
                'resultCode' => 1,
                'message' => 'something wrong with portal',
                '$portal' => $portal,
                '$domain' => $domain

            ]);
        }

        $template->save();

        $fieldIds = json_decode($fieldIds, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($fieldIds)) {
            $length = count($fieldIds);
            if (!$length) {

                $fieldIds = Field::where('isGeneral', true)->pluck('id');
            }
            $template->fields()->attach($fieldIds);
        } else {
            return response([
                'resultCode' => 1,
                'message' => 'something wrong with fields ids'

            ]);
        }
        $fields = $template->fields;
        $domain = $template->portal->domain;
        $type = $template->type;
        $name = $template->name;

        $uploadFile = FileController::uploadTemplate($file, $domain, $type, $name);
        $template['link'] =  $uploadFile['file'];
        $template->save();
        $templates = Template::get();




        return response([
            'resultCode' => 0,
            'fields' => $fields,
            'templates' => $templates,
            'template' => $template,
            '$template->fields();' =>  $fields,
            '$template->portal();' =>  $domain,
            '$type= $template->type;' =>  $type,
            '$name= $template->name;' =>  $name,
            '$uploadFile' => $uploadFile,
            '$portal' =>  $portal
        ]);
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
}
