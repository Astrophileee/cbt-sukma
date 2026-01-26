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
        Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->string('jurusan');
                $table->unsignedBigInteger('created_by');
                $table->string('type');
                $table->text('question_text');
                $table->unsignedInteger('points')->default(1);
                $table->string('difficulty')->nullable();
                $table->boolean('is_published')->default(false);
                $table->string('question_image')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->cascadeOnUpdate();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
