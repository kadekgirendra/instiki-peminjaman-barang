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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('purpose');
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('status', ['pending', 'booked', 'completed', 'rejected'])->default('pending');
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->string('document_path')->nullable(); // untuk KTM/Surat Tugas
            $table->timestamps();

            // index penting untuk query overlap tanggal — dipakai terus-menerus di AvailabilityService
            $table->index(['item_id', 'status', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
