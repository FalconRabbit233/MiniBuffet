<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use MiniBuffet\RestController;

class InitController extends RestController
{
    public function run()
    {
        if (!Manager::schema()->hasTable('buffet_order')) {
            Manager::schema()->create('buffet_order', function ($table) {
                /** @var Blueprint $table */
                $table->increments('id');

                $table->smallInteger('Tisch')->nullable();
                $table->foreign('Tisch')
                    ->references('Tisch')->on('besitzen')
                    ->onUpdate('cascade')
                    ->onDelete('set null');

                $table->string('diningType');
                $table->string('menuType');

                $table->integer('adultCount');
                $table->integer('childCount');
                $table->integer('timer')->comment('seconds for each round');
                $table->integer('item')->comment('item count for each round');

                $table->integer('totalRound');
                $table->string('password');

                $table->decimal('totalPrice')->default(0);
                $table->boolean('closed')->default(0);

                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            });
        }

        if (!Manager::schema()->hasTable('buffet_order_detail')) {
            Manager::schema()->create('buffet_order_detail', function ($table) {
                /** @var Blueprint $table */
                $table->increments('id');

                $table->integer('ART_ID');
                $table->index('ART_ID');

                $table->unsignedInteger('orderId');
                $table->foreign('orderId')
                    ->references('id')->on('buffet_order')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->integer('amount');
                $table->string('name');
                $table->decimal('price');
                $table->integer('round');

                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            });
        }

        $this->responseJson(array('status' => 'done'));
    }
}
