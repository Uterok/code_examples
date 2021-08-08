<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveFieldToWikisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( Schema::hasTable('wikis') ) {
            if ( !Schema::hasColumn('wikis', 'active') ) {
                Schema::table('wikis', function (Blueprint $table) {
                    $table->boolean('active')->default(true)->after('completed');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ( Schema::hasTable('wikis') ) {
            if ( Schema::hasColumn('wikis', 'active') ) {
                Schema::table('wikis', function (Blueprint $table) {
                    $table->dropColumn('active');
                });
            }
        }
    }
}
