<?php namespace Inerba\PostExtras;

use Backend;
use System\Classes\PluginBase;
use Event;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Inerba\Embedd\Classes\Embedd;
use RainLab\Blog\Models\Post as PostModel;
use RainLab\Blog\Models\Category as PostCategory;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\User as UserModel;

/**
 * PostExtras Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'PostExtras',
            'description' => 'Post & Cms extra fields',
            'author'      => 'Inerba',
            'icon'        => 'icon-leaf'
        ];
    }

    public $require = ['RainLab.Blog','RainLab.Pages','RainLab.User'];

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        PostModel::extend(function($model){
            $model->jsonable(array_merge($model->getJsonable(), ["extend"]));
            $model->attachOne = array_merge( $model->attachOne, ['cover_image' => 'System\Models\File'] );
            $model->belongsTo['author'] = [ 'RainLab\User\Models\User' ];

            // Imposta il post come "pubblicato" di default con la data e ora del momento in cui si apre
            $model->attributes = [
                'published' => true,
                'published_at' => \Carbon\Carbon::now()
            ];

            $model->bindEvent('model.afterSave', function() use ($model) {

                $input = input('Post');
                // Copia l'immagine dell'elemento incorporato per farne una copertina manipolabile
                if($input['extend']['embed_cover'] == 0 && isset($model->extend['embed']['image'])){
                    $remote_file = strtok($model->extend['embed']['image'],'?');
                    $temp_file = storage_path('temp/') . uniqid(rand(), true) . '.' . pathinfo($remote_file, PATHINFO_EXTENSION);
                    $file = copy($remote_file, $temp_file);

                    $file = new \System\Models\File;
                    $file->data = $temp_file;
                    $file->is_public = true;
                    $file->save();

                    $model->cover_image()->add($file);
                    //dd($model);
                }
            });
        });

        PostCategory::extend(function($model){
            $model->attachOne = array_merge( $model->attachOne, ['cover_image' => 'System\Models\File'] );
        });

        UserModel::extend(function($model)
        {
            $model->addFillable([
                'pe_bio',
            ]);
        });

        UsersController::extendFormFields(function($form, $model, $context)
        {
            if (!$model instanceof UserModel) {
                return;
            }
            $form->addTabFields([
                'pe_bio' => [
                    'label' => 'Bio',
                    'tab'   => 'Bio',
                    'type'  => 'textarea',
                    'size'  => 'small',
                    'span'  => 'full'
                ],
            ]);
        });

        /*
         * Aggiunge un campo "media" ai post del blog
         */
        Event::listen('backend.form.extendFields', function ($widget) {
            if( PluginManager::instance()->hasPlugin('RainLab.Blog') && $widget->model instanceof \RainLab\Blog\Models\Post)
            {

                $widget->addFields([
                    'extend[direct_link]' => [
                        'label'   => 'Link diretto, invia direttamente al link inserito',
                        'type'    => 'text',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage',
                        'span'    => 'full',
                    ],
                    'author' => [
                        'label'   => 'Autore',
                        'type'    => 'relation',
                        'select'  => 'concat(name, " ", surname)',
                        'emptyOption' => 'Nessun autore',
                        'span'    => 'left',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage'
                    ],
                    'extend[custom_author]' => [
                        'label'   => 'Autore personalizzato',
                        'type'    => 'text',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage',
                        'span'    => 'right',
                        'trigger' => [
                            'action' => 'show',
                            'field' => 'author',
                            'condition' => 'value[]'
                        ]
                    ],
                    'extend[embed]' => [
                        'label'   => 'Media embedder',
                        'type'    => 'embedd',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage'
                    ],
                    'extend[embed_cover]' => [
                        'label'   => 'Usa un\'immagine di copertina',
                        'type'    => 'switch',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage'
                    ],
                    'cover_image' => [
                        'label'   => 'Copertina',
                        'type'    => 'fileupload',
                        'mode'    => 'file',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage',
                        'trigger' => [
                            'action' => 'show',
                            'field' => 'extend[embed_cover]',
                            'condition' => 'checked'
                        ]
                    ],
                    'is_featured' => [
                        'label'   => 'Post in evidenza',
                        'type'    => 'switch',
                        'default' => 1,
                        'tab'     => 'rainlab.blog::lang.post.tab_categories',
                    ],
                ],
                'secondary');
            }

            if( PluginManager::instance()->hasPlugin('RainLab.Blog') && $widget->model instanceof \RainLab\Blog\Models\Category)
            {
                $widget->addFields([
                    'cover_image' => [
                        'label'   => 'Immagine di copertina',
                        'type'    => 'fileupload',
                        'mode'    => 'image',
                    ],
                ]);
            }

            if (!$widget->model instanceof \Cms\Classes\Page) return;

            if (!($theme = Theme::getEditTheme())) {
                throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
            }

        });

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
             'Inerba\PostExtras\Components\FacebookComments' => 'FacebookComments',
             'Inerba\PostExtras\Components\ShareButtons' => 'ShareButtons',
             'Inerba\PostExtras\Components\FeaturedPosts' => 'featuredPosts',
             'Inerba\PostExtras\Components\PostModule' => 'PostModule',
             'Inerba\PostExtras\Components\ExcludeCategory' => 'ExcludeCategory'
        ];
    }
}
