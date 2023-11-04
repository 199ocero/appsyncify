<?php

use App\Enums\Actor;
use App\Enums\Operation;
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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('integration_id')->nullable()->references('id')->on('integrations')->onDelete('cascade');
            $table->foreignId('actor_id')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->enum('actor_type', Actor::all())->default(Actor::USER);
            $table->string('name');
            $table->enum('status', Operation::all())->default(Operation::PENDING);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
