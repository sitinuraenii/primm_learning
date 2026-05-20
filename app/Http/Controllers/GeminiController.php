<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GeminiController extends Controller
{
    // =========================================================================
    // FITUR 1: FITUR CHAT DUA ARAH (KODE LAMA KAMU - TETAP DIPERTAHANKAN)
    // =========================================================================
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

            $sessionKey = 'chat_count_' . $request->pertanyaan_id;
            
            if (empty($request->history)) {
                Session::forget($sessionKey);
            }

            $jumlahChatSiswa = Session::get($sessionKey, 0);
            $interaksiKe = $jumlahChatSiswa + 1;

            $formattedHistory = [];
            foreach ($request->history ?? [] as $msg) {
                $role = ($msg['role'] === 'bot' || $msg['role'] === 'assistant') ? 'assistant' : 'user';
                $formattedHistory[] = [
                    'role' => $role,
                    'content' => $msg['content']
                ];
            }

            if (!empty($formattedHistory) && end($formattedHistory)['content'] === $request->question) {
                array_pop($formattedHistory);
            }

            $providers = [];
            foreach (['GROQ_API_KEY'] as $envKey) {
                if ($key = env($envKey)) {
                    $providers[] = ['type' => 'groq', 'key' => $key];
                }
            }
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
            2. DILARANG menggunakan pertanyaan menuntun (leading questions) yang di dalam kalimat pertanyaannya sudah membocorkan peran/cara kerja elemen (Contoh dilarang: 'Apakah i bertindak sebagai penampung nilai?'). Gunakan pertanyaan terbuka (Contoh: 'Menurutmu, apa tugas huruf i di baris tersebut?').
            3. Jika siswa menjawab singkat (Contoh: 'ya', 'berubah', 'ada in'): Jangan beri petunjuk baru. Paksa siswa untuk menjelaskan detail dari jawaban singkatnya (Misal: 'Nah, di sebelah mana berubahnya? Coba jelaskan alasanmu!').
            4. Jika siswa memberikan kemajuan informasi: Akui jawaban mereka, lalu bimbing selangkah lebih dekat ke target tanpa membocorkan langkah berikutnya.
            5. Jika jawaban siswa kurang tepat atau salah: JANGAN mengoreksi isi jawaban secara langsung, JANGAN menyebutkan jawaban yang benar. Katakan bahwa ada bagian yang perlu diperhatikan kembali, lalu arahkan siswa untuk mengamati bagian kode yang relevan dan berpikir ulang.
            EVALUASI AKHIR CHAT:
            - Jika jawaban siswa sudah tepat sesuai \"{$data->pembahasan}\": JANGAN memberikan pertanyaan baru. Langsung ketik kalimat persis tanpa tambahan kata lain: 'Tulis kesimpulanmu di kolom jawaban sekarang. 💪'

            PANDUAN BAHASA & ANTI-SPOILER:
            1. Gunakan gaya bahasa tutor yang mengalir, alami, hangat, dan panggil 'kamu'.
            2. JANGAN PERNAH menyebut istilah teknis pemrograman (seperti: perulangan, loop, iterasi, array, variabel, counter, range) sebelum siswa menuliskannya sendiri di dalam chat.";

            $jawabanAI = null;

            foreach ($providers as $provider) {
                try {
                    $response = null;

                    if ($provider['type'] === 'groq') {
                        $response = Http::withoutVerifying()->timeout(20)
                            ->withToken($provider['key'])
                            ->post('https://api.groq.com/openai/v1/chat/completions', [
                                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                                'messages' => array_merge(
                                    [['role' => 'system', 'content' => $systemPrompt]],
                                    $formattedHistory,
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
                                    $formattedHistory,
                                    [['role' => 'user', 'content' => $request->question]]
                                ),
                                'temperature' => 0.2,
                                'max_tokens' => 300,
                            ]);

                        if ($response->successful()) {
                            $jawabanAI = $response->json()['choices'][0]['message']['content'] ?? null;
                        }
                    }

                    if (Session::isStarted()) {
                        Session::put($sessionKey, $interaksiKe);
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

    public function analyzeDraftAnswer(Request $request)
    {
        $request->validate([
            'answer' => 'required|string',
            'pertanyaan_id' => 'required|integer', 
        ]);

        $hasMisconception = false;
        $aiFeedback = null;

        try {
            // Mengambil program, pertanyaan teks, dan pembahasan dari database
            $data = DB::table('primm_questions')
                ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
                ->select('primm_questions.pembahasan', 'primm_questions.pertanyaan', 'primms.kode_program')
                ->where('primm_questions.id', $request->pertanyaan_id)
                ->first();

            if (!$data) {
                return back()->with('aiDraftFeedback', [
                    'pertanyaan_id' => (int) $request->pertanyaan_id,
                    'hasMisconception' => (bool) $hasMisconception,
                    'feedback' => $aiFeedback
                ]);
                
            }

            $cleanPembahasan = strip_tags($data->pembahasan);
            $cleanPembahasan = html_entity_decode($cleanPembahasan, ENT_QUOTES, 'UTF-8');
            $cleanPembahasan = preg_replace('/\s+/', ' ', $cleanPembahasan);
            $cleanPembahasan = trim($cleanPembahasan);

            $systemPrompt = "Kamu adalah pengganti fasilitator guru untuk siswa SMK Kelas 10. Tugasmu memeriksa draf jawaban siswa dan memberikan respons sesuai kondisi jawabannya.

            KONTEKS DATA PEMBELAJARAN (DARI DATABASE):
            - Kode Program: \n{$data->kode_program}
            - Pertanyaan Teks: \"{$data->pertanyaan}\"
            - Ekspektasi Pemahaman Benar (Target): \"{$cleanPembahasan}\"

            DATA YANG SEKARANG SEDANG DIKETIK SISWA DI TEXTAREA:
            \"{$request->answer}\"

            ATURAN RESPONS (2 KONDISI, PILIH SALAH SATU):

            KONDISI A - SALAH : Jawaban mengandung kata/frasa yang JELAS BERTENTANGAN dengan konteks kode.
            - Sebutkan HANYA kata yang salah di feedback, lalu arahkan siswa untuk melihat kode program kembali.
            - Format feedback: 'Sepertinya kamu keliru pada bagian '[kata salah]' tersebut, coba lihat lagi kode programnya.'
            - Contoh: 'menyimpan nilai huruf' → kata salah: 'huruf' → {\"hasMisconception\": true, \"feedback\": \"Sepertinya kamu keliru pada bagian 'huruf' tersebut, coba lihat lagi kode programnya.\"}
            - Contoh: 'i untuk menghentikan program' → kata salah: 'menghentikan program' → {\"hasMisconception\": true, \"feedback\": \"Sepertinya kamu keliru pada  'menghentikan program' tersebut, coba lihat lagi kode programnya.\"}

            KONDISI B - TERLALU UMUM: Jawaban tidak salah, tapi hanya 1-2 kata saja dan tidak menjelaskan apapun secara spesifik.
            - Hanya berlaku jika jawaban benar-benar sangat singkat dan tidak informatif.
            - Format feedback: 'Jawabanmu terlalu umum, coba jelaskan lebih spesifik.'
            - Contoh: 'menyimpan' → {\"hasMisconception\": true, \"feedback\": \"Jawabanmu terlalu umum, coba jelaskan lebih spesifik.\"}
            - Contoh: 'untuk i' → {\"hasMisconception\": true, \"feedback\": \"Jawabanmu terlalu umum, coba jelaskan lebih spesifik.\"}

            KONDISI C - LENGKAP: Jawaban sudah spesifik dan tidak ada yang salah.
            - Nilai dan angka dianggap SAMA karena merujuk pada tipe data yang sama.
            - Contoh: 'menyimpan angka dari 0 sampai 4' → {\"hasMisconception\": false, \"feedback\": null}
            - Contoh: 'menyimpan nilai 0 sampai 4' → {\"hasMisconception\": false, \"feedback\": null}

            LOGIKA PENENTUAN KONDISI (PANDUAN UTAMA):
            Bandingkan kata per kata dari ketikan siswa dengan Ekspektasi Pemahaman Benar.

            - Jika TIDAK ADA SATU KATA PUN dari ketikan siswa yang sesuai atau berkaitan dengan ekspektasi → KONDISI A (keliru total)
            - Jika ADA 1 KATA ATAU LEBIH yang sesuai atau berkaitan dengan ekspektasi, namun belum lengkap → KONDISI B (minta lebih detail)  
            - Jika ketikan siswa SUDAH MENCAKUP inti dari ekspektasi secara lengkap → KONDISI C (tidak perlu feedback)

            CONTOH untuk pertanyaan fungsi variabel i:
            - Input: 'i untuk menghentikan program' → tidak ada kata yang sesuai → KONDISI A → {\"hasMisconception\": true, \"feedback\": \"Sepertinya kamu keliru pada bagian 'menghentikan program' tersebut.\"}
            - Input: 'menyimpan nilai angka' → ada kata 'menyimpan nilai' yang mengarah → KONDISI B → {\"hasMisconception\": true, \"feedback\": \"coba jelaskan lebih detail.\"}
            - Input: 'i menyimpan nilai 0 sampai 4 yang berubah tiap putaran' → sudah lengkap → KONDISI C → {\"hasMisconception\": false, \"feedback\": null}
            
            ATURAN TAMBAHAN (MUTLAK):
            1. DILARANG menyebut istilah teknis yang benar seperti: iterator, counter, loop, perulangan, variabel.
            2. DILARANG membocorkan isi ekspektasi pemahaman benar.
            3. Feedback maksimal 2 kalimat pendek.
            4. Respons HANYA dalam format JSON murni tanpa markdown.
            5. Jika jawaban siswa TIDAK mengandung kata yang sesuai ekspektasi, tetap KONDISI A meskipun siswa sudah mengganti kata sebelumnya.
            6. Nama teknologi atau bahasa pemrograman seperti 'python', 'java', 'html' BUKAN sinonim dari kata umum seperti 'belajar', 'memahami', 'menjalankan'.
            7. Evaluasi HANYA berdasarkan ketikan siswa saat ini, ABAIKAN history jawaban sebelumnya.

            ATURAN SINONIM (PENTING):
            - Jangan menganggap salah hanya karena siswa menggunakan kata yang bersinonim atau bermakna sama dengan ekspektasi.
            - Gunakan pemahaman bahasa natural untuk menilai apakah maksud siswa sudah sesuai dengan ekspektasi, bukan kecocokan kata per kata.
            - Contoh umum: jika siswa menulis kata lain yang maknanya sama dengan yang ada di ekspektasi, anggap BENAR.
            - Sinonim hanya berlaku untuk kata yang BENAR-BENAR bermakna sama secara konteks.
            - 'python' BUKAN sinonim dari 'belajar'. 
            - 'menghapus' BUKAN sinonim dari 'menyimpan'.
            - Jika ragu apakah kata siswa bersinonim dengan ekspektasi, pilih KONDISI A

            FORMAT WAJIB:
            {
            \"hasMisconception\": true/false,
            \"feedback\": \"teks feedback atau null\"
            }";

            $apiKey = env('GROQ_API_KEY') ?? env('DEEPSEEK_API_KEY');
            $baseUrl = env('GROQ_API_KEY') 
                ? 'https://api.groq.com/openai/v1/chat/completions' 
                : 'https://api.deepseek.com/chat/completions';
            $model = env('GROQ_API_KEY') 
                ? env('GROQ_MODEL', 'llama-3.3-70b-versatile') 
                : env('DEEPSEEK_MODEL', 'deepseek-chat');

            if (!$apiKey) {
                return response()->json([
                    'pertanyaan_id' => (int) $request->pertanyaan_id,
                    'hasMisconception' => false,
                    'feedback' => null
                ]);
            }

            $response = Http::withoutVerifying()->timeout(15)
                ->withToken($apiKey)
                ->post($baseUrl, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Analisis ketikan saya berdasarkan instruksi."]
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 150,
                ]);

            if ($response->successful()) {
                $aiText = $response->json()['choices'][0]['message']['content'] ?? '{}';
                
                // Log mentah
                Log::info("AI RAW: " . json_encode($aiText));
                
                // Bersihkan semua kemungkinan wrapper
                $cleanJson = preg_replace('/^```json\s*/i', '', trim($aiText));
                $cleanJson = preg_replace('/```$/', '', trim($cleanJson));
                $cleanJson = trim($cleanJson);
                
                // Cari JSON object
                preg_match('/\{.*\}/s', $cleanJson, $matches);
                $jsonStr = $matches[0] ?? '{}';
                
                Log::info("CLEAN JSON: " . $jsonStr);
                
                $result = json_decode($jsonStr, true);
                
                Log::info("DECODE ERROR: " . json_last_error_msg());
                Log::info("RESULT: " . json_encode($result));

                if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                    $hasMisconception = (bool) ($result['hasMisconception'] ?? false);
                    $aiFeedback = $result['feedback'] ?? null;
                }
            } else {
                // TAMBAHKAN INI
                return response()->json([
                    'debug_status' => $response->status(),
                    'debug_body' => $response->body(),
                    'pertanyaan_id' => (int) $request->pertanyaan_id,
                    'hasMisconception' => false,
                    'feedback' => null
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Gagal mendeteksi draf jawaban: " . $e->getMessage());
        }

        return response()->json([
            'pertanyaan_id' => (int) $request->pertanyaan_id,
            'hasMisconception' => (bool) $hasMisconception,
            'feedback' => $aiFeedback
        ]);

    }
}