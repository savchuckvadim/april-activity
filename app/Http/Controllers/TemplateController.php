<?php

namespace App\Http\Controllers;

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

        foreach ($templates as $template) {

            //PORTAL RELATION
            $portal = Portal::where('domain',  $template['portal'])->first();



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
            'templates' => $result
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
                $templates = $portal->templates();
            }
        }

        $testtemplates = Template::all();

        return response([
            'resultCode' => 0,
            'templates' => $templates,
            'testtemplates' => $testtemplates
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
        $portal = Portal::where('domain', $domain)->first();
    
        if ($portal) {
            $template = new Template;
            $template['portalId'] = $portal->id;
            $template['type'] = $type;
            $template['name'] = $name;
            $uid = Uuid::uuid4()->toString();
            $template['code'] = $domain . '' . '' . $type . '' . $uid;
            $generalFields = Field::where('isGeneral', true)->get();

            $template->save();

            $templates = Template::get();

            return response([
                'resultCode' => 0,
                'generalFields' => $generalFields,
                'template' => $template,
                'templates' => $templates,
                'message' => ''
            ]);
        }else{
            return response([
                'resultCode' => 1,
                'message' => 'portal was not found'
            ]);
        }
    }


    public static function setTemplate($templateId, $fieldIds)
    {
        $template = Template::find($templateId);
        $template = new Template;
        $template->fields()->attach($fieldIds);
        $fields = $template->fields();

        $templates = Template::get();

        return response([
            'resultCode' => 0,
            'fields' => $fields,
            'templates' => $templates,
            'template' => $template,

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
}
