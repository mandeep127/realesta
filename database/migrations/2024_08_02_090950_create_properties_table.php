<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string("user_id")->nullable();
            $table->string("property_type_id")->nullable();
            $table->string("price");
            $table->string("bedrooms");
            $table->string("bathrooms");
            $table->string("size");
            $table->string("image");
            $table->text('description');
            $table->string("address");
            $table->string("city");
            $table->string("state");
            $table->string("pincode");
            $table->string("country");
            $table->string("created_by")->nullable();
            $table->string('status')->default(1);
            $table->string('type')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
