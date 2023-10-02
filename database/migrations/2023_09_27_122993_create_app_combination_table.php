<?php

use App\Enums\Constant;
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
        Schema::create('app_combination', function (Blueprint $table) {
            $table->id();
            $table->foreignId('first_app_id')->nullable()->references('id')->on('apps')->onDelete('set null');
            $table->foreignId('second_app_id')->nullable()->references('id')->on('apps')->onDelete('set null');
            $table->boolean('is_active')->default(Constant::ACTIVE);
            $table->json('features')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_combination');
    }
};
