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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('site_description')->nullable()->after('site_name');
            $table->string('site_facebook_url')->nullable()->after('site_address');
            $table->string('site_twitter_url')->nullable()->after('site_facebook_url');
            $table->string('site_instagram_url')->nullable()->after('site_twitter_url');
            $table->string('site_linkedin_url')->nullable()->after('site_instagram_url');
            $table->string('site_youtube_url')->nullable()->after('site_linkedin_url');
            $table->string('site_telegram_url')->nullable()->after('site_youtube_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            //
        });
    }
};
