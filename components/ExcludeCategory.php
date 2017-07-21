<?php namespace Inerba\PostExtras\Components;

use Redirect;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category as BlogCategory;

class ExcludeCategory extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Lista categorie escluse',
            'description' => "Lista di post categorie escluse"
        ];
    }

    public function defineProperties()
    {
        return [
            'categoriesId'    => [
                'title'             => 'ID categorie da includere o escludere',
                'type'              => 'string',
                //'validationPattern' => '^[0-9]+$',
                //'validationMessage' => 'Please enter only numbers',
               // 'default'           => null,
            ],
            'excludeCategories'    => [
                'title'       => 'Escludi le categorie indicate',
                'type'        => 'checkbox',
                'default'     => 1,
               // 'default'           => null,
            ],
            'inEvidenza' => [
                'title'       => 'Post in evidenza',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
            'max' => [
                'title'       => 'massimo',
                'type'        => 'string',
                'default'     => 5,
            ],
            'exceptPost' => [
                'title'             => 'rainlab.blog::lang.settings.posts_except_post',
                'description'       => 'rainlab.blog::lang.settings.posts_except_post_description',
                'group'             => 'Exceptions',
            ],
            'categoryPage' => [
                'title'       => 'rainlab.blog::lang.settings.posts_category',
                'description' => 'rainlab.blog::lang.settings.posts_category_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'Links',
            ],
            'postPage' => [
                'title'       => 'rainlab.blog::lang.settings.posts_post',
                'description' => 'rainlab.blog::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'Links',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        
        $categories_id = $this->property('categoriesId');
        $exclude = $this->property('excludeCategories');
        $exclude_post = $this->property('exceptPost');

        $inEvidenza = $this->property('inEvidenza');
        
        $post_query = BlogPost::with('categories')->where('published',1);

        //dd($this->properties);

        if($inEvidenza == 1){
            $post_query->where('is_featured',1);
        }

        if($categories_id){

            $categories_array = explode(',', $categories_id);

            if($exclude){
                $post_query->whereHas('categories', function($q) use ($categories_array) {
                    $q->whereNotIn('id', $categories_array);
                })->doesntHave('categories', 'or');
            } else {
                $post_query->whereHas('categories', function($q) use ($categories_array) {
                    $q->whereIn('id', $categories_array);
                });
            }
        }

        $post_query->orderBy('published_at', 'desc');
        
        $posts = $post_query->paginate($this->property('max'));

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $posts->each(function($post_query) {
            $post_query->setUrl($this->property('postPage'), $this->controller);

            $post_query->categories->each(function($category) {
                $category->setUrl($this->property('categoryPage'), $this->controller);
            });
        });

        $this->page['posts'] = $posts;
    }
}