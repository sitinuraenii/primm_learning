<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class GeminiController extends Controller
{
    public function checkStatus(Request $request, int $pertanyaan_id)
    {
        $userId = Auth::id();

        $feedbackCount = DB::table('ai_interaction_logs')
            ->where('user_id', $userId)
            ->where('primm_question_id', $pertanyaan_id)
            ->count();

        $chatCount = Session::get('chat_count_' . $pertanyaan_id, 0);

        return response()->json([
            'feedbackCount' => $feedbackCount,
            'cekLocked'     => $feedbackCount >= 3,
            'chatCount'     => $chatCount,
            'chatLocked'    => $chatCount >= 3,
        ]);
    }

    public function getHint(Request $request)
    {
        $request->validate([
            'question'      => 'required|string',
            'pertanyaan_id' => 'required|integer',
            'kode_siswa'    => 'nullable|string'
        ]);

        $jawabanAI = null;

        try {
            $data = DB::table('primm_questions')
                ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
                ->select(
                    'primm_questions.pembahasan',
                    'primm_questions.pertanyaan',
                    'primms.tahap',
                    'primms.kode_program'
                )
                ->where('primm_questions.id', $request->pertanyaan_id)
                ->first();

            if (!$data) return back()->with('error', 'Konteks soal tidak ditemukan.');

            $sessionKey  = 'chat_count_' . $request->pertanyaan_id;
            if (empty($request->history)) Session::forget($sessionKey);

            $jumlahChat  = Session::get($sessionKey, 0);
            $interaksiKe = $jumlahChat + 1;

            if ($jumlahChat >= 3) {
                return back()->with('aiResponse',
                    'Kamu sudah mendapat 3 diskusi AI. Coba pelajari materi nya yaa!. 💪'
                );
            }

            $formattedHistory = [];
            foreach ($request->history ?? [] as $msg) {
                $role               = ($msg['role'] === 'bot' || $msg['role'] === 'assistant') ? 'assistant' : 'user';
                $formattedHistory[] = ['role' => $role, 'content' => $msg['content']];
            }
            if (!empty($formattedHistory) && end($formattedHistory)['content'] === $request->question) {
                array_pop($formattedHistory);
            }

            $tahapKonteks = match(strtolower(trim($data->tahap))) {

            'investigate' => "
            TAHAP: INVESTIGATE
            Tujuan: Siswa memahami CARA KERJA kode yang sudah ada —
            alur eksekusi, peran setiap bagian, dan hubungan sebab-akibat di dalamnya.

            Strategi bimbingan:

            LANGKAH 1 — jika ini diskusi pertama atau siswa belum mengarah ke bagian yang relevan:
            Arahkan perhatian siswa ke bagian kode yang paling berkaitan dengan pertanyaan.
            Sebutkan elemen spesifik (misalnya variabel, fungsi, kondisi, perulangan, atau output), tapi jangan bocorkan jawabannya.

            Contoh pola:
            'Coba perhatikan bagian [X] di kode kamu — menurutmu, bagian itu sedang berperan sebagai apa?'
            'Kalau melihat [X], menurutmu bagian itu berhubungan dengan hasil yang muncul?'

            LANGKAH 2 — jika siswa sudah melihat bagian yang benar tapi belum paham prosesnya:
            WAJIB tanggapi dulu inti jawaban siswa dengan menyebut apa yang sudah ia amati.
            Lalu bantu siswa menelusuri jalannya program langkah demi langkah.
            Fokuskan pada perubahan nilai, urutan eksekusi, atau hubungan kode dengan output.

            Contoh pola:
            'Oke, kamu sudah melihat [X] — sekarang coba pikirkan, saat bagian itu dijalankan, apa yang terjadi?'
            'Setelah [X] diproses, menurutmu apa yang berubah?'
            'Menurutmu, bagian mana yang menentukan kenapa hasilnya bisa seperti itu?'
            'Apakah ada sesuatu yang berubah setiap putaran, atau justru tetap sama?'

            LANGKAH 3 — jika siswa sudah mulai memahami polanya:
            WAJIB tanggapi dulu jawaban siswa dengan mengulang inti pemikirannya.
            Lalu dorong siswa merangkai sendiri penjelasan lengkapnya.
            Jangan tambah petunjuk baru.

            Contoh pola:
            'Jadi menurutmu, [ulang inti jawaban siswa].'
            'Nah, dari situ coba simpulkan sendiri, apa yang sebenarnya terjadi di kode kamu.'

            PENTING — berlaku di semua langkah:
            - Jika jawaban siswa meleset atau belum jelas, jangan lanjut ke langkah berikutnya.
            Gali dulu maksudnya: 'Maksud kamu [ulang jawaban siswa] — bisa dijelaskan lebih lanjut?'
            - Jika siswa sudah mengarah ke jawaban yang tepat lebih awal, langsung dorong ke sintesis.
            - Jangan meminta siswa mengubah atau membuat kode baru.
            - Jangan memberi jawaban langsung.
            ",

            'modify' => "
            TAHAP: MODIFY
            Tujuan: Membimbing siswa menemukan sendiri perubahan yang dibutuhkan program agar sesuai dengan target soal.

            Strategi:
            - Berikan SATU petunjuk konseptual berdasarkan kebutuhan baru pada soal.
            - Arahkan siswa berpikir: program sekarang belum bisa apa, lalu apa yang dibutuhkan agar bisa melakukan itu.
            - Fokus pada kebutuhan logika program, bukan langsung pada sintaks.
            - Jangan menyebut solusi atau nama fungsi secara langsung.

            Contoh gaya:
            - Jika diminta agar nilai dimasukkan pengguna:
            'Kalau pengguna harus bisa menentukan nilainya sendiri, menurutmu program perlu bisa melakukan apa dulu?'

            - Jika diminta menghitung total:
            'Kalau setiap nilai ingin digabung menjadi satu hasil akhir, apa yang perlu dilakukan pada setiap nilai yang masuk?'

            - Jika diminta menampilkan hasil akhir:
            'Setelah semua proses selesai, informasi apa yang perlu ditunjukkan kepada pengguna?'

            - Jika diminta mengubah jumlah pengulangan:
            'Kalau banyaknya pengulangan ingin berbeda, bagian mana yang mengatur berapa kali proses berjalan?'

            PENTING:
            - Petunjuk harus menyoroti kebutuhan baru dari soal.
            - Jangan langsung menunjuk sintaks atau memberi jawaban.
            - Bantu siswa menyadari apa yang harus bisa dilakukan program sebelum memikirkan cara menulis kodenya.
            ",

            'make' => "
            TAHAP: MAKE
            Tujuan: Membimbing siswa mulai menyusun program sendiri tanpa memberi solusi langsung.

            Strategi:
            - Berikan SATU petunjuk konseptual berdasarkan kebutuhan paling awal dari soal.
            - Arahkan siswa memikirkan apa yang harus disiapkan atau diketahui program terlebih dahulu.
            - Fokus pada urutan logika: input → proses → hasil.
            - Jangan menyebut sintaks atau memberi kerangka kode.

            Contoh gaya:
            - Jika program perlu menerima beberapa nilai:
            'Sebelum menghitung apa pun, pikirkan dulu data apa saja yang perlu dimasukkan ke program.'

            - Jika program perlu menghitung total:
            'Kalau beberapa nilai ingin digabung menjadi satu jumlah, apa yang perlu dilakukan pada setiap nilai yang masuk?'

            - Jika program perlu menghitung rata-rata:
            'Setelah total didapat, informasi apa lagi yang dibutuhkan agar bisa mencari rata-ratanya?'

            - Jika program perlu mengecek kondisi:
            'Setelah hasil perhitungan didapat, bagaimana program bisa menentukan apakah hasil itu memenuhi syarat tertentu?'

            PENTING:
            - Berikan hanya SATU petunjuk, sesuai langkah yang paling awal dibutuhkan siswa.
            - Jangan langsung menyebut nama fungsi, perintah, atau struktur program.
            - Jangan memberi urutan langkah lengkap; cukup bantu siswa menemukan langkah berikutnya sendiri.
            ",

            default => "
                TAHAP: UMUM
                Tujuan: Bantu siswa memahami konsep inti dari soal.
                Strategi: Sesuaikan bimbingan dengan konteks kode dan soal yang tersedia."
        };

        $systemPrompt = "Kamu adalah Tutor Sokrates untuk siswa SMK Kelas 10.
        Bahasa: santai, hangat, panggil siswa dengan 'kamu'.
        Tugasmu BUKAN menjawab — tugasmu membantu siswa menemukan jawabannya sendiri lewat pertanyaan.

        ════════════════════════════════════
        KONTEKS SESI
        ════════════════════════════════════
        {$tahapKonteks}

        Soal yang dihadapi siswa : \"{$data->pertanyaan}\"
        Konsep target           : \"{$data->pembahasan}\"
        Kode program siswa      :{$data->kode_program}
        Kode Siswa (ditulis di editor): \n{$request->kode_siswa}

        Diskusi saat ini : ke-{$interaksiKe}
        - Diskusi 1-3 : bimbingan bertahap sesuai strategi tahap
        - Diskusi 4   : evaluasi jawaban siswa dari diskusi ke-3.
        Nilai apakah jawaban siswa benar atau meleset, lalu tutup sesi dengan mendorong
        siswa menulis kesimpulan sendiri. Jangan beri pertanyaan baru.

        ════════════════════════════════════
        ATURAN MUTLAK — berlaku di semua tahap & diskusi
        ════════════════════════════════════
        1. DILARANG memberi jawaban, nama fungsi, atau struktur solusi secara langsung.
        2. DILARANG menyebut istilah atau perintah teknis sebelum siswa menyebutnya duluan.
        3. Setiap respons HARUS merujuk ke soal atau kode siswa secara konkret.
        4. Satu respons = satu pertanyaan. Tidak lebih.
        5. Jangan beri pujian berlebihan — tetap fokus ke proses berpikir siswa.
        6. Sebelum melanjutkan ke langkah bimbingan berikutnya, SELALU tanggapi
        jawaban siswa terlebih dahulu — akui apa yang sudah benar, dan gali
        lebih dalam apa yang masih kurang, baru arahkan ke pertanyaan berikutnya.
        7. Strategi bimbingan per diskusi adalah PANDUAN ARAH, bukan script wajib.
        Sesuaikan dengan jawaban siswa — jika siswa sudah mengarah ke konsep
        yang benar lebih awal, tidak perlu menunggu diskusi ke-3 untuk mendorong sintesis.
        8. Jangan pernah mengabaikan isi jawaban siswa. Respons harus selalu
        terhubung langsung dengan apa yang baru saja siswa katakan.
        DILARANG menampilkan potongan kode (code block) dalam bentuk apapun — 
        baik markdown (```), inline code, maupun teks biasa yang menyerupai kode.
        Respons harus dalam kalimat percakapan biasa, bukan kode
        DILARANG memberi pertanyaan yang secara implisit mengisyaratkan jawaban.
        Contoh DILARANG:
        ❌ 'apa yang seharusnya ada di antara `i` dan `range`?' → mengisyaratkan ada kata di antara keduanya
        ❌ 'apakah penulisan `is` sudah benar untuk perulangan?' → mengisyaratkan `is` salah
        ❌ 'coba bandingkan dengan cara biasa menulis for' → mengisyaratkan ada perbedaan spesifik
        
        Contoh yang BENAR:
        ✅ 'Coba baca perbaris kodenya pelan-pelan — menurutmu bagian mana yang terasa berbeda?'

        ════════════════════════════════════
        EVALUASI DI AKHIR DISKUSI KE-3
        ════════════════════════════════════
        Setelah siswa menjawab di diskusi ke-3, evaluasi jawabannya:

        JIKA BENAR atau mendekati benar:
        → 'Pemahamanmu sudah bagus sejauh ini!'

        JIKA MASIH KURANG atau meleset:
        → Coba baca ulang soalnya pelan-pelan atau pahami materi nya'";

            $providers = [];
            if ($key = env('GROQ_API_KEY'))     $providers[] = ['type' => 'groq',     'key' => $key];
            if ($key = env('DEEPSEEK_API_KEY')) $providers[] = ['type' => 'deepseek', 'key' => $key];

            if (empty($providers)) return back()->with('error', 'API Key tidak dikonfigurasi.');

            $jawabanAI = null;
            foreach ($providers as $provider) {
                try {
                    $endpoint = $provider['type'] === 'groq'
                        ? 'https://api.groq.com/openai/v1/chat/completions'
                        : 'https://api.deepseek.com/chat/completions';
                    $model = $provider['type'] === 'groq'
                        ? env('GROQ_MODEL', 'llama-3.3-70b-versatile')
                        : env('DEEPSEEK_MODEL', 'deepseek-chat');

                    $response = Http::withoutVerifying()->timeout(20)
                        ->withToken($provider['key'])
                        ->post($endpoint, [
                            'model'    => $model,
                            'messages' => array_merge(
                                [['role' => 'system', 'content' => $systemPrompt]],
                                $formattedHistory,
                                [['role' => 'user', 'content' => $request->question]]
                            ),
                            'temperature' => 0.5,
                            'max_tokens'  => 120,
                        ]);

                    if ($response->successful()) {
                        $jawabanAI = $response->json()['choices'][0]['message']['content'] ?? null;
                        if ($jawabanAI) {
                            Session::put($sessionKey, $interaksiKe);
                            break;
                        }
                    }
                    Log::warning("getHint: provider {$provider['type']} gagal (Status: {$response->status()})");
                } catch (\Exception $e) {
                    Log::error("getHint error: " . $e->getMessage());
                }
            }

            if ($jawabanAI) {
                if ($interaksiKe >= 3) {
                    $jawabanAI .= "\n\n💪 Kamu sudah mendapat 3 diskusi AI. Coba simpulkan sendiri percakapannya dan pelajari lagi materi nya yaa!";
                }
                return back()->with('aiResponse', $jawabanAI);
            }

            return back()->with('error', 'Tutor sedang sibuk, coba lagi sebentar ya!');

        } catch (\Exception $e) {
            Log::error("getHint sistem error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server AI.');
        }
    }

    public function analyzeDraftAnswer(Request $request)
    {
        $request->validate([
            'answer'        => 'required|string',
            'pertanyaan_id' => 'required|integer',
            'is_manual'     => 'boolean',
            'kode_siswa'    => 'nullable|string'
        ]);

        $hasMisconception = false;
        $aiFeedback       = null;
        $status           = 'benar';

        $isManual      = $request->boolean('is_manual', false);
        $userId = Auth::id();
        $feedbackCount = DB::table('ai_interaction_logs')
            ->where('user_id', $userId)
            ->where('primm_question_id', $request->pertanyaan_id)
            ->count();

        $currentAttempt = $feedbackCount + 1;
        try {
            $data = DB::table('primm_questions')
                ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
                ->select(
                    'primm_questions.pembahasan',
                    'primm_questions.pertanyaan',
                    'primms.tahap',
                    'primms.kode_program'
                )
                ->where('primm_questions.id', $request->pertanyaan_id)
                ->first();

            if (!$data) {
                return response()->json([
                    'pertanyaan_id'    => (int) $request->pertanyaan_id,
                    'status'           => 'benar',
                    'hasMisconception' => false,
                    'feedback'         => null,
                    'feedbackCount'    => $feedbackCount,
                ]);
            }

            $cleanPembahasan = trim(preg_replace('/\s+/', ' ',
                html_entity_decode(strip_tags($data->pembahasan), ENT_QUOTES, 'UTF-8')
            ));

            $kodeSiswa = trim($request->kode_siswa ?? '');
            $tahap     = strtolower(trim($data->tahap));

            $tahapInstruksi = match($tahap) {

            'modify' => "
            === PEMERIKSAAN KODE — TAHAP MODIFY ===
            Ini adalah percobaan ke-{$currentAttempt}.
            Lakukan HANYA jika kode siswa tidak kosong. Jika kosong, lewati seluruh bagian ini.

            LANGKAH 1 — Cek kesalahan sintaks FATAL di Kode Siswa:
            Lolos jika: kode bisa dijalankan secara logis meski ada perbedaan gaya penulisan.
            Gagal HANYA jika: ada keyword Python yang jelas salah eja dan akan menyebabkan error
            (contoh: 'pint' bukan 'print', 'fro' bukan 'for').
            Jika gagal → {\"status\": \"salah\", \"feedback\":
                \"Bagian '[HANYA kata yang salah]' belum tepat — coba periksa kembali penulisan di baris tersebut.\"}
            STOP.

            LANGKAH 2 — Cek relevansi modifikasi:
            Lolos jika: kode siswa mengandung setidaknya SATU perubahan yang mengarah ke perintah soal,
            meski belum sempurna atau belum lengkap.
            Gagal HANYA jika: kode siswa identik dengan kode template (tidak ada perubahan apapun).
            Jika gagal → {\"status\": \"kurang_lengkap\", \"feedback\":
                \"Kodenya belum berubah dari template — coba perhatikan lagi bagian mana yang perlu dimodifikasi.\"}
            STOP.

            Jika lolos Langkah 1 dan 2 → ABAIKAN kode siswa, lanjut evaluasi TEKS JAWABAN dengan Kondisi A-D.
            Fokus penilaian: apakah siswa bisa menjelaskan FUNGSI dari perubahan yang ia buat.",

            'make' => "
            === PEMERIKSAAN KODE — TAHAP MAKE ===
            Ini adalah percobaan ke-{$currentAttempt}.
            Lakukan HANYA jika kode siswa tidak kosong. Jika kosong, lewati seluruh bagian ini.

            LANGKAH 1 — Cek kesalahan sintaks FATAL di Kode Siswa:
            Lolos jika: kode bisa dijalankan secara logis meski belum sempurna.
            Gagal HANYA jika: ada keyword Python yang jelas salah eja dan akan menyebabkan error.
            Jika gagal → {\"status\": \"salah\", \"feedback\":
                \"Bagian '[HANYA kata yang salah]' belum tepat — coba periksa kembali penulisan di baris tersebut.\"}
            STOP.

            LANGKAH 2 — Cek relevansi kode:
            Lolos jika: kode siswa mencoba memenuhi minimal satu ketentuan soal, meski belum sempurna.
            Gagal HANYA jika: kode sama sekali tidak ada hubungannya dengan perintah soal.
            Jika gagal → {\"status\": \"salah\", \"feedback\":
                \"Program belum menjawab ketentuan soal — coba perhatikan lagi apa yang diminta.\"}
            STOP.

            Jika lolos Langkah 1 dan 2 → ABAIKAN kode siswa, lanjut evaluasi TEKS JAWABAN dengan Kondisi A-D.
            Fokus penilaian: apakah siswa bisa menjelaskan TUJUAN dan LOGIKA dari kode yang ia buat.",
            default => ""
        };

            $systemPrompt = "Kamu adalah pemeriksa jawaban sekaligus pembimbing siswa yang bertugas untuk mengarahkan.
            Periksa jawaban siswa dan respons sesuai kondisi di bawah.

            KONTEKS:
            - Tahap Belajar: {$data->tahap}
            - Kode Awal (template soal): \n{$data->kode_program}
            - Kode Siswa (ditulis di editor): \n{$kodeSiswa}
            - Pertanyaan: \"{$data->pertanyaan}\"
            - Target Pemahaman: \"{$cleanPembahasan}\"
            - Jawaban siswa: \"{$request->answer}\"

            {$tahapInstruksi}

            === CARA MENILAI JAWABAN ===
            Gunakan Target Pemahaman hanya sebagai ARAH, bukan checklist kata per kata

            PRINSIP UTAMA — BUKTI PEMAHAMAN:
            Siswa dianggap PAHAM jika jawaban mereka membuktikan pemahaman dengan CARA APAPUN:
            - Definisi konseptual yang tepat
            - Simulasi/trace konkret yang akurat dan mencakup semua kasus
            - Penjelasan sebab-akibat yang logis
            - Contoh output yang benar
            Bentuk penyampaian tidak dinilai. Yang dinilai adalah AKURASI ISI.

            Tanyakan pada diri sendiri:
            - Apakah konsepnya bertentangan dengan kode? → SALAH
            - Apakah arahnya benar tapi masih permukaan atau belum tuntas? → KURANG LENGKAP,
            arahkan siswa menjelaskan lebih dalam sesuai konteks pertanyaan tanpa bocor jawaban
            - Apakah inti konsep sudah tertangkap dengan baik? → BENAR

            === 4 KONDISI RESPONS ===

            KONDISI A — SALAH:
            Jawaban mengandung konsep yang jelas bertentangan dengan kode dan pertanyaan atau sama sekali tidak nyambung.
            → JANGAN sebutkan bagian mana yang benar.
            → Identifikasi HANYA kata/frasa spesifik yang keliru, bukan seluruh kalimat jawaban siswa.
            Contoh: jika jawaban \"menyimpan nilai huruf\" dan yang salah hanya \"huruf\",
            maka yang disebut keliru hanya \"huruf\", bukan \"menyimpan nilai huruf\".
            → Identifikasi sendiri bagian kode yang berkaitan dengan kekeliruan,
            lalu arahkan siswa ke sana tanpa mengisyaratkan jawaban yang benar.
            → {\"status\": \"salah\", \"feedback\": \"Bagian '[HANYA kata/frasa yang keliru]' belum tepat — coba perhatikan lagi '[bagian kode yang relevan]' di program tersebut.\"}

            KONDISI B — BENAR TAPI TIDAK LENGKAP:
            Arah jawaban sudah benar tapi masih permukaan, kurang detail, atau belum tuntas.
            → JANGAN sebutkan bagian mana yang sudah benar.
            → JANGAN bocorkan bagian yang kurang.
            → Arahkan siswa menjelaskan lebih spesifik sesuai konteks pertanyaan.
            → {\"status\": \"kurang_lengkap\", \"feedback\": \"Sudah mengarah, tapi coba jelaskan lebih detail — [arahan spesifik dalam bentuk KALIMAT PERINTAH, bukan pertanyaan].\"}

            KONDISI C — BENAR DAN LENGKAP:
            Jawaban sudah mencakup inti target pemahaman secara spesifik.
            → {\"status\": \"benar\", \"feedback\": \"Pemahamanmu sudah bagus! tingkatkan lagi! 🎉\"}

            KONDISI D — KOSONG ATAU TERLALU SINGKAT:
            → {\"status\": \"kurang_lengkap\", \"feedback\": \"Tulis dulu pendapatmu selengkapnya, baru bisa diperiksa.\"}

           === KONDISI KHUSUS (Percobaan saat ini: {$currentAttempt}) ===
            Jika ini pemeriksaan ke-3 DAN hasilnya BUKAN benar (Kondisi A, B, atau D):
            → Tetap tulis feedback seperti biasa, lalu tambahkan kalimat penutup:
            \"Ini sudah percobaan terakhir — coba pelajari lagi materinya ya, kamu pasti bisa menyimpulkan sendiri.\"
            Jika hasilnya benar (Kondisi C), respons normal tanpa kalimat penutup.

            === ATURAN MUTLAK ===
            1. DILARANG bocorkan jawaban benar atau bagian yang kurang dari Target Pemahaman: \"{$cleanPembahasan}\".
            2. DILARANG menyebut istilah teknis (loop, variabel, counter, iterator) sebelum siswa menyebutnya.
            3. DILARANG memberi pertanyaan balik.
            Contoh arahan yang benar:
            ✅ \'coba jelaskan apa yang terjadi pada nilai i di setiap putaran.\'
            ❌ \'bagaimana nilai i berubah di setiap putaran?\'
            4. DILARANG bilang 'belum tepat' jika arah jawaban sudah benar — gunakan 'belum lengkap' atau 'coba jelaskan lebih detail'.
            5. Feedback maksimal 2 kalimat singkat dan natural (kalimat penutup percobaan ke-3 tidak dihitung).
            6. Sinonim bermakna sama dianggap BENAR.
            7. Nilai dan angka bermakna sama dianggap BENAR.
            8. Respons HANYA JSON murni tanpa markdown.
            9. Jika siswa menjelaskan dengan contoh atau simulasi konkret yang akurat,
            nilainya SAMA dengan penjelasan konseptual. Jangan minta siswa
            mengulang dengan kata-kata yang lebih 'formal' atau 'abstrak'.

            FORMAT WAJIB:
            {\"status\": \"salah|kurang_lengkap|benar\", \"feedback\": \"teks atau null\"}";

            $providers = [];
            if ($key = env('GROQ_API_KEY')) {
                $providers[] = [
                    'key'   => $key,
                    'url'   => 'https://api.groq.com/openai/v1/chat/completions',
                    'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                ];
            }
            if ($key = env('DEEPSEEK_API_KEY')) {
                $providers[] = [
                    'key'   => $key,
                    'url'   => 'https://api.deepseek.com/chat/completions',
                    'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
                ];
            }

            $response = null;
            foreach ($providers as $provider) {
                try {
                    $resp = Http::withoutVerifying()->timeout(15)
                        ->withToken($provider['key'])
                        ->post($provider['url'], [
                            'model'       => $provider['model'],
                            'messages'    => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user',   'content' => 'Analisis jawaban saya sekarang.'],
                            ],
                            'temperature' => 0.1,
                            'max_tokens'  => 150,
                        ]);

                    if ($resp->successful()) { $response = $resp; break; }
                    Log::warning("analyzeDraftAnswer: {$provider['url']} gagal (Status: {$resp->status()})");
                } catch (\Exception $e) {
                    Log::error("analyzeDraftAnswer error: " . $e->getMessage());
                }
            }

            if ($response) {
                $aiText = $response->json()['choices'][0]['message']['content'] ?? '{}';
                $clean  = trim(preg_replace('/```$/', '', preg_replace('/^```json\s*/i', '', trim($aiText))));
                preg_match('/\{.*\}/s', $clean, $matches);
                $result = json_decode($matches[0] ?? '{}', true);

                Log::info("analyzeDraftAnswer result: " . json_encode($result));

                if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                    $status     = $result['status']  ?? 'salah';
                    $aiFeedback = $result['feedback'] ?? null;

                    if ($status === 'benar') {
                        $hasMisconception = false;

                        DB::table('ai_interaction_logs')->insert([
                            'user_id'             => $userId,
                            'primm_question_id'   => $request->pertanyaan_id,
                            'student_answer_text' => $request->answer,
                            'ai_feedback'         => $aiFeedback ?? '',
                            'is_valid'            => true,  // ← bedanya di sini
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);

                    } else {
                        $hasMisconception = true;

                        DB::table('ai_interaction_logs')->insert([
                            'user_id'             => $userId,
                            'primm_question_id'   => $request->pertanyaan_id,
                            'student_answer_text' => $request->answer,
                            'ai_feedback'         => $aiFeedback ?? '',
                            'is_valid'            => false,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }

                    // Selalu update feedbackCount setelah insert
                    $feedbackCount = DB::table('ai_interaction_logs')
                        ->where('user_id', $userId)
                        ->where('primm_question_id', $request->pertanyaan_id)
                        ->count();
                }
            }

        } catch (\Exception $e) {
            Log::error("Gagal analyzeDraftAnswer: " . $e->getMessage());
        }

        return response()->json([
            'pertanyaan_id'    => (int) $request->pertanyaan_id,
            'status'           => $status,
            'hasMisconception' => (bool) $hasMisconception,
            'feedback'         => $aiFeedback,
            'feedbackCount'    => $feedbackCount,
        ]);
    }
}