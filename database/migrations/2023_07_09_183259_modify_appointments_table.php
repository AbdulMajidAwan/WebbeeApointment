<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Check if the column already exists before adding it
        if (!Schema::hasColumn('appointments', 'slot_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unsignedInteger('slot_id');
                $table->foreign('slot_id')->references('id')->on('slots')->onDelete('cascade');
            });
        }

        // Add new columns
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop columns
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['email', 'first_name', 'last_name']);
        });
    }
};
