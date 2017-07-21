<?php namespace Inerba\PostExtras\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Essence\Essence as Essence;
use Cms\Classes\Controller;

/**
 * ExtractMedia Form Widget
 */
class ExtractMedia extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'inerba_postextras_extract_media';

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('extractmedia');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['form'] = $this;

    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/extractmedia.css', 'Inerba.PostExtras');
        $this->addJs('js/extractmedia.js', 'Inerba.PostExtras');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        $media_url = $value;

        if( !empty($media_url) ) {

            $Essence = new Essence();
            $media = $Essence->extract($media_url);

            //dd($model->attributes);

            if($media) {
                $value = $media->properties();
            }
        }

        return $value;
    }

    public function onExtractMedia(){
        /*dump($this);
        exit;*/
        $formFieldValue = post($this->formField->getName());
        $media = $this->ExtractMedia($formFieldValue);

        if(isset($media['html'])){
            return $this->vars['media'] = $media['html'];
        }
        return $this->vars['media'] = false;
    }

    public function ExtractMedia($value){
        $media_url = $value;

        if( !empty($media_url) ) {

            $Essence = new Essence();
            $options = [
                //'maxwidth' => '1980',
                //'maxheight' => '768'
            ];
            $media = $Essence->extract($media_url,$options);

            //dd($model->attributes);

            if($media) {
                //$this->attrib['media'] = $media->properties();
                $value = $media->properties();
            }
        }

        return $value;
    }
}
