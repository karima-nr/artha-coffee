<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reset_by')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('month');          // 1–12
            $table->smallInteger('year');          // e.g. 2026
            $table->decimal('revenue_snapshot', 15, 2)->default(0); // pendapatan saat reset
            $table->unsignedInteger('orders_snapshot')->default(0);  // jumlah pesanan saat reset
            $table->timestamp('reset_at');          // waktu eksekusi reset
            $table->string('note')->nullable();     // catatan opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_resets');
    }
};
