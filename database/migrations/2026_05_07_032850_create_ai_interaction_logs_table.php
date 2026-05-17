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
        Schema::create('ai_interaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('primm_question_id')->constrained()->onDelete('cascade');
            
            // Data yang dikirim siswa saat mencoba (Proses)
            $table->text('student_answer_text')->nullable(); // Jawaban deskripsi
            $table->text('student_answer_code')->nullable(); // Jawaban kode
            
            // Respon dari Groq
            $table->text('ai_feedback'); // Clue scaffolding yang diberikan AI
            
            // Status kelulusan untuk sesi tersebut
            $table->boolean('is_valid'); // 1 jika LULUS, 0 jika harus memperbaiki
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interaction_logs');
    }
};
