<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('admission_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('patient_id')->nullable()->after('admission_code')->constrained('patients')->nullOnDelete();
            $table->foreignId('attending_doctor_id')->nullable()->after('patient_id')->constrained('doctors')->nullOnDelete();
            $table->date('admitted_on')->nullable()->after('attending_doctor_id');
            $table->date('discharged_on')->nullable()->after('admitted_on');
            $table->string('room_number', 50)->nullable()->after('discharged_on');
            $table->string('status', 30)->default('ADMITTED')->after('room_number');
            $table->decimal('daily_rate', 12, 2)->default(0)->after('status');
            $table->text('notes')->nullable()->after('daily_rate');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();

            $table->index(['patient_id', 'status']);
            $table->index(['admitted_on', 'discharged_on']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('patient_id')->nullable()->after('invoice_code')->constrained('patients')->nullOnDelete();
            $table->string('status', 30)->default('UNPAID')->after('patient_id')->index();
            $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('total_amount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance', 12, 2)->default(0)->after('paid_amount');
            $table->dateTime('issued_at')->after('balance');
            $table->date('due_date')->nullable()->after('issued_at');
            $table->text('notes')->nullable()->after('due_date');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('created_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();

            $table->index(['patient_id', 'issued_at']);
            $table->index(['status', 'due_date']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_type', 30)->after('invoice_id');
            $table->string('description', 255)->after('item_type');
            $table->string('source_type', 50)->nullable()->after('description');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->decimal('quantity', 12, 2)->default(1)->after('source_id');
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            $table->decimal('line_total', 12, 2)->default(0)->after('unit_price');

            $table->index(['source_type', 'source_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('invoice_id')->nullable()->after('payment_code')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('patient_id')->nullable()->after('invoice_id')->constrained('patients')->nullOnDelete();
            $table->decimal('amount', 12, 2)->after('patient_id');
            $table->string('payment_method', 30)->after('amount');
            $table->string('reference_number', 100)->nullable()->after('payment_method');
            $table->dateTime('paid_at')->after('reference_number');
            $table->text('notes')->nullable()->after('paid_at');
            $table->foreignId('received_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();

            $table->index(['invoice_id', 'paid_at']);
            $table->index(['patient_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn(['payment_code', 'amount', 'payment_method', 'reference_number', 'paid_at', 'notes']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn(['item_type', 'description', 'source_type', 'source_id', 'quantity', 'unit_price', 'line_total']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['invoice_code', 'status', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'paid_amount', 'balance', 'issued_at', 'due_date', 'notes', 'cancelled_at']);
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('attending_doctor_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['admission_code', 'admitted_on', 'discharged_on', 'room_number', 'status', 'daily_rate', 'notes']);
        });
    }
};
