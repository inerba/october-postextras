<?php namespace Inerba\PostExtras\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;
use System\Classes\PluginManager;
class RenameBlogPostsFieldExtras extends Migration
{
    public function up()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->renameColumn('extend','postextras');
            });
        }
    }
    public function down()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->renameColumn('postextras','extend');
            });
        }
    }
}