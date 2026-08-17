<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->string('test_code', 30)->nullable()->unique()->after('id');
            $table->string('name', 200)->after('test_code');
            $table->string('category', 100)->nullable()->after('name');
            $table->string('specimen_type', 100)->after('category');
            $table->string('unit', 100)->nullable()->after('specimen_type');
            $table->string('reference_range', 255)->nullable()->after('unit');
            $table->unsignedSmallInteger('turnaround_hours')->nullable()->after('reference_range');
            $table->decimal('price', 10, 2)->default(0)->after('turnaround_hours');
            $table->text('notes')->nullable()->after('price');
            $table->boolean('is_active')->default(true)->after('notes')->index();

            $table->index(['category', 'is_active']);
        });

        Schema::table('lab_requests', function (Blueprint $table) {
            $table->string('request_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('patient_id')->after('request_code')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->after('patient_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('lab_test_id')->after('doctor_id')->constrained('lab_tests')->restrictOnDelete();
            $table->foreignId('medical_record_id')->nullable()->after('lab_test_id')->constrained('medical_records')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->after('medical_record_id')->constrained('users')->nullOnDelete();
            $table->string('priority', 20)->default('NORMAL')->after('requested_by');
            $table->string('status', 30)->default('REQUESTED')->after('priority')->index();
            $table->text('clinical_notes')->nullable()->after('status');
            $table->dateTime('requested_at')->after('clinical_notes');
            $table->dateTime('sample_collected_at')->nullable()->after('requested_at');
            $table->foreignId('sample_collected_by')->nullable()->after('sample_collected_at')->constrained('users')->nullOnDelete();
            $table->string('sample_identifier', 100)->nullable()->after('sample_collected_by');
            $table->string('specimen_condition', 30)->nullable()->after('sample_identifier');
            $table->text('sample_notes')->nullable()->after('specimen_condition');
            $table->dateTime('processing_started_at')->nullable()->after('sample_notes');
            $table->dateTime('completed_at')->nullable()->after('processing_started_at');

            $table->index(['patient_id', 'requested_at']);
            $table->index(['doctor_id', 'requested_at']);
            $table->index(['lab_test_id', 'status']);
        });

        Schema::table('lab_results', function (Blueprint $table) {
            $table->foreignId('lab_request_id')->unique()->after('id')->constrained('lab_requests')->cascadeOnDelete();
            $table->text('result_value')->after('lab_request_id');
            $table->string('unit', 100)->nullable()->after('result_value');
            $table->string('reference_range', 255)->nullable()->after('unit');
            $table->string('interpretation', 30)->default('NORMAL')->after('reference_range');
            $table->text('remarks')->nullable()->after('interpretation');
            $table->foreignId('entered_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->after('entered_by')->constrained('users')->nullOnDelete();
            $table->dateTime('resulted_at')->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lab_request_id');
            $table->dropConstrainedForeignId('entered_by');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn([
                'result_value',
                'unit',
                'reference_range',
                'interpretation',
                'remarks',
                'resulted_at',
            ]);
        });

        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropConstrainedForeignId('lab_test_id');
            $table->dropConstrainedForeignId('medical_record_id');
            $table->dropConstrainedForeignId('requested_by');
            $table->dropConstrainedForeignId('sample_collected_by');
            $table->dropColumn([
                'request_code',
                'priority',
                'status',
                'clinical_notes',
                'requested_at',
                'sample_collected_at',
                'sample_identifier',
                'specimen_condition',
                'sample_notes',
                'processing_started_at',
                'completed_at',
            ]);
        });

        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropColumn([
                'test_code',
                'name',
                'category',
                'specimen_type',
                'unit',
                'reference_range',
                'turnaround_hours',
                'price',
                'notes',
                'is_active',
            ]);
        });
    }
};
