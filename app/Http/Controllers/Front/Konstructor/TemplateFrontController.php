<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\FieldCollection;
use App\Http\Resources\TemplateCollection;
use App\Http\Resources\TemplateResource;
use App\Models\Field;
use App\Models\Portal;
use App\Models\Template;

use Illuminate\Support\Str;


class TemplateFrontController extends Controller
{

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

        return  APIController::getSuccess(
            $templatesCollection
        );
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



}
