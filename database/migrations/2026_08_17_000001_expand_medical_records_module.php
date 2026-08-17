<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('record_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('patient_id')->after('record_code')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->after('patient_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->unique()->after('doctor_id')->constrained('appointments')->nullOnDelete();
            $table->dateTime('recorded_at')->after('appointment_id');
            $table->text('chief_complaint')->nullable()->after('recorded_at');
            $table->text('diagnosis')->after('chief_complaint');
            $table->text('treatment_plan')->nullable()->after('diagnosis');
            $table->text('clinical_notes')->nullable()->after('treatment_plan');
            $table->date('follow_up_date')->nullable()->after('clinical_notes');
            $table->text('follow_up_notes')->nullable()->after('follow_up_date');
            $table->foreignId('created_by')->nullable()->after('follow_up_notes')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->index(['patient_id', 'recorded_at']);
            $table->index(['doctor_id', 'recorded_at']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('prescription_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('medical_record_id')->unique()->after('prescription_code')->constrained('medical_records')->cascadeOnDelete();
            $table->foreignId('patient_id')->after('medical_record_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('prescribed_by')->after('patient_id')->constrained('doctors')->restrictOnDelete();
            $table->dateTime('issued_at')->after('prescribed_by');
            $table->text('notes')->nullable()->after('issued_at');

            $table->index(['patient_id', 'issued_at']);
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->foreignId('prescription_id')->after('id')->constrained('prescriptions')->cascadeOnDelete();
            $table->string('medicine_name', 200)->after('prescription_id');
            $table->string('dosage', 100)->after('medicine_name');
            $table->string('frequency', 100)->after('dosage');
            $table->unsignedSmallInteger('duration_days')->nullable()->after('frequency');
            $table->decimal('quantity', 10, 2)->nullable()->after('duration_days');
            $table->text('instructions')->nullable()->after('quantity');
        });

        Schema::create('medical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_type', 100);
            $table->string('title', 200);
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['medical_record_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prescription_id');
            $table->dropColumn([
                'medicine_name',
                'dosage',
                'frequency',
                'duration_days',
                'quantity',
                'instructions',
            ]);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medical_record_id');
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('prescribed_by');
            $table->dropColumn([
                'prescription_code',
                'issued_at',
                'notes',
            ]);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropConstrainedForeignId('appointment_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn([
                'record_code',
                'recorded_at',
                'chief_complaint',
                'diagnosis',
                'treatment_plan',
                'clinical_notes',
                'follow_up_date',
                'follow_up_notes',
            ]);
        });
    }
};
