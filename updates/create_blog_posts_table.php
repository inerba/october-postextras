<?php namespace Inerba\PostExtras\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;
use System\Classes\PluginManager;
class CreateBlogPostsTable extends Migration
{
    public function up()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->text('extend')->nullable();
                $table->boolean('is_featured')->default(true);
                $table->integer('author_id')->unsigned()->nullable();
            });
        }
    }
    public function down()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->dropColumn('extend');
                $table->dropColumn('is_featured');
                $table->dropColumn('author_id');
            });
        }
    }
}