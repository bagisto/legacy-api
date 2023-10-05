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
        if (! Schema::hasTable('push_notification')) {
            Schema::create('push_notification', function (Blueprint $table) {
                $table->integer('id');
                $table->string('type');
                $table->string('image')->nullable();
                $table->string('product_category_id')->nullable();
                $table->boolean('status')->default(0);
            });
        }

        Schema::table('push_notification', function (Blueprint $table) {
            $table->string('type');
            $table->string('image')->nullable();
            $table->string('product_category_id')->nullable();
            $table->boolean('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_notifications');
    }
};
