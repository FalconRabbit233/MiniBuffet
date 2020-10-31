<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use MiniBuffet\RestController;

class InitController extends RestController
{
    public function run()
    {
        $PRODUCT_TABLE_NAME = 's_karte';
        $DESCRIPTION_COLUMN_NAME = 'beschreibung';

        $ORDER_TABLE_NAME = 'buffet_order';
        $ORDER_DETAIL_TABLE_NAME = 'buffet_order_detail';
        $SETTING_TABLE_NAME = 'buffet_setting';
        $DRINK_GROUP_TABLE_NAME = 'buffet_drink_group';

        $COLUMN_TIP_EXIST = 'column exists, abort creating';
        $TABLE_TIP_EXIST = 'table exists, abort creating';
        $TABLE_TIP_NOT_EXIST = 'table not exists, abort creating';
        $TABLE_TIP_CREATED = 'done';

        $init_status = array();

        if (Manager::schema()->hasTable($PRODUCT_TABLE_NAME)) {
            $columns = Manager::connection()->select(<<<SQL
select column_name from information_schema.columns where table_schema = ? and table_name = ?
SQL
                , array($this->app->env['db']['database'], $PRODUCT_TABLE_NAME));
            $column_names = array_map(function ($item) {
                return $item['COLUMN_NAME'];
            }, $columns);

            if (in_array($DESCRIPTION_COLUMN_NAME, $column_names)) {
                $init_status["$PRODUCT_TABLE_NAME.$DESCRIPTION_COLUMN_NAME"] = $COLUMN_TIP_EXIST;
            } else {
                Manager::schema()->table($PRODUCT_TABLE_NAME, function ($table) use ($DESCRIPTION_COLUMN_NAME) {
                    /** @var Blueprint $table */
                    $table->text($DESCRIPTION_COLUMN_NAME);
                });

                $init_status["$PRODUCT_TABLE_NAME.$DESCRIPTION_COLUMN_NAME"] = $TABLE_TIP_CREATED;
            }

        } else {
            $init_status["$PRODUCT_TABLE_NAME.$DESCRIPTION_COLUMN_NAME"] = $TABLE_TIP_NOT_EXIST;
        }

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

                $table->timestamp('lastRoundAt')->default('2020-10-31 00:00:00');
                $table->integer('currentRound')->default(0);
                $table->integer('totalRound');
                $table->string('password');

                $table->decimal('totalPrice')->default(0);
                $table->boolean('closed')->default(0);
                $table->index('closed');

                $table->timestamp('createdAt')->default('2020-10-31 00:00:00');
                $table->timestamp('updatedAt')->default('2020-10-31 00:00:00');
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

                $table->boolean('attribute1Selected')->default(false);
                $table->boolean('attribute2Selected')->default(false);
                $table->string('attr1')->nullable();
                $table->string('attr2')->nullable();

                $table->timestamp('createdAt')->default('2020-10-31 00:00:00');
                $table->timestamp('updatedAt')->default('2020-10-31 00:00:00');
            });

            $init_status[$ORDER_DETAIL_TABLE_NAME] = $TABLE_TIP_CREATED;
        }

        if (Manager::schema()->hasTable($SETTING_TABLE_NAME)) {
            $init_status[$SETTING_TABLE_NAME] = $TABLE_TIP_EXIST;
        } else {
            Manager::schema()->create($SETTING_TABLE_NAME, function ($table) {
                /** @var Blueprint $table */
                $table->increments('id');
                $table->string('buffetPassword');
                $table->integer('totalRound');
            });

            Manager::table($SETTING_TABLE_NAME)
                ->insert(array(
                    'buffetPassword' => '123456',
                    'totalRound' => 10,
                ));

            $init_status[$SETTING_TABLE_NAME] = $TABLE_TIP_CREATED;
        }

        if (Manager::schema()->hasTable($DRINK_GROUP_TABLE_NAME)) {
            $init_status[$DRINK_GROUP_TABLE_NAME] = $TABLE_TIP_EXIST;
        } else {
            Manager::schema()->create($DRINK_GROUP_TABLE_NAME, function ($table) {
                /** @var Blueprint $table */
                $table->increments('id');
                $table->integer('groupId');

            });

            $init_status[$DRINK_GROUP_TABLE_NAME] = $TABLE_TIP_CREATED;
        }

        $this->responseJson(array('status' => $init_status));
    }
}
