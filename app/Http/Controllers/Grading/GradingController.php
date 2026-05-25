<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers\Grading; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Course;        
use App\Models\User;           
use App\Models\StudentAnswer;  
use App\Models\CourseProgress;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function index()
    {
        $totalMateriTersedia = Course::count(); 

        $students = User::where('role', 'siswa')
            ->withCount('courseProgress') 
            ->get()
            ->map(function ($user) use ($totalMateriTersedia) {
                
                // 1. Ambil semua materi (Course) yang progresnya sudah dicatat untuk siswa ini
                $coursesDone = Course::whereHas('courseProgress', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->get();

                // 2. Hitung total skor per masing-masing materi
                $materiScores = $coursesDone->map(function ($course) use ($user) {
                    $skorPerMateri = \App\Models\StudentAnswer::where('user_id', $user->id)
                        ->whereHas('question.primm', function($q) use ($course) {
                            $q->where('course_id', $course->id);
                        })
                        ->sum('skor');

                    return [
                        'title' => $course->title,
                        'total_score' => $skorPerMateri
                    ];
                });

                // 3. Filter: Mengabaikan materi yang judulnya mengandung "Pengenalan"
                $filteredMaterials = $materiScores->filter(function ($m) {
                    $bukanPengenalan = stripos($m['title'], 'Pengenalan') === false;
                    $sudahDinilai = $m['total_score'] > 0; 

                    return $bukanPengenalan && $sudahDinilai;
                });

                $jumlahMateriDinilai = $filteredMaterials->count();
                $rataRata = $jumlahMateriDinilai > 0 
                    ? round($filteredMaterials->avg('total_score'), 1) 
                    : 0;

                $faseCount = \App\Models\StudentAnswer::where('user_id', $user->id)
                    ->with('question.primm')
                    ->get()
                    ->map(fn($a) => $a->question->primm->tahap ?? null)
                    ->filter()->unique()->count();

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'materi_selesai' => $user->course_progress_count . ' / ' . $totalMateriTersedia,
                    'total_fase' => $faseCount . ' / 5',
                    'materi_dinilai' => $jumlahMateriDinilai,
                    'rata_rata_nilai' => $rataRata 
                ];
            });

        return Inertia::render('guru/nilai/daftarNilai', ['students' => $students]);
    }

    public function show($userId, $courseId)
    {
   
        $student = User::findOrFail($userId);
        $course = Course::findOrFail($courseId);

        $answers = StudentAnswer::with(['question.primm']) 
        ->where('user_id', $userId)
        ->whereHas('question.primm', function($q) use ($courseId) {
            $q->where('course_id', $courseId); 
        })
        ->get();

        $aiLogs = DB::table('ai_interaction_logs')
        ->join('primm_questions', 'ai_interaction_logs.primm_question_id', '=', 'primm_questions.id')
        ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
        ->where('ai_interaction_logs.user_id', $userId)
        ->where('primms.course_id', $courseId)
        ->select(
            'ai_interaction_logs.id',
            'ai_interaction_logs.primm_question_id',
            'ai_interaction_logs.student_answer_text',
            'ai_interaction_logs.ai_feedback',
            'ai_interaction_logs.created_at',
            'primm_questions.pertanyaan',
            'primms.tahap',
        )
        ->orderBy('ai_interaction_logs.created_at', 'asc')
        ->get();

        return Inertia::render('guru/nilai/detailJawaban', [
        'student' => $student,
        'answers' => $answers,
        'currentMateri' => $course->title,
        'aiLogs'       => $aiLogs, 
        ]);
    }

    public function bulkUpdate(Request $request, $userId)
    {
        $scores = $request->input('scores', []);
        $feedbacks = $request->input('feedbacks', []);

        $allAnswerIds = array_unique(array_merge(array_keys($scores), array_keys($feedbacks)));

        foreach ($allAnswerIds as $answerId) {
            $dataToUpdate = [];

            if (isset($scores[$answerId])) {
                $dataToUpdate['skor'] = $scores[$answerId];
            }

            if (isset($feedbacks[$answerId])) {
                $dataToUpdate['feedback'] = $feedbacks[$answerId];
            }

            if (!empty($dataToUpdate)) {
                \App\Models\StudentAnswer::where('id', $answerId)
                    ->where('user_id', $userId) 
                    ->update($dataToUpdate);
            }
        }

        return redirect()->route('grading.index');
    }

    public function listCourses($userId)
    {
        $student = User::findOrFail($userId);

        $courses = Course::whereHas('courseProgress', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->get()->map(function ($course) use ($userId) {

            $jawabanMateri = StudentAnswer::where('user_id', $userId)
                ->whereHas('question.primm', function($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->with('question.primm')
                ->get();

            $totalSkor = $jawabanMateri->sum('skor');

            $faseCount = $jawabanMateri->map(fn($a) => $a->question->primm->tahap ?? null)
                ->filter()
                ->unique()
                ->count();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'total_score' => $totalSkor,
                'fase_count' => $faseCount . ' / 5', 
            ];
        });

        return Inertia::render('guru/nilai/daftarMateriSiswa', [
            'student' => $student,
            'materials' => $courses,
        ]);
    }

    public function aiInteractionLogs($userId)
{
    $student = User::findOrFail($userId);

    $logs = DB::table('ai_interaction_logs')
        ->join('primm_questions', 'ai_interaction_logs.primm_question_id', '=', 'primm_questions.id')
        ->join('primms', 'primm_questions.primm_id', '=', 'primms.id')
        ->join('courses', 'primms.course_id', '=', 'courses.id')
        ->where('ai_interaction_logs.user_id', $userId)
        ->select(
            'ai_interaction_logs.id',
            'ai_interaction_logs.primm_question_id',
            'ai_interaction_logs.student_answer_text',
            'ai_interaction_logs.ai_feedback',
            'ai_interaction_logs.is_valid',
            'ai_interaction_logs.created_at',
            'primm_questions.pertanyaan',
            'primms.tahap',
            'courses.title as course_title',
            'courses.id as course_id',
        )
        ->orderBy('ai_interaction_logs.created_at', 'desc')
        ->get();

    return Inertia::render('guru/nilai/aiInteractionLogs', [
        'student' => $student,
        'logs'    => $logs,
    ]);
}

public function resetAiInteraction($userId, $pertanyaan_id)
    {
        DB::table('ai_interaction_logs')
            ->where('user_id', $userId)
            ->where('primm_question_id', $pertanyaan_id)
            ->delete();

        return back()->with('success', 'Percobaan siswa berhasil direset.');
    }
}