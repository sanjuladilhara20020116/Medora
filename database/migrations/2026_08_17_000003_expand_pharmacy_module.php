<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_categories', function (Blueprint $table) {
            $table->string('category_code', 30)->nullable()->unique()->after('id');
            $table->string('name', 150)->unique()->after('category_code');
            $table->text('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('description')->index();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->string('medicine_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('medicine_category_id')->nullable()->after('medicine_code')->constrained('medicine_categories')->nullOnDelete();
            $table->string('name', 200)->after('medicine_category_id');
            $table->string('generic_name', 200)->nullable()->after('name');
            $table->string('manufacturer', 200)->nullable()->after('generic_name');
            $table->string('dosage_form', 100)->after('manufacturer');
            $table->string('strength', 100)->nullable()->after('dosage_form');
            $table->decimal('reorder_level', 12, 2)->unsigned()->default(0)->after('strength');
            $table->text('description')->nullable()->after('reorder_level');
            $table->boolean('is_active')->default(true)->after('description')->index();

            $table->index(['medicine_category_id', 'is_active']);
            $table->index('name');
        });

        Schema::table('medicine_stocks', function (Blueprint $table) {
            $table->foreignId('medicine_id')->after('id')->constrained('medicines')->restrictOnDelete();
            $table->string('batch_number', 100)->after('medicine_id');
            $table->date('expiry_date')->after('batch_number');
            $table->date('received_date')->after('expiry_date');
            $table->decimal('quantity_received', 12, 2)->unsigned()->after('received_date');
            $table->decimal('quantity_available', 12, 2)->unsigned()->after('quantity_received');
            $table->decimal('unit_cost', 12, 2)->default(0)->after('quantity_available');
            $table->decimal('selling_price', 12, 2)->default(0)->after('unit_cost');
            $table->string('supplier', 200)->nullable()->after('selling_price');
            $table->foreignId('received_by')->nullable()->after('supplier')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('received_by');

            $table->unique(['medicine_id', 'batch_number']);
            $table->index(['expiry_date', 'quantity_available']);
            $table->index(['medicine_id', 'quantity_available']);
        });

        Schema::create('prescription_dispenses', function (Blueprint $table) {
            $table->id();
            $table->string('dispense_code', 30)->nullable()->unique();
            $table->foreignId('prescription_id')->constrained('prescriptions')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('PARTIAL');
            $table->text('notes')->nullable();
            $table->dateTime('dispensed_at');
            $table->timestamps();

            $table->index(['prescription_id', 'dispensed_at']);
            $table->index(['patient_id', 'dispensed_at']);
        });

        Schema::create('prescription_dispense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_dispense_id')->constrained('prescription_dispenses')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->constrained('prescription_items')->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->foreignId('medicine_stock_id')->constrained('medicine_stocks')->restrictOnDelete();
            $table->decimal('quantity_dispensed', 12, 2)->unsigned();
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            $table->index('prescription_item_id');
            $table->index('medicine_stock_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_dispense_items');
        Schema::dropIfExists('prescription_dispenses');

        Schema::table('medicine_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_id');
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn([
                'batch_number',
                'expiry_date',
                'received_date',
                'quantity_received',
                'quantity_available',
                'unit_cost',
                'selling_price',
                'supplier',
                'notes',
            ]);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_category_id');
            $table->dropColumn([
                'medicine_code',
                'name',
                'generic_name',
                'manufacturer',
                'dosage_form',
                'strength',
                'reorder_level',
                'description',
                'is_active',
            ]);
        });

        Schema::table('medicine_categories', function (Blueprint $table) {
            $table->dropColumn(['category_code', 'name', 'description', 'is_active']);
        });
    }
};
