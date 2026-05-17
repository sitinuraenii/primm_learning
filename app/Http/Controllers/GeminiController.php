<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeminiController extends Controller
{
    public function getHint(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'pertanyaan_id' => 'required|integer', 
        ]);

        try {
            $data = DB::table('primm_questions')
                ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
                ->select('primm_questions.pembahasan', 'primm_questions.pertanyaan', 'primms.tahap', 'primms.kode_program')
                ->where('primm_questions.id', $request->pertanyaan_id)
                ->first();

            if (!$data) {
                return back()->with('error', 'Konteks soal tidak ditemukan.');
            }

            // =========================================================================
            // HITUNG INTERAKSI UNTUK KONTEKS SCAFFOLDING (TANPA PEMBATASAN CHAT)
            // =========================================================================
            $sessionKey = 'chat_count_' . $request->pertanyaan_id;
            
            // Jika history dari frontend kosong (habis di-refresh), reset hitungan dari 0
            if (empty($request->history)) {
                session()->forget($sessionKey);
            }

            $jumlahChatSiswa = session()->get($sessionKey, 0);
            $interaksiKe = $jumlahChatSiswa + 1;
            // =========================================================================

            // =========================================================================
            // PENERJEMAH ROLE 'BOT' MENJADI 'ASSISTANT' (SOLUSI EROR CHAT KE-2)
            // =========================================================================
            $formattedHistory = [];
            foreach ($request->history ?? [] as $msg) {
                // Mengubah 'bot' dari React menjadi 'assistant' agar dikenali Groq/DeepSeek
                $role = ($msg['role'] === 'bot' || $msg['role'] === 'assistant') ? 'assistant' : 'user';
                $formattedHistory[] = [
                    'role' => $role,
                    'content' => $msg['content']
                ];
            }

            // Mencegah duplikasi pesan terakhir di dalam array history
            if (!empty($formattedHistory) && end($formattedHistory)['content'] === $request->question) {
                array_pop($formattedHistory);
            }
            // =========================================================================

            // 1. Susun daftar provider berdasarkan PRIORITAS
            $providers = [];

            // PRIORITAS 1: Groq
            foreach (['GROQ_API_KEY'] as $envKey) {
                if ($key = env($envKey)) {
                    $providers[] = ['type' => 'groq', 'key' => $key];
                }
            }

            // PRIORITAS 2: DeepSeek
            foreach (['DEEPSEEK_API_KEY'] as $envKey) {
                if ($key = env($envKey)) {
                    $providers[] = ['type' => 'deepseek', 'key' => $key];
                }
            }

            if (empty($providers)) {
                return back()->with('error', 'API Key tidak dikonfigurasi di .env');
            }

            $systemPrompt = "Kamu adalah Tutor AI interaktif yang ramah, santai, dan SANGAT SINGKAT untuk siswa SMK Kelas 10. Tugasmu membimbing siswa menemukan jawaban secara mandiri dengan metode scaffolding langkah demi langkah — TANPA memberi jawaban langsung, TANPA membocorkan kunci jawaban, dan TANPA membuat kesimpulan.

            KONTEKS INTERAKSI:
            - Tahap Bimbingan Saat Ini: {$data->tahap}
            - Pertanyaan Siswa/Soal: \"{$data->pertanyaan}\"
            - Kode Program: 
            {$data->kode_program}
            - Konsep Target Pemahaman: \"{$data->pembahasan}\"
            - Urutan Interaksi Ke: {$interaksiKe}

            BATASAN RESPONS (MUTLAK & KETAT):
            1. WAJIB MAKSIMAL 2 KALIMAT PENDEK per respons. Jangan bertele-tele, jangan membuat penjelasan panjang, dan jangan berparagraf-paragraf.
            2. DILARANG KERAS memberikan jawaban langsung atau membocorkan cara kerja elemen kode di kolom \"{$data->pembahasan}\".

            STRATEGI SCAFFOLDING LANGSUNG PADA INTI:
            1. Jika soal meminta penjelasan (Proses/Fungsi/Alasan): Jangan beri penjelasannya. Pancing siswa memberikan pendapat/dugaan awal mereka mengenai elemen tersebut dan suruh mereka melihat elemen di sekitarnya.
            2. Jika siswa menjawab singkat (Contoh: 'ya', 'berubah', 'ada in'): Jangan beri petunjuk baru. Paksa siswa untuk menjelaskan detail dari jawaban singkatnya (Misal: 'Nah, di sebelah mana berubahnya? Coba jelaskan alasanmu!').
            3. Jika siswa memberikan kemajuan informasi: Akui jawaban mereka, lalu bimbing selangkah lebih dekat ke target tanpa membocorkan langkah berikutnya.

            EVALUASI AKHIR CHAT:
            - Jika jawaban siswa sudah tepat sesuai \"{$data->pembahasan}\": JANGAN memberikan pertanyaan baru. Langsung ketik kalimat persis tanpa tambahan kata lain: 'Tulis kesimpulanmu di kolom jawaban sekarang. 💪'

            PANDUAN BAHASA & ANTI-SPOILER:
            1. Gunakan gaya bahasa tutor yang mengalir, alami, hangat, dan panggil 'kamu'.
            2. JANGAN PERNAH menyebut istilah teknis pemrograman (seperti: perulangan, loop, iterasi, array, variabel, counter, range) sebelum siswa menuliskannya sendiri di dalam chat.";

            $jawabanAI = null;

            foreach ($providers as $provider) {
                try {
                    $response = null; // Inisialisasi awal aman dari undefined variable warning

                    if ($provider['type'] === 'groq') {
                        $response = Http::withoutVerifying()->timeout(20)
                            ->withToken($provider['key'])
                            ->post('https://api.groq.com/openai/v1/chat/completions', [
                                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                                'messages' => array_merge(
                                    [['role' => 'system', 'content' => $systemPrompt]],
                                    $formattedHistory, // Menggunakan history yang sudah bersih & standar
                                    [['role' => 'user', 'content' => $request->question]]
                                ),
                                'temperature' => 0.2,
                                'max_tokens' => 300,
                            ]);

                        if ($response->successful()) {
                            $jawabanAI = $response->json()['choices'][0]['message']['content'] ?? null;
                        }

                    } elseif ($provider['type'] === 'deepseek') {
                        $response = Http::withoutVerifying()->timeout(25)
                            ->withToken($provider['key'])
                            ->post('https://api.deepseek.com/chat/completions', [
                                'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
                                'messages' => array_merge(
                                    [['role' => 'system', 'content' => $systemPrompt]],
                                    $formattedHistory, // Menggunakan history yang sudah bersih & standar
                                    [['role' => 'user', 'content' => $request->question]]
                                ),
                                'temperature' => 0.2,
                                'max_tokens' => 300,
                            ]);

                        if ($response->successful()) {
                            $jawabanAI = $response->json()['choices'][0]['message']['content'] ?? null;
                        }
                    }

                    if ($jawabanAI) {
                        // Simpan hitungan chat terbaru ke session hanya jika API sukses merespons
                        session()->put($sessionKey, $interaksiKe);
                        break;
                    }

                    $statusCode = $response?->status() ?? 'No Response';
                    Log::warning("Provider {$provider['type']} gagal (Status: {$statusCode}), mencoba provider berikutnya...");

                } catch (\Exception $e) {
                    Log::error("Gagal pada provider {$provider['type']}: " . $e->getMessage());
                    continue;
                }
            }

            if ($jawabanAI) {
                return back()->with('aiResponse', $jawabanAI);
            }

            return back()->with('error', 'Tutor sedang sangat ramai, coba tanyakan ke guru atau pelajari materi yaa!');

        } catch (\Exception $e) {
            Log::error("Sistem Error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem pada server AI.');
        }
    }
}