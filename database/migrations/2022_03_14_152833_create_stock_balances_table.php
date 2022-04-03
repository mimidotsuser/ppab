<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')
                ->cascadeOnDelete();

            $table->unsignedInteger('reorder_level')->default(0); //snapshot
            $table->string('internal_code'); // for human dev use
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);

            //requests qty expected to leave the warehouse
            $table->unsignedInteger('b2c_qty_in_pipeline')->default(0);

            //purchase requests/RFQ/LPO qty expected from vendors
            $table->unsignedInteger('b2b_qty_in_pipeline')->default(0);

            //Calculated columns
            $table->integer('stock_balance', false, false)
                ->storedAs(new Expression('`qty_in`-`qty_out`'));

            $table->integer('virtual_balance')
                ->storedAs(new Expression('`stock_balance`-`b2c_qty_in_pipeline`'));

            $outOfOrderExpression = <<<EOL
                IF(`reorder_level`=0,0,
               ((`stock_balance` + CAST(`b2b_qty_in_pipeline` AS SIGNED)) -
                CAST(`reorder_level` AS SIGNED))<=0
                )
            EOL;

            $table->integer('out_of_stock')->storedAs(new Expression($outOfOrderExpression));

            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by_id')->constrained('users')->restrictOnDelete();
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
        Schema::dropIfExists('stock_balances');
    }
};
