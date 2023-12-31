<?php

use App\Enums\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->boolean('is_active')->default(Status::ACTIVE);
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->foreignId('app_combination_id')->nullable()->references('id')->on('app_combination')->onDelete('set null');
            $table->foreignId('first_app_token_id')->nullable()->default(null)->references('id')->on('tokens')->onDelete('set null');
            $table->foreignId('second_app_token_id')->nullable()->default(null)->references('id')->on('tokens')->onDelete('set null');
            $table->json('first_app_settings')->nullable();
            $table->json('second_app_settings')->nullable();
            $table->json('custom_field_mapping')->nullable();
            $table->json('schedule')->nullable();
            $table->smallInteger('step')->default(1);
            $table->boolean('is_finished')->default(false);
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
