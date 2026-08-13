<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('doctor_code', 30)
                ->nullable()
                ->unique();

            $table->string('registration_number', 100)
                ->unique();

            $table->string('designation', 150)
                ->nullable();

            $table->string('specialization', 150)
                ->nullable();

            $table->text('qualifications')
                ->nullable();

            $table->unsignedSmallInteger('experience_years')
                ->default(0);

            $table->decimal('consultation_fee', 10, 2)
                ->default(0);

            $table->string('room_number', 50)
                ->nullable();

            $table->text('biography')
                ->nullable();

            $table->date('joined_at')
                ->nullable();

            $table->boolean('is_available')
                ->default(true)
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index('specialization');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};