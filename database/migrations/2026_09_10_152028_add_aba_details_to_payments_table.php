<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('qr_string')->nullable();
            $table->string('aba_transaction_id')->nullable();
            $table->text('aba_context')->nullable();
            $table->string('aba_last_action')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['qr_string', 'aba_transaction_id', 'aba_context', 'aba_last_action']);
        });
    }
};
