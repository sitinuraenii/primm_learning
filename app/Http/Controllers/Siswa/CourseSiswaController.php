<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Redirect;

class CourseSiswaController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('id', 'asc')->get();
        
        $completedCourseIds = DB::table('course_progress')
            ->where('user_id', Auth::id()) 
            ->pluck('course_id')
            ->toArray();

        return Inertia::render('siswa/courseSiswa/listCourse', [
            'courses' => $courses,
            'completedCourseIds' => $completedCourseIds
        ]);
    }

    public function show($id)
    {
       $course = Course::with(['primms.questions'])->findOrFail($id);

       $courseData = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'link' => $course->link,
            'file' => $course->file,
            'link_drive' => $course->link_drive, 
        ];

        if (str_contains(strtolower($course->title), 'pengenalan')) {
            return Inertia::render('siswa/courseSiswa/showCourse', ['course' => $courseData]);
        }

        $primmData = $course->primms->groupBy('tahap');

        return Inertia::render('siswa/courseSiswa/showPrimm', [
            'course' => $courseData,
            'primm' => $primmData
        ]);
    }

    public function showPrimm(int $id, string $step)
{
    $course = Course::with(['primms.questions'])->findOrFail($id);
    $primmData = $course->primms->groupBy('tahap');
    $userId = Auth::id();

    $stepsToFetch = [$step];
    if (in_array($step, ['investigate'])) {
        $stepsToFetch[] = 'predict';
    }

    $existingAnswersData = \App\Models\StudentAnswer::where('user_id', $userId)
        ->whereHas('question.primm', function($query) use ($id, $stepsToFetch) {
            $query->where('course_id', $id)
                  ->whereIn('tahap', $stepsToFetch); 
        })
        ->get()
        ->keyBy('primm_question_id'); 

    $isAllFinished = DB::table('course_progress')
        ->where('user_id', $userId)
        ->where('course_id', $id)
        ->exists();    

    return Inertia::render('siswa/courseSiswa/showPrimm', [
        'course' => [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'link' => $course->link, 
            'link_drive' => $course->link_drive,
        ],
        'primm' => $primmData,
        'activeStepFromUrl' => $step, 
        'existingAnswers' => $existingAnswersData, 
        'isAllFinished' => $isAllFinished, 
        'hintUrl' => url('/hint'),
        'aiDraftFeedback' => session('aiDraftFeedback')
    ]);
}

    public function saveProgress(Request $request)
{
    $request->validate([
        'jawaban'    => 'required|array',
        'tahap'      => 'required|string',
        'kode_siswa' => 'nullable|string'
    ]);

    try {
        $userId = Auth::id();
        $kodeSiswa = $request->kode_siswa;
        $tahapAktif = $request->tahap;

        DB::transaction(function () use ($request, $userId, $kodeSiswa, $tahapAktif) {

            $isCodingStep = in_array($tahapAktif, ['modify', 'make']);

            foreach ($request->input('jawaban') as $questionId => $teks) {

                $dataToUpdate = [
                    'jawaban_siswa' => $teks ?? '',
                ];

                if ($isCodingStep) {
                    $dataToUpdate['kode_program'] = $kodeSiswa;
                }

                \App\Models\StudentAnswer::updateOrCreate(
                    [
                        'user_id' => $userId, 
                        'primm_question_id' => $questionId
                    ],
                    $dataToUpdate
                );
            }
        });

        return back()->with('success', 'Progress berhasil disimpan!');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

    public function listPrimm($id)
{
    $course = Course::findOrFail($id);
    $userId = Auth::id();
    $steps = ['predict', 'run', 'investigate', 'modify', 'make'];
    $progress = [];

    foreach ($steps as $step) {
        // 1. Hitung total soal yang tersedia di tahap ini
        // Asumsi: Anda punya relasi 'questions' di model Primm
        $totalQuestionsCount = \App\Models\PrimmQuestion::whereHas('primm', function($query) use ($id, $step) {
                $query->where('course_id', $id)->where('tahap', $step);
            })->count();

        // 2. Hitung jumlah soal unik yang sudah dijawab siswa di tahap ini
        $answeredCount = \App\Models\StudentAnswer::where('user_id', $userId)
            ->whereHas('question.primm', function($query) use ($id, $step) {
                $query->where('course_id', $id)->where('tahap', $step);
            })
            ->distinct('primm_question_id')
            ->count('primm_question_id');

        // 3. Tahap dianggap SELESAI jika jumlah jawaban >= jumlah soal
        // Dan pastikan jumlah soal tidak nol (untuk menghindari false positive)
        $progress[$step] = ($totalQuestionsCount > 0) && ($answeredCount >= $totalQuestionsCount);
    }

    // Cek apakah seluruh course sudah selesai secara keseluruhan
    $isAllFinished = DB::table('course_progress')
        ->where('user_id', $userId)
        ->where('course_id', $id)
        ->exists();

    return Inertia::render('siswa/courseSiswa/listPrimm', [
        'course' => $course,
        'progress' => $progress, 
        'isAllFinished' => $isAllFinished
    ]);
}

    public function complete($id)
    {
        try {
            $userId = Auth::id();
            $course = Course::with('primms')->findOrFail($id);

            $hasPrimm = $course->primms->count() > 0;

            if ($hasPrimm) {
                
                $steps = ['predict', 'run', 'investigate', 'modify', 'make'];
                
                foreach ($steps as $step) {
                    $isStepDone = \App\Models\StudentAnswer::where('user_id', $userId)
                        ->whereHas('question.primm', function($query) use ($id, $step) {
                            $query->where('course_id', $id)->where('tahap', $step);
                        })->exists();

                    if (!$isStepDone) {
                        return back()->with('error', "Tahap " . ucfirst($step) . " belum diselesaikan.");
                    }
                }
            }

            DB::table('course_progress')->updateOrInsert(
                ['user_id' => $userId, 'course_id' => $id],
                ['created_at' => now(), 'updated_at' => now()]
            );

        return back()->with('success', 'Selamat! Materi telah selesai.');
        
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses penyelesaian: ' . $e->getMessage());
        }
    }
}
