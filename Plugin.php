<?php namespace Inerba\PostExtras;

use Backend;
use System\Classes\PluginBase;
use Event;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use RainLab\Blog\Models\Post as PostModel;
use RainLab\Blog\Models\Category as PostCategory;

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
            'description' => 'No description provided yet...',
            'author'      => 'Inerba',
            'icon'        => 'icon-leaf'
        ];
    }

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
            $model->jsonable(["extend"]);

            $model->attachOne = [
                'cover_image' => 'System\Models\File',
                'og_image' => 'System\Models\File'
            ];

           //$model->hasOne = [ 'author' => ['RainLab\User\Models\User','key'=>'id', 'otherkey'=>'author']];
           $model->belongsTo = [ 'author' => 'RainLab\User\Models\User' ];

            /*$model->addDynamicMethod('listStatuses', function() {
                return \RainLab\User\Models\User::all()->lists('surname', 'id');
            });*/


            $model->bindEvent('model.afterSave', function() use ($model) {

                $input = input('Post');

                if($input['extend']['embed_cover'] == 0 && isset($model->extend['embed']['thumbnailUrl'])){
                    $remote_file = strtok($model->extend['embed']['thumbnailUrl'],'?');
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

            $model->attachOne = [
                'cover_image' => 'System\Models\File',
            ];

        });

        /*
         * Aggiunge un campo "media" ai post del blog
         */
        Event::listen('backend.form.extendFields', function ($widget) {
            if( PluginManager::instance()->hasPlugin('RainLab.Blog') && $widget->model instanceof \RainLab\Blog\Models\Post)
            {

               // $widget->model->jsonable(['media']);

                $widget->addFields([
                    /*'extend[author]' => [
                        'label'   => 'Autore',
                        'type' => 'dropdown',
                        'options' => 'listStatuses',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage'
                    ],*/
                    'author' => [
                        'label'   => 'Autore',
                        'type'    => 'relation',
                        'select'  => 'concat(name, " ", surname)',
                        'emptyOption' => 'Nessun autore',
                        'tab'     => 'rainlab.blog::lang.post.tab_manage'
                    ],
                    'extend[embed]' => [
                        'label'   => 'Media embedder',
                        'type'    => 'extractmedia',
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
                    // SEO
                    'extend[seo][meta_title]' => [
                        'label'   => 'Title',
                        'type'    => 'text',
                        'tab'     => 'SEO e Social',
                    ],
                    'extend[seo][meta_description]' => [
                        'label'   => 'Description',
                        'type'    => 'textarea',
                        'size'    => 'tiny',
                        'tab'     => 'SEO e Social',
                    ],
                    'extend[seo][meta_robots]' => [
                        'label'   => 'Robots, direttive per i motori di ricerca',
                        'type'    => 'dropdown',
                        'options' => [
                            'index,follow' => 'Indicizza e segui i link diretti (index,follow)',
                            'noindex,follow' => 'Non indicizzare ma segui i link diretti (noindex,follow)',
                            'index,nofollow' => 'Indicizza ma non seguire i link diretti (index,nofollow)',
                            'noindex,nofollow' => 'Non indicizzare e non seguire i link diretti (noindex,nofollow)'
                        ],
                        'tab'     => 'SEO e Social',
                    ],
                    'og_image' => [
                        'label'   => 'Immagine per i social network',
                        'type'    => 'fileupload',
                        'mode'    => 'image',
                        'tab'     => 'SEO e Social',
                    ],
                    'extend[seo][twitter_creator]' => [
                        'label'   => 'Username twitter dell\'autore',
                        'type'    => 'text',
                        'tab'     => 'SEO e Social',
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

               // $widget->model->jsonable(['media']);

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
            
            $widget->addFields(
                [
                    'settings[meta_robots]' => [
                        'label'   => 'Robots, direttive per i motori di ricerca',
                        'type'    => 'dropdown',
                        'options' => [
                            'index,follow' => 'Indicizza e segui i link diretti (index,follow)',
                            'noindex,follow' => 'Non indicizzare ma segui i link diretti (noindex,follow)',
                            'index,nofollow' => 'Indicizza ma non seguire i link diretti (index,nofollow)',
                            'noindex,nofollow' => 'Non indicizzare e non seguire i link diretti (noindex,nofollow)'
                        ],
                        'tab'     => 'cms::lang.editor.meta',
                    ],
                    'settings[og_image_default]' => [
                        'label'   => 'Immagine per i social network',
                        'type'    => 'mediafinder',
                        'mode'    => 'image',
                        'tab'     => 'cms::lang.editor.meta',
                    ],
                    'settings[twitter_creator]' => [
                        'label'   => 'Username twitter dell\'autore',
                        'type'    => 'text',
                        'tab'     => 'cms::lang.editor.meta',
                    ],
                ],
                'primary'
            );
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

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'inerba.postextras.some_permission' => [
                'tab' => 'PostExtras',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'postextras' => [
                'label'       => 'PostExtras',
                'url'         => Backend::url('inerba/postextras/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['inerba.postextras.*'],
                'order'       => 500,
            ],
        ];
    }

    /**
     * Registers any form widgets implemented in this plugin.
     */
    public function registerFormWidgets()
    {
        return [
            'Inerba\PostExtras\FormWidgets\ExtractMedia' => [
                'label' => 'Extract Media',
                'code' => 'extractmedia'
            ],
        ];
    }
}
