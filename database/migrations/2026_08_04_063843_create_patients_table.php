<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->string('patient_code', 30)
                ->nullable()
                ->unique();

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->date('date_of_birth');

            $table->string('gender', 30);

            $table->string('blood_group', 5)
                ->nullable();

            $table->string('nic_passport', 50)
                ->nullable()
                ->unique();

            $table->string('email', 150)
                ->nullable();

            $table->string('phone', 20);

            $table->string('alternate_phone', 20)
                ->nullable();

            $table->string('address_line_1');
            $table->string('address_line_2')
                ->nullable();

            $table->string('city', 100);

            $table->string('district', 100)
                ->nullable();

            $table->string('postal_code', 20)
                ->nullable();

            $table->string('country', 100)
                ->nullable();

            $table->string('emergency_contact_name', 150);

            $table->string('emergency_contact_relation', 100)
                ->nullable();

            $table->string('emergency_contact_phone', 20);

            $table->text('allergies')
                ->nullable();

            $table->text('chronic_conditions')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->foreignId('registered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'last_name',
                'first_name',
            ]);

            $table->index('phone');
            $table->index('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};