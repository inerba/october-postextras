<?php namespace Inerba\PostExtras\Components;

use Cms\Classes\ComponentBase;

class ShareButtons extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Share Buttons',
            'description' => 'Pulsanti di condivisione'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addJs('/plugins/inerba/postextras/assets/js/rrssb.min.js');
        $this->addCss('/plugins/inerba/postextras/assets/css/rrssb.css');
    }
}
