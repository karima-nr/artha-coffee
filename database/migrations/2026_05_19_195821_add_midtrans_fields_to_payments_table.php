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
        Schema::table('payments', function (Blueprint $table) {
            // Make existing offline columns nullable and change payment_method enum to string
            $table->string('payment_method')->nullable()->change();
            $table->decimal('amount_paid', 12, 2)->nullable()->change();
            $table->decimal('change', 12, 2)->nullable()->change();

            // Add Midtrans Columns
            $table->string('transaction_id')->nullable()->after('proof_of_payment');
            $table->string('payment_type')->nullable()->after('transaction_id');
            $table->decimal('gross_amount', 12, 2)->nullable()->after('payment_type');
            $table->string('transaction_status')->nullable()->after('gross_amount');
            $table->timestamp('transaction_time')->nullable()->after('transaction_status');
            $table->string('fraud_status')->nullable()->after('transaction_time');
            $table->string('snap_token')->nullable()->after('fraud_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revert columns to not nullable
            $table->string('payment_method')->nullable(false)->change();
            $table->decimal('amount_paid', 12, 2)->nullable(false)->change();
            $table->decimal('change', 12, 2)->default(0)->nullable(false)->change();

            $table->dropColumn([
                'transaction_id',
                'payment_type',
                'gross_amount',
                'transaction_status',
                'transaction_time',
                'fraud_status',
                'snap_token',
            ]);
        });
    }
};
