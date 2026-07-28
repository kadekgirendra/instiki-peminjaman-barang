<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('return_photo')->nullable()->after('returned_at');
            $table->text('return_note')->nullable()->after('return_photo');
            $table->timestamp('return_requested_at')->nullable()->after('return_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['return_photo', 'return_note', 'return_requested_at']);
        });
    }
};
