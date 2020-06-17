<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingSetupRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_setup_requirements', function (Blueprint $table) {
            $table->integer('building_setup_entry_id')->unsigned();
            $table->string('type');
            $table->dateTime('due_date')->nullable()->default(null);
            $table->enum('status', ['met', 'not-met', 'not-applicable'])->default('not-met');
            $table->timestamps();
            $table->primary(['building_setup_entry_id', 'type']);
            $table->foreign('building_setup_entry_id')
                ->references('property_id')
                ->on('building_setup_entries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_setup_requirements', function (Blueprint $table) {
            $table->dropForeign('building_setup_requirements_building_setup_entry_id_foreign');
        });

        Schema::dropIfExists('building_setup_requirements');
    }
}
