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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->foreignId('app_combination_id')->nullable()->references('id')->on('app_combination')->onDelete('set null');
            $table->foreignId('first_app_token_id')->nullable()->default(null)->references('id')->on('tokens')->onDelete('set null');
            $table->foreignId('second_app_token_id')->nullable()->default(null)->references('id')->on('tokens')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};