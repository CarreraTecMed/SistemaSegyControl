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
        Schema::table('spents', function (Blueprint $table) {
            // $table->unsignedBigInteger('interested_id');
            // $table->foreign('interested_id')->references('id')->on('interesteds')->constrained();
            $table->string('interested')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spents', function (Blueprint $table) {
            //
        });
    }
};
