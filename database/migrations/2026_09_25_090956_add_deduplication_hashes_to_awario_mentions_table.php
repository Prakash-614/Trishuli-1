<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('awario_mentions', function (Blueprint $table) {
            $table->string('clean_url_hash', 64)->nullable()->index()->after('url');
            $table->string('clean_title_hash', 64)->nullable()->index()->after('title');
            $table->unsignedInteger('duplicate_count')->default(0)->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('awario_mentions', function (Blueprint $table) {
            $table->dropIndex(['clean_url_hash']);
            $table->dropIndex(['clean_title_hash']);
            $table->dropColumn(['clean_url_hash', 'clean_title_hash', 'duplicate_count']);
        });
    }
};