<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'link',
        'portalId',

    ];

    // public function providers()
    // {
    //     return $this->hasMany(TField::class);
    // }


    public static function getTemplate($templateId)
    {
        return Template::find($templateId);
    }

    public static function getTemplatePath($templateId)
    {
        $templatePath = null;

        $template = Template::find($templateId);
        if ($template) {
            $templatePath = $template->link;
        }
        return $templatePath;
    }

    public function fields()
    {
        return $this->belongsToMany(Field::class, 'template_field', 'template_id', 'field_id');
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portalId', 'id');
    }
}
