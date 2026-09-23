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
            $table->timestamp('alert_sent_at')->nullable()->after('classified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awario_mentions', function (Blueprint $table) {
            $table->dropColumn('alert_sent_at');
        });
    }
};
