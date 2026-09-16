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
    Schema::table('awario_mentions', function (Blueprint $table) {
        $table->longText('content')->nullable()->after('snippet');
        $table->timestamp('content_fetched_at')->nullable()->after('content');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awario_mentions', function (Blueprint $table) {
            //
        });
    }
};