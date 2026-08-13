<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->string('appointment_code', 30)
                ->nullable()
                ->unique();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->restrictOnDelete();

            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->foreignId('doctor_schedule_id')
                ->nullable()
                ->constrained('doctor_schedules')
                ->nullOnDelete();

            $table->date('appointment_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->string('appointment_type', 30)
                ->default('CONSULTATION');

            $table->string('priority', 20)
                ->default('NORMAL');

            $table->string('status', 30)
                ->default('SCHEDULED');

            $table->text('reason')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('checked_in_at')
                ->nullable();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->text('cancellation_reason')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'doctor_id',
                'appointment_date',
                'status',
            ]);

            $table->index([
                'patient_id',
                'appointment_date',
            ]);

            $table->index([
                'department_id',
                'appointment_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};