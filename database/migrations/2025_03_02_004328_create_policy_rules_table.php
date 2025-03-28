<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->onDelete('cascade');
            $table->string('category'); // flight, hotel, car
            $table->string('rule_type'); // max_price, class, advance_booking, etc.
            $table->text('rule_value');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_rules');
    }
};