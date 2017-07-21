<?php namespace Inerba\PostExtras\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use System\Classes\PluginManager;

class Migration102 extends Migration
{
    public function up()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->boolean('is_featured')->default(true);
            });
        }
    }
    public function down()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            Schema::table('rainlab_blog_posts', function($table)
            {
                $table->dropColumn('is_featured');
            });
        }
    }
}