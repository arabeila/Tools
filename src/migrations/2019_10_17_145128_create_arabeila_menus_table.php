<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArabeilaMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('arabeila_menus')){
            return;
        }

        Schema::create('arabeila_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guard_name')->default('admin')->comment('菜单类型');
            $table->string('icon')->nullable()->comment('菜单图标');
            $table->string('type')->default('_self');
            $table->integer('parent_id', false, true)->nullable()->comment('父级 ID');
            $table->string('url')->default('#')->comment('地址');
            $table->string('name')->comment('菜单名称');
            $table->integer('sort', false, false)->default(0)->comment('排序');
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
        Schema::dropIfExists('arabeila_menus');
    }
}
