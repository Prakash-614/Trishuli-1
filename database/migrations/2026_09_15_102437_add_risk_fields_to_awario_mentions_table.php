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
            $table->string('risk_tier', 10)->nullable()->after('sentiment');
            $table->text('risk_reason')->nullable()->after('risk_tier');
            $table->timestamp('classified_at')->nullable()->after('risk_reason');
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