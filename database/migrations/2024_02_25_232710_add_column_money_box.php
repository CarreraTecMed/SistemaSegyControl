<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('money_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('director_teacher_id')->nullable();
            $table->foreign('director_teacher_id')->references('id')->on('teachers')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
