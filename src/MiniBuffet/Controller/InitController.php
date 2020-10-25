<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use MiniBuffet\RestController;

class InitController extends RestController
{
    public function run()
    {
        $ORDER_TABLE_NAME = 'buffet_order';
        $ORDER_DETAIL_TABLE_NAME = 'buffet_order_detail';

        $TABLE_TIP_EXIST = 'table exists, abort creating';
        $TABLE_TIP_CREATED = 'done';

        $init_status = array();

        if (Manager::schema()->hasTable($ORDER_TABLE_NAME)) {
            $init_status[$ORDER_TABLE_NAME] = $TABLE_TIP_EXIST;
        } else {
            Manager::schema()->create($ORDER_TABLE_NAME, function ($table) {
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

                $table->timestamp('lastRoundAt');
                $table->integer('currentRound')->default(0);
                $table->integer('totalRound');
                $table->string('password');

                $table->decimal('totalPrice')->default(0);
                $table->boolean('closed')->default(0);
                $table->index('closed');

                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            });

            $init_status[$ORDER_TABLE_NAME] = $TABLE_TIP_CREATED;
        }

        if (Manager::schema()->hasTable($ORDER_DETAIL_TABLE_NAME)) {
            $init_status[$ORDER_DETAIL_TABLE_NAME] = $TABLE_TIP_EXIST;
        } else {
            Manager::schema()->create($ORDER_DETAIL_TABLE_NAME, function ($table) use ($ORDER_TABLE_NAME) {
                /** @var Blueprint $table */
                $table->increments('id');

                $table->integer('ART_ID');
                $table->index('ART_ID');

                $table->unsignedInteger('orderId');
                $table->foreign('orderId')
                    ->references('id')->on($ORDER_TABLE_NAME)
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->integer('amount');
                $table->string('name');
                $table->decimal('price');
                $table->integer('round');

                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            });

            $init_status[$ORDER_DETAIL_TABLE_NAME] = $TABLE_TIP_CREATED;
        }

        $this->responseJson(array('status' => $init_status));
    }
}
