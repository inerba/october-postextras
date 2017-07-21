<?php namespace Inerba\PostExtras\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use System\Classes\PluginManager;

class AddBioToUsers extends Migration
{
    public function up()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.User'))
        {
            Schema::table('users', function($table)
            {
                $table->text('pe_bio')->nullable();
            });
        }
    }

    public function down()
    {
        if(PluginManager::instance()->hasPlugin('RainLab.User'))
        {
            Schema::table('users', function($table)
            {
                $table->dropColumn('pe_bio');
            });
        }
    }
}