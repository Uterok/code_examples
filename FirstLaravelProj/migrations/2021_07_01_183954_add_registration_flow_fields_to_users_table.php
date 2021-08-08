<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationFlowFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( Schema::hasTable('users') ) {
            if ( !Schema::hasColumn('users', 'registration_flow') ) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('registration_flow')->nullable()->after('remember_token');
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
        if ( Schema::hasTable('users') ) {
            if ( Schema::hasColumn('users', 'registration_flow') ) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('registration_flow');
                });
            }
        }
    }
}
