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
        Schema::create('awario_mentions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('awario_id')->unique();
            $table->unsignedBigInteger('alert_id');
            $table->timestamp('mentioned_at');
            $table->unsignedBigInteger('reach')->nullable();
            $table->string('language', 10)->nullable();
            $table->text('url')->nullable();
            $table->text('snippet')->nullable();
            $table->string('title')->nullable();
            $table->string('sentiment', 20)->nullable();
            $table->string('source', 30)->nullable();
            $table->string('author_name')->nullable();
            $table->text('author_url')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awario_mentions');
    }
};
