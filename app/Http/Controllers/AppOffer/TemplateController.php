<?php

namespace App\Http\Controllers\AppOffer;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\TemplateCollection;
use App\Models\Portal;
use App\Models\Template;


class TemplateController extends Controller
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
