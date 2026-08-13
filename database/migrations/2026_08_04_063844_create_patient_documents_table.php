<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('document_type', 100);

            $table->string('title', 200);

            $table->string('file_name');

            $table->string('file_path');

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('file_size');

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('document_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
    }
};