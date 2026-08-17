<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('user_id')->nullable()->unique()->after('employee_code')->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->nullOnDelete();
            $table->string('first_name', 100)->after('department_id');
            $table->string('last_name', 100)->after('first_name');
            $table->string('email', 150)->nullable()->after('last_name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('job_title', 150)->after('phone');
            $table->string('employment_type', 30)->default('FULL_TIME')->after('job_title');
            $table->date('joined_on')->after('employment_type');
            $table->date('date_of_birth')->nullable()->after('joined_on');
            $table->string('emergency_contact_name', 150)->nullable()->after('date_of_birth');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('status', 30)->default('ACTIVE')->after('emergency_contact_phone');
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index(['department_id', 'status']);
            $table->index(['status', 'joined_on']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id')->constrained('employees')->cascadeOnDelete();
            $table->date('attendance_date')->after('employee_id');
            $table->dateTime('clock_in')->nullable()->after('attendance_date');
            $table->dateTime('clock_out')->nullable()->after('clock_in');
            $table->string('status', 30)->default('PRESENT')->after('clock_out');
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('recorded_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('leave_code', 30)->nullable()->unique()->after('id');
            $table->foreignId('employee_id')->nullable()->after('leave_code')->constrained('employees')->cascadeOnDelete();
            $table->string('leave_type', 30)->after('employee_id');
            $table->date('start_date')->after('leave_type');
            $table->date('end_date')->after('start_date');
            $table->unsignedSmallInteger('total_days')->after('end_date');
            $table->text('reason')->after('total_days');
            $table->string('status', 30)->default('PENDING')->after('reason');
            $table->dateTime('requested_at')->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('requested_at')->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('reviewer_comment')->nullable()->after('reviewed_at');

            $table->index(['employee_id', 'status']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['leave_code', 'leave_type', 'start_date', 'end_date', 'total_days', 'reason', 'status', 'requested_at', 'reviewed_at', 'reviewer_comment']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropConstrainedForeignId('recorded_by');
            $table->dropColumn(['attendance_date', 'clock_in', 'clock_out', 'status', 'notes']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['employee_code', 'first_name', 'last_name', 'email', 'phone', 'job_title', 'employment_type', 'joined_on', 'date_of_birth', 'emergency_contact_name', 'emergency_contact_phone', 'status', 'notes', 'deleted_at']);
        });
    }
};
