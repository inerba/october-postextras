<?php namespace Inerba\PostExtras\Components;

use Redirect;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category as BlogCategory;

class PostModule extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'PostModule Component',
            'description' => "Prende l'ultimo post in evidenza da una categoria scelta"
        ];
    }

    public function defineProperties()
    {
        return [
            'idCategoria'    => [
                'title'             => 'ID categoria',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Please enter only numbers',
                'default'           => '50',
            ],
            'inEvidenza' => [
                'title'       => 'Post in evidenza',
                'type'        => 'checkbox',
                'default'     => 1,
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
        
        $idCategoria = $this->property('idCategoria');
        $inEvidenza = $this->property('inEvidenza');
        $postPage = $this->property('postPage');
        
        $post_query = BlogPost::with('categories');


        if($inEvidenza){
            $post_query->where('is_featured',1);
        }


        if($idCategoria){
            $post_query->whereHas('categories', function($q) use ($idCategoria) {
                $q->where('id', $idCategoria);
            });
        }

        $post_query->orderBy('published_at', 'desc');
        
        $post = $post_query->firstOrFail();

        $post->setUrl($postPage, $this->controller); 

        //dump($post->toArray());

        $this->page['featured_post'] = $post;
    }
}