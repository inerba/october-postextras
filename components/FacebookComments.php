<?php namespace Inerba\PostExtras\Components;

use Cms\Classes\ComponentBase;

class FacebookComments extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Facebook Comments',
            'description' => 'Mostra i commenti di facebook'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
}
