import React, { useState, useEffect, useRef, useCallback } from "react";
import { Head, router, Link, usePage } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Code2, Play, Volume2, VolumeX, BookOpen, ExternalLink, Search, X, Lightbulb } from "lucide-react";
import CodeMirror from '@uiw/react-codemirror';
import { python } from '@codemirror/lang-python';
import { vscodeDark } from '@uiw/codemirror-theme-vscode';
import ChatAI from '../../chatAi';
import { useVoice } from '@/hooks/useVoice';

interface Question { 
    id: number; 
    pertanyaan: string; 
    pembahasan: string; 
    student_answers?: Array<{
        jawaban_siswa: string;
        kode_program: string;
    }>;
}
interface PrimmActivity { id: number; tahap: string; gambar: string | null; kode_program: string | null; questions: Question[]; }
interface PrimmData { [key: string]: PrimmActivity[] | undefined; }
interface Course { id: number; title: string; description: string; link?: string; file?: string; link_drive?: string; }
interface Props { course: Course; primm: PrimmData; hintUrl: string; activeStepFromUrl?: string; existingAnswers?: { [key: number]: string }; isAllFinished: boolean; }

export default function ShowPrimm({ course, primm, activeStepFromUrl, existingAnswers, isAllFinished, hintUrl }: Props) {
    const steps = ["predict", "run", "investigate", "modify", "make"];
    const initialStep = activeStepFromUrl ? steps.indexOf(activeStepFromUrl.toLowerCase()) : 0;
    const [currentStep, setCurrentStep] = useState<number>(initialStep !== -1 ? initialStep : 0);
    const [currentActivityIdx, setCurrentActivityIdx] = useState(0);
    const [aiDraftFeedback, setAiDraftFeedback] = useState<{
        pertanyaan_id: number;
        hasMisconception: boolean;
        feedback: string | null;
    } | null>(null); 
    const [activeQuestionIdx, setActiveQuestionIdx] = useState(0);
    const [isBlockSubmitted, setIsBlockSubmitted] = useState(false); 
    const [subView, setSubView] = useState<'aktivitas' | 'materi'>('aktivitas');
    const [answers, setAnswers] = useState<{ [key: number]: string }>(existingAnswers || {});
    const [editorCodes, setEditorCodes] = useState<{ [key: number]: string }>({});
    const [openExplanations, setOpenExplanations] = useState<number[]>([]);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [pyodide, setPyodide] = useState<any>(null);
    const [pyOutput, setPyOutput] = useState<{ [key: number]: string }>({});
    const activeStep = steps[currentStep];
    const activities = primm[activeStep] || [];
    const act = activities[currentActivityIdx];
    const [isRunning, setIsRunning] = useState<number | null>(null);
    const { speak } = useVoice();
    const [isMuted, setIsMuted] = useState(false);
    const isMutedRef = useRef(isMuted);
    const [isJustSubmitted, setIsJustSubmitted] = useState(false);
    const speechIdRef = useRef(0);
    const [isReviewMode, setIsReviewMode] = useState(
        new URLSearchParams(window.location.search).get('review') === 'true'
    );
    
    const qActive = act?.questions[activeQuestionIdx];
    const debounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const currentAiFeedback = aiDraftFeedback?.feedback
    ? aiDraftFeedback
    : null;
    const sendDraftToLaravel = async (textValue: string, questionId: number, isManual: boolean = false, kodeSiswa: string = '') => {
        console.log('sendDraftToLaravel dipanggil, questionId:', questionId);
        
        if (!textValue || textValue.trim().length < 5 || isReviewMode || isBlockSubmitted) return;
        const allowedSteps = ['investigate', 'modify', 'make'];
        if (!allowedSteps.includes(activeStep?.toLowerCase())) return;

        try {
            const getCsrfToken = () => {
            const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : '';
            };

            const res = await fetch(`/tasks/${questionId}/analyze-draft`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    answer: textValue,
                    pertanyaan_id: questionId,
                   kode_siswa: kodeSiswa,
                    is_manual: isManual,
                })
            });

            const data = await res.json();
            console.log('Response data:', data);

            if (data.feedbackCount !== undefined) setCekCount(data.feedbackCount);

            setAiDraftFeedback({
                ...data,
                _ts: Date.now()
            });

        } catch (e) {
            console.error('Radar AI error:', e);
        }
    };

    const pendingDraftRef = useRef<{ value: string; qId: number } | null>(null);

    const handleTextareaChange = (qId: number, value: string) => {
        setAnswers(prev => ({ ...prev, [qId]: value }));
    };

    const handleTextareaBlur = (qId: number, value: string) => {
       setAnswers(prev => ({ ...prev, [qId]: value }));
    };

    const handleCodeEditorChange = (actIdx: number, qId: number, currentCode: string) => {
        setEditorCodes(prev => ({ ...prev, [actIdx]: currentCode }));
    };

    useEffect(() => {
        setAiDraftFeedback(null);
    }, [activeQuestionIdx, activeStep]);

    const stripHtml = (html: string) => {
        if (!html) return "";

        let cleanText = html.replace(/<\/?[^>]+(>|$)/g, "");

        const decodedText = document.createElement("textarea");
        decodedText.innerHTML = cleanText;
        cleanText = decodedText.value;

        return cleanText;
    };

    const renderEmbedMedia = (url: string): string => {
        if (!url) return '';
        if (url.includes('<iframe')) return url.replace('<iframe', '<iframe class="w-full aspect-video rounded-[30px] shadow-lg border-0"');
        let videoId = '';
        if (url.includes('v=')) videoId = url.split('v=')[1]?.split('&')[0];
        else if (url.includes('youtu.be/')) videoId = url.split('youtu.be/')[1]?.split('?')[0];
        if (videoId) return `<iframe width="100%" height="400" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen class="rounded-[30px] shadow-lg w-full aspect-video border-0"></iframe>`;
        return `<div class="p-10 bg-gray-50 rounded-[30px] text-center border-2 border-dashed border-gray-200"><p class="text-xs font-bold text-gray-400 uppercase mb-2">Media Eksternal</p><a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline font-bold flex items-center justify-center gap-2">Buka Media di Tab Baru <ExternalLink size={14} /></a></div>`;
    };

    const getPastPredictAnswer = () => {
        if (activeStep !== 'run' || !primm['predict']) return null;

        const predictActivity = primm['predict'][currentActivityIdx];
        if (!predictActivity || !predictActivity.questions.length) return null;

        const firstPredictQId = predictActivity.questions[0].id;
        const savedData = (existingAnswers as any)?.[firstPredictQId];

        if (!savedData) return null;

        return typeof savedData === 'object' ? savedData.jawaban_siswa : savedData;
    };

    const pastPredictAnswer = getPastPredictAnswer();

    const { pause, resume } = useVoice();

    const toggleMute = () => {
    const synth = window.speechSynthesis;

    if (isMuted) {
        setIsMuted(false);
        if (synth.speaking || synth.paused) {
            synth.resume();
        } else {
            const teksPembahasan = act?.questions[activeQuestionIdx]?.pembahasan;
            if (activeStep === 'investigate' && teksPembahasan) {
                speak(teksPembahasan);
            }
        }
    } else {
        setIsMuted(true);
        synth.pause();
    }
    };

    useEffect(() => {

    const firstQuestionInStep = activities[0]?.questions[0];
    if (!firstQuestionInStep) return;

    const savedData = (existingAnswers as any)?.[firstQuestionInStep.id];
    const currentLocalAnswer = answers[firstQuestionInStep.id];
    const hasAnswer = (savedData !== undefined && savedData !== null) || (currentLocalAnswer && currentLocalAnswer.trim() !== "");

    if (!isReviewMode && !hasAnswer) {
        const currentIndex = steps.indexOf(activeStep);
        const prevStep = currentIndex > 0 ? steps[currentIndex - 1] : null;
        
        let pesan = "";
        
        if (activeStep === 'predict') {
            pesan = "Kamu masuk pada halaman memprediksi program. Silakan amati kode yang ada dan jawab pertanyaan prediksi dengan jelas.";
        } 
        else if (activeStep === 'run') {
            pesan = "Sekarang mari kita buktikan dengan menjalankan kodenya dengan menekan tombol Run.";
        }
        else if (activeStep === 'investigate') {
            pesan = `Sekarang mari kita bedah logikanya pada tahap ${activeStep}.`;
        }
        else if (prevStep) {
            pesan = `Kalian keren bisa menyelesaikan tahap ${prevStep}. Sekarang tantangan berikutnya tahap ${activeStep}. Semangat!`;
        }

        if (pesan) {
            const timer = setTimeout(() => {
                if (activeStep === steps[currentIndex]) {
                    window.speechSynthesis.cancel(); 

                    const pesanSuara = pesan.replace(/make/gi, "meyk");

                    speak(pesanSuara); 
                    
                    if (isMuted) {
                        setTimeout(() => window.speechSynthesis.pause(), 10);
                    }
                }
            }, 300);

            return () => clearTimeout(timer);
        }
    }

    if (hasAnswer) {
        window.speechSynthesis.cancel();
    }

}, [activeStep, isReviewMode]);

    useEffect(() => {
    const teksPembahasan = act?.questions[activeQuestionIdx]?.pembahasan;
    const synth = window.speechSynthesis;

    synth.cancel();

    if (isReviewMode && activeStep === 'investigate' && teksPembahasan) {
        if (isMuted) return; 

        speechIdRef.current += 1;
        const currentId = speechIdRef.current;

        const timer = setTimeout(() => {
            if (currentId === speechIdRef.current) {
                console.log("Membaca teks:", teksPembahasan);
                speak(teksPembahasan);
            }
        }, 400);

        return () => {
            clearTimeout(timer);
            synth.cancel(); 
        };
    }

    return () => {
        synth.cancel();
    };
}, [activeQuestionIdx, isReviewMode, activeStep]);

    useEffect(() => {
        if (activeStepFromUrl) {
            const index = steps.indexOf(activeStepFromUrl.toLowerCase());
            if (index !== -1) {
                setCurrentStep(index);
                setCurrentActivityIdx(0);
                setActiveQuestionIdx(0);
                setSubView('aktivitas');
                setPyOutput({});
                setOpenExplanations([]);
            }
        }
    }, [activeStepFromUrl]); 

    useEffect(() => {
        const currentAct = activities[currentActivityIdx];
        if (!currentAct) return;

        const initialCodes: { [key: number]: string } = {};
        
        activities.forEach((item: PrimmActivity, idx: number) => {
            const firstQId = item.questions[0]?.id;
            const savedData = (existingAnswers as any)?.[firstQId];

            if (['modify', 'make'].includes(item.tahap) && savedData?.kode_program) {
                initialCodes[idx] = savedData.kode_program; 
            } else {

                initialCodes[idx] = item.kode_program || "";
            }
        });

        const initialAnswers: { [key: number]: string } = {};
        if (existingAnswers) {
            Object.keys(existingAnswers).forEach((key) => {
                const data = (existingAnswers as any)[key];
                if (typeof data === 'object' && data !== null) {
                    initialAnswers[parseInt(key)] = data.jawaban_siswa || "";
                } else {
                    initialAnswers[parseInt(key)] = data;
                }
            });

            const currentQuestionIds = currentAct.questions.map(q => q.id);
            const isAlreadyAnswered = currentQuestionIds.length > 0 && 
                currentQuestionIds.every(id => !!(existingAnswers as any)[id]);

            setIsBlockSubmitted(isAlreadyAnswered);

            if (isAlreadyAnswered) {
                setOpenExplanations(currentQuestionIds);
            } else {
                setOpenExplanations([]);
            }
        }

        setEditorCodes(initialCodes);
        setAnswers(initialAnswers);

    }, [activeStep, currentActivityIdx, activities, existingAnswers]);


    useEffect(() => {
        async function initPy() {
            if ((window as any).loadPyodide && !pyodide) {
                try {
                    const py = await (window as any).loadPyodide({ indexURL: "https://cdn.jsdelivr.net/pyodide/v0.25.0/full/" });
                    setPyodide(py);
                } catch (e) { console.error(e); }
            }
        }
        initPy();
    }, [pyodide]);

    useEffect(() => {
        if (showSuccessModal) {
            window.speechSynthesis.cancel();

            const pesanSukses = `Horeee! Selamatt kamu telah menyelesaikan pembelajaran materi ${course.title}`;

            const timer = setTimeout(() => {
                speak(pesanSukses);
            }, 200);

            return () => clearTimeout(timer);
        }
    }, [showSuccessModal]);

    const runPythonCode = async (actIdx: number, code: string) => {
        if (!pyodide) return; 
        setIsRunning(actIdx);

        let fullLog = ""; 

        try {
            const handlePythonInput = (message: string) => {
                const userInput = prompt(message);
                const value = userInput !== null ? userInput : "";

                fullLog += `${message}${value}\n`;

                setPyOutput(prev => ({ ...prev, [actIdx]: fullLog }));
                
                return value;
            };

            pyodide.globals.set("internal_input_js", handlePythonInput);

            await pyodide.runPythonAsync(`
                import sys, io
                import builtins

                # Pengalihan Print
                stdout_buffer = io.StringIO()
                sys.stdout = stdout_buffer

                # Gunakan lambda agar memanggil fungsi JS kita
                builtins.input = lambda msg="": internal_input_js(msg)
            `);

            await pyodide.runPythonAsync(code);

            const finalStdout = pyodide.runPython("sys.stdout.getvalue()");
            fullLog += finalStdout;

            setPyOutput(prev => ({ ...prev, [actIdx]: fullLog || "Program selesai running." }));
            
        } catch (err: any) {
            setPyOutput(prev => ({ ...prev, [actIdx]: fullLog + "\nError: " + err.message }));
        } finally { 
                setIsRunning(null); 
        }
    };

    const handleFinalComplete = () => {
        if (isAllFinished) {
            setShowSuccessModal(true);
            return;
        }
        router.post(`/siswa/courseSiswa/complete/${course.id}`, {}, {
            onSuccess: () => {
                window.speechSynthesis.cancel();
                setShowSuccessModal(true);
            },
            onError: (errors: any) => {
                alert(errors?.error || "Gagal menyelesaikan materi. Pastikan semua tahap sudah terisi.");
            },
            preserveScroll: true
        });
    }; 

    const [isCurrentStepReviewed, setIsCurrentStepReviewed] = useState(false);
        useEffect(() => {
            setIsCurrentStepReviewed(false);
        }, [activeStep]);

    const handleSaveAndNext = () => {
        if (!act) return;
        const currentQuestion = act.questions[activeQuestionIdx];
        const isAnsweredInServer = !!(existingAnswers as any)?.[currentQuestion.id];

        if (!isAnsweredInServer && !isJustSubmitted && !isCurrentStepReviewed) {
            const isCodingStep = ['modify', 'make'].includes(activeStep);
            const currentAnswer = answers[currentQuestion.id];

            if (!isCodingStep && (!currentAnswer || currentAnswer.trim() === "")) {
                alert("Harap isi jawabanmu terlebih dahulu.");
                return;
            }

            router.post('/siswa/courseSiswa/saveProgress', {
                course_id: course.id,
                tahap: activeStep,
                jawaban: { [currentQuestion.id]: currentAnswer || "" },
                kode_siswa: isCodingStep ? (editorCodes[currentActivityIdx] || "") : null 
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    setIsJustSubmitted(true);
                }
            });
            return; 
        }

        setIsJustSubmitted(false); 

        const isLastBlockInStep = currentActivityIdx === activities.length - 1;
        const isLastQuestionInBlock = activeQuestionIdx === act.questions.length - 1;
        const isAbsoluteLastQuestionInStep = isLastBlockInStep && isLastQuestionInBlock;

        if (activeStep === 'predict') {
            if (!isLastQuestionInBlock) {
                setActiveQuestionIdx(prev => prev + 1);
            } else if (!isLastBlockInStep) {
                setActiveQuestionIdx(0);
                setCurrentActivityIdx(prev => prev + 1);
            } else {
                window.speechSynthesis.cancel();
                router.visit(`/siswa/courseSiswa/showPrimm/${course.id}/run`, {
                });
            }
        } 
        else if (activeStep === 'run') {
            if (!isLastQuestionInBlock) {
                setActiveQuestionIdx(prev => prev + 1);
            } else if (!isLastBlockInStep) {
                setActiveQuestionIdx(0);
                setCurrentActivityIdx(prev => prev + 1);
            } else {
                const nextStep = steps[currentStep + 1];
                window.speechSynthesis.cancel();
                router.visit(`/siswa/courseSiswa/showPrimm/${course.id}/${nextStep}`, {
                });
            }
        }
        else {
            if (isReviewMode) {
                if (!isLastQuestionInBlock) {
                    setActiveQuestionIdx(prev => prev + 1);
                } else if (!isLastBlockInStep) {
                    setActiveQuestionIdx(0);
                    setCurrentActivityIdx(prev => prev + 1);
                } else {
                    const nextStep = steps[currentStep + 1];
                    if (nextStep) {
                        window.speechSynthesis.cancel();
                        router.visit(`/siswa/courseSiswa/showPrimm/${course.id}/${nextStep}?review=true`);
                    } else {
                        setShowSuccessModal(true);
                    }
                }
            } else {
                if (!isCurrentStepReviewed) {
            // Jika siswa MASIH dalam fase menjawab soal (belum masuk fase lihat pembahasan)
            if (!isLastQuestionInBlock) {
                setActiveQuestionIdx(prev => prev + 1);
            } else if (!isLastBlockInStep) {
                setActiveQuestionIdx(0);
                setCurrentActivityIdx(prev => prev + 1);
            } else {
                // TEPAT SETELAH SOAL TERAKHIR DI-SUBMIT:
                // Nyalakan mode review tahap, lalu lempar balik siswa ke Blok 1 dan Soal 1
                setIsCurrentStepReviewed(true);
                setCurrentActivityIdx(0);
                setActiveQuestionIdx(0);
            }
        }else {
                if (!isLastQuestionInBlock) {
                    setActiveQuestionIdx(prev => prev + 1);
                } else if (!isLastBlockInStep) {
                    setActiveQuestionIdx(0);
                    setCurrentActivityIdx(prev => prev + 1);
                } else {
                    const isStepFullyAnswered = activities.every(activity => 
                        activity.questions.every(question => !!(existingAnswers as any)?.[question.id])
                    );

                    if (!isStepFullyAnswered) {
                        return;
                    }
                    const nextStep = steps[currentStep + 1];
                    if (nextStep) {
                        window.speechSynthesis.cancel();
                        router.visit(`/siswa/courseSiswa/showPrimm/${course.id}/${nextStep}`);
                    } else {
                        handleFinalComplete(); 
                    }
                }
            }
        }
        }
    };

    const handleStartReview = () => {
        setShowSuccessModal(false);
        router.visit(`/siswa/courseSiswa/showPrimm/${course.id}/investigate?review=true`);
    };

    const [cekCount, setCekCount] = useState<number>(0);
    const [chatCount, setChatCount] = useState<number>(0);
    const [isLoadingAI, setIsLoadingAI] = useState<boolean>(false);
    const [showFeedbackLayout, setShowFeedbackLayout] = useState<boolean>(false);
    const [isFeedbackLoading, setIsFeedbackLoading] = useState<boolean>(false);

    useEffect(() => {
        setShowFeedbackLayout(false);
        setAiDraftFeedback(null);

        if (!qActive?.id) {
            setCekCount(0);
            setChatCount(0);
            return;
        }

        const fetchStatus = async () => {
            try {
                const res  = await fetch(`/tasks/${qActive.id}/check-status`);
                const data = await res.json();
                setCekCount(data.feedbackCount ?? 0);
                setChatCount(data.chatCount ?? 0);
                if (data.feedbackCount > 0) setShowFeedbackLayout(true);
            } catch (e) {
                console.error('checkStatus error:', e);
                setCekCount(0);
                setChatCount(0);
            }
        };

        fetchStatus();
    }, [qActive?.id, currentStep, currentActivityIdx]);

    return (
        <div className=" h-screen w-full bg-[#F8FAFC] flex flex-col overflow-hidden">
            <header className="h-20 flex-none bg-white border-b px-6 flex items-center justify-between shadow-sm z-10">
                <div className="flex items-center gap-4">
                    <div className="bg-blue-600 text-white px-3 py-1 rounded-lg font-black text-xs uppercase shadow-sm">PRIMM</div>
                    <h1 className="font-bold text-gray-800 truncate">{course.title}</h1>

                    <button 
                        onClick={toggleMute}
                        className={`p-2 rounded-xl transition-all ${isMuted ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-600'}`}
                    >
                        {isMuted ? <VolumeX size={18} /> : <Volume2 size={18} />}
                    </button>
                </div>
                <div className="flex items-center gap-2 bg-gray-100 p-1 rounded-xl">
                    {steps.map((s) => (
                        <div key={s} className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase ${s === activeStep ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400'}`}>
                            {s}
                        </div>
                    ))}
                </div>
            </header>

            <main className="flex-1 flex overflow-hidden p-4 relative gap-4">
                {subView === 'aktivitas' ? (
                    <div className="w-full flex gap-4 animate-in fade-in duration-500 overflow-hidden">
                        <section className="w-1/2 flex flex-col gap-4">
                         {['modify', 'make'].includes(activeStep?.toLowerCase()) && currentAiFeedback && currentAiFeedback.hasMisconception && (
                            <div className="mt-3 p-4 border-l-4 border-yellow-300 bg-white text-yellow-900 rounded-r-2xl text-xs font-semibold flex gap-2 items-start animate-in slide-in-from-top-2 duration-300">
                                <span className="shrink-0 text-sm">⚠️</span>
                                <div>
                                    <span className="font-bold text-yellow-700 block mb-0.5">Feedback:</span>
                                    <p className="italic font-medium text-justify leading-relaxed">{currentAiFeedback.feedback}</p>
                                </div>
                            </div>
                            )}
                            <div className="flex flex-col flex-[3] min-h-0 bg-[#1e1e1e]  shadow-xl border border-gray-800 overflow-hidden">
                                <div className="flex items-center justify-between px-4 py-2 bg-[#252526] border-b border-black/20">
                                    <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                        <Code2 size={14} className="text-blue-400" /> main.py
                                    </span>
                                    {['run', 'modify', 'make'].includes(activeStep) && (
                                        <button 
                                            onClick={() => runPythonCode(currentActivityIdx, editorCodes[currentActivityIdx] || "")} 
                                            disabled={isRunning !== null} 
                                            className="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1 rounded-lg text-[10px] font-bold flex items-center gap-2 transition-all active:scale-95 shadow-lg shadow-emerald-900/20"
                                        >
                                            <Play size={12} fill="currentColor"/> RUN
                                        </button>
                                    )}
                                </div>
                                <div className="flex-1 overflow-hidden">
                                    <CodeMirror 
                                        key={`editor-${activeStep}-${currentActivityIdx}`}
                                        value={editorCodes[currentActivityIdx] || ""} 
                                        theme={vscodeDark} 
                                        extensions={[python()]} 
                                        readOnly={!['modify', 'make'].includes(activeStep) || isBlockSubmitted || isAllFinished} 
                                        onChange={(val: string) => {
                                            const qId = act?.questions[activeQuestionIdx]?.id;
                                            if (qId) {
                                                handleCodeEditorChange(currentActivityIdx, qId, val);
                                            } else {
                                                setEditorCodes(prev => ({ ...prev, [currentActivityIdx]: val }));
                                            }
                                        }} 
                                        height="100%"
                                        className="text-sm h-full"
                                    />
                                </div>
                            </div>

                            {['run', 'modify', 'make'].includes(activeStep) && (
                                <div className="flex flex-col flex-[2] min-h-0 bg-black shadow-xl border border-gray-800 overflow-hidden animate-in slide-in-from-bottom duration-500">
                                    <div className="px-4 py-2 bg-[#1a1a1a] border-b border-gray-800 flex items-center gap-2">
                                        <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span className="text-[10px] font-black text-gray-500 uppercase tracking-widest italic">Console Output</span>
                                    </div>
                                    <div className="flex-1 p-5 font-mono text-[13px] text-white overflow-y-auto custom-scrollbar relative">
                                        {pyOutput[currentActivityIdx] ? (
                                            <pre className="whitespace-pre-wrap animate-in fade-in duration-300">
                                                {pyOutput[currentActivityIdx]}
                                            </pre>
                                        ) : (
                                            <div className="absolute inset-0 flex items-top justify-between opacity-50 ml-4 pt-3">
                                                <p className="text-[11px] uppercase tracking-[0.2em] text-white">
                                                    Silahkan tekan tombol RUN, Output akan muncul di sini..
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </section>

                       <section className="w-1/2 flex flex-col gap-4 overflow-y-auto pr-2 pb-32 custom-scrollbar min-h-0 flex-1">
                            {['investigate', 'modify', 'make'].includes(activeStep) && (
                                <button 
                                    onClick={() => setSubView('materi')}
                                    className="flex items-center gap-3 px-4 py-2.5 bg-white text-blue-600 rounded-xl border border-blue-100 font-black text-[11px] uppercase tracking-wider hover:bg-blue-50 transition-all shadow-sm w-fit"
                                >
                                    <BookOpen size={16} /> Pelajari Materi
                                </button>
                            )}

                            {act && act.questions.length > 0 ? (
                                <div className="space-y-4">
                                    {act.gambar && (
                                        <div className="bg-white p-3 border border-gray-200 shadow-sm rounded-2xl">
                                            <img src={`/storage/${act.gambar}`} className="mx-auto max-h-[200px] object-contain rounded-xl" alt="Visual Aid" />
                                        </div>
                                    )}

                                    {(() => {
                                        const q = act.questions[activeQuestionIdx];
                                        const isAnswered = !!(existingAnswers as any)?.[q.id] || isJustSubmitted;
                                        const isCodingStep = ['modify', 'make'].includes(activeStep);
                                        const isAllInBlockAnswered = act.questions.every(question => 
                                            !!(existingAnswers as any)?.[question.id]
                                        );

                                        return (
                                            <div key={q.id} className="bg-white rounded-[25px] border border-gray-200 shadow-sm overflow-hidden animate-in slide-in-from-right duration-500">
                                                <div className="bg-gray-50/50 p-6 border-b flex items-start gap-4 select-none">
                                                    <div className="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0 shadow-lg shadow-blue-100">
                                                        {activeQuestionIdx + 1}
                                                    </div>
                                                    <div className="flex-1">
                                                        <span className="text-[12px] font-black text-blue-500 uppercase tracking-widest block mb-1">Pertanyaan</span>
                                                        <p onCopy={(e) => {
                                                            e.preventDefault(); 
                                                            alert('Maaf, teks pertanyaan tidak dapat disalin!'); // Opsional: Beri peringatan ke user
                                                        }}
                                                        className="text-[15px] font-bold text-gray-700 leading-relaxed whitespace-pre-wrap text-justify">
                                                            {q.pertanyaan}
                                                        </p>
                                                    </div>
                                                </div>

                                                {activeStep === 'run' && pastPredictAnswer && (
                                                    <div className="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-r-2xl mb-4 animate-in fade-in zoom-in duration-300">
                                                        <span className="text-[10px] font-black text-emerald-600 uppercase tracking-widest block mb-1">
                                                            Jawaban Prediksi Anda Sebelumnya:
                                                        </span>
                                                        <p className="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
                                                            {pastPredictAnswer}
                                                        </p>
                                                    </div>
                                                )}

                                                <div className="p-6 space-y-4">
                                                    {!isCodingStep && (
                                                        <div className="flex flex-col gap-2">
                                                            <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                                Jawaban:
                                                            </span>

                                                            {isAnswered ? (
                                                                <div className="w-full p-5 rounded-2xl border-2 bg-emerald-50/50 border-emerald-100 text-black text-sm whitespace-pre-wrap animate-in fade-in duration-300">
                                                                    {(() => {
                                                                        const data = (existingAnswers as any)?.[q.id] || answers[q.id];
                                                                        if (typeof data === 'object' && data !== null) {
                                                                            return data.jawaban_siswa || '-';
                                                                        }
                                                                        return data || '-';
                                                                    })()}
                                                                </div>
                                                            ) : (
                                                                <div>
                                                                    <textarea
                                                                        className="w-full p-5 rounded-2xl border-2 border-gray-500 text-sm focus:border-blue-400 outline-none min-h-[120px] resize-y"
                                                                        value={answers[q.id] || ''}
                                                                        onChange={(e) => handleTextareaChange(q.id, e.target.value)}
                                                                        onBlur={(e) => handleTextareaBlur(q.id, e.target.value)} // Tambahkan pemicu instan di sini
                                                                        placeholder="Tuliskan jawabanmu di sini..."
                                                                    />

                                                                    {showFeedbackLayout && (isFeedbackLoading || (currentAiFeedback && currentAiFeedback.feedback)) && (
                                                                        <div className="mt-3 p-4 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-900 rounded-r-2xl text-xs font-semibold flex gap-2 items-start animate-in slide-in-from-top-2 duration-300">
                                                                            <span className="shrink-0 text-sm">
                                                                                {isFeedbackLoading ? "⏳" : "💡"}
                                                                            </span>
                                                                            <div className="w-full">
                                                                                <span className="font-bold text-yellow-700 block mb-1">
                                                                                    Bimbingan AI (Petunjuk {cekCount}/3):
                                                                                </span>
                                                                                
                                                                                {isFeedbackLoading ? (
                                                                                    // --- SKELETON ANIMASI INSTAN (Menunggu Backend) ---
                                                                                    <div className="space-y-2 py-1 animate-pulse">
                                                                                        <div className="h-3 bg-yellow-200/70 rounded-full w-11/12"></div>
                                                                                        <div className="h-3 bg-yellow-200/70 rounded-full w-full"></div>
                                                                                        <div className="h-3 bg-yellow-200/70 rounded-full w-4/5"></div>
                                                                                        <p className="text-[10px] text-yellow-600/80 italic mt-2 block">
                                                                                            Tutor AI sedang menelaah logika jawabanmu...
                                                                                        </p>
                                                                                    </div>
                                                                                ) : (
                                                                                    // --- TEKS PETUNJUK ASLI (Muncul setelah fetch selesai) ---
                                                                                    <p className="italic font-medium text-justify leading-relaxed whitespace-pre-wrap animate-in fade-in duration-500">
                                                                                        {currentAiFeedback?.feedback}
                                                                                    </p>
                                                                                )}
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    )}

                                                    {(isReviewMode || isCurrentStepReviewed) && !['predict', 'run'].includes(activeStep) && (
                                                        <div 
                                                        className="mt-4 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl w-full h-auto"
                                                        > 
                                                        <div className="flex items-center gap-2 mb-3">
                                                            <Lightbulb size={16} className="text-blue-600" />
                                                            <span className="text-[12px] font-black text-blue-600 uppercase">
                                                                Pembahasan 
                                                            </span>
                                                        </div>

                                                        <div 
                                                        className="text-sm text-gray-700 leading-relaxed text-justify 
                                                                whitespace-normal overflow-wrap-anywhere break-normal
                                                                [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-4
                                                                [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-4
                                                                [&_li]:mb-1
                                                                [&_p]:mb-4 [&_p:last-child]:mb-0 whitespace-pre-wrap"
                                                        dangerouslySetInnerHTML={{ 
                                                            __html: q.pembahasan.replace(/&nbsp;/g, ' ') 
                                                        }} 
                                                        />
                                                    </div>
                                                    )}
                                                </div>
                                            </div>
                                        );
                                    })()}
                                </div>
                            ) : (
                                <div className="h-64 flex items-center justify-center border-2 border-dashed rounded-[40px] text-gray-400 italic">
                                    Pilih tahap untuk melihat soal.
                                </div>
                            )}
                        </section>
                    </div>
                ) : (
                    <div className="fixed inset-0 z-[100] bg-slate-100 flex flex-col animate-in fade-in duration-300">
                        <button 
                            onClick={() => setSubView('aktivitas')}
                            className="absolute top-6 right-8 z-[110] flex items-center gap-2 px-5 py-3 bg-white/90 backdrop-blur-md text-red-600 rounded-2xl font-black text-xs uppercase shadow-2xl border border-red-100 hover:bg-red-600 hover:text-white transition-all active:scale-95 group"
                        >
                            <X size={20} className="group-hover:rotate-90 transition-transform" /> 
                            Tutup Materi
                        </button>
                        <div className="flex-1 w-full overflow-y-auto ">
                            <div className="max-w-4xl mx-auto min-h-screen flex flex-col py-10 px-4 md:px-0">
                                {course.link ? (
                                        <div className="aspect-video w-full ">
                                            <div 
                                                className="w-full h-full"
                                                dangerouslySetInnerHTML={{ __html: renderEmbedMedia(course.link) }} 
                                            />
                                        </div>

                                ) : course.file ? (
                                    <div className="space-y-6">
                                        {course.file.endsWith('.pdf') ? (
                                            <div className="bg-white  shadow-2xl border border-gray-200 overflow-hidden h-[85vh]">
                                                <iframe 
                                                    src={`/storage/${course.file}#toolbar=0`} 
                                                    className="w-full h-full border-0" 
                                                />
                                            </div>
                                        ) : (
                                            <div className="flex justify-center">
                                                <img src={`/storage/${course.file}`} className="max-w-full h-auto  shadow-2xl border-4 border-white" />
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <div className="flex-1 flex flex-col items-center justify-center py-20 text-slate-400">
                                        <Search size={48} className="opacity-10 mb-4" />
                                        <p className="font-bold">Materi tidak ditemukan.</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </main>

           <footer className="h-20 flex-none bg-white border-t px-8 flex items-center justify-between z-10 shadow-lg">
                <div className="flex flex-col">
                    <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                        Progres Belajar
                    </span>
                    <div className="flex items-center gap-3">
                        <div className="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">
                            Blok {currentActivityIdx + 1}
                        </div>
                        <span className="text-gray-300">•</span>
                        <div className="text-xs font-bold text-gray-500">
                            Soal {activeQuestionIdx + 1} dari {act?.questions.length || 0}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-4 mr-24">
                    {act && act.questions.length > 0 && 
                        ['investigate', 'modify', 'make'].includes(activeStep.toLowerCase()) &&
                        !((existingAnswers as any)?.[act.questions[activeQuestionIdx]?.id]) && 
                        !isJustSubmitted && 
                        !isReviewMode && (
                            <button
                                type="button"
                                disabled={cekCount >= 3 || isFeedbackLoading}
                                onClick={async () => {
                                    if (cekCount >= 3 || isFeedbackLoading) return;

                                    const currentQuestion = act.questions[activeQuestionIdx];
                                    const isCodingStep = ['modify', 'make'].includes(activeStep);
                                    const currentAnswer = answers[currentQuestion.id] || "";
                                    const currentCode = editorCodes[currentActivityIdx] || "";

                                    if (isCodingStep && !currentCode.trim()) {
                                        alert("Harap modifikasi atau tulis kode programmu terlebih dahulu.");
                                        return;
                                    }
                                    if (!isCodingStep && !currentAnswer.trim()) {
                                        alert("Harap isi jawabanmu terlebih dahulu.");
                                        return;
                                    }

                                    if (debounceTimerRef.current) {
                                        clearTimeout(debounceTimerRef.current);
                                    }

                                    setShowFeedbackLayout(true);
                                    setIsFeedbackLoading(true);

                                    const dataDikirim = isCodingStep ? currentCode : currentAnswer;
                                    
                                    try {
                                 
                                        await sendDraftToLaravel(dataDikirim, currentQuestion.id, true);
                                    } catch (error) {
                                        console.error(error);
                                    } finally {
                         
                                        setIsFeedbackLoading(false);
                                    }
                                }}
                                className={`px-4 py-2 rounded-[15px] font-bold text-sm flex items-center gap-2 transition-all active:scale-95 shadow-lg ${
                                    cekCount >= 3 || isFeedbackLoading
                                        ? 'bg-gray-100 text-gray-400 border border-gray-200 shadow-none cursor-not-allowed'
                                        : 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-100'
                                }`}
                            >
                                {isFeedbackLoading ? (
                                    <>
                                        <span className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        Membaca...
                                    </>
                                ) : cekCount >= 3 ? (
                                    "🔒 Cek Selesai"
                                ) : (
                                    `💡 Cek Jawaban (${cekCount}/3)`
                                )}
                            </button>
                        )
                    }
                        <button 
                        onClick={handleSaveAndNext} 
                        className={`
                            px-4 py-2 rounded-[15px] font-bold text-sm flex items-center gap-2 
                            transition-all active:scale-95 shadow-lg
                            ${!(existingAnswers as any)?.[act?.questions[activeQuestionIdx]?.id] 
                                ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100' 
                                : isReviewMode ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-600 hover:bg-blue-700 shadow-gray-100'}
                            text-white
                        `}
                    >
                        {(() => {
                            const isAnsweredInServer = !!(existingAnswers as any)?.[act?.questions[activeQuestionIdx]?.id];
                            const isFinalSoal = currentActivityIdx === activities.length - 1 && activeQuestionIdx === act.questions.length - 1;
                            const isLastStep = currentStep === steps.length - 1; 

                            if (!isAnsweredInServer && !isJustSubmitted && !isCurrentStepReviewed) {
                                return "Simpan Jawaban";
                            }

                            if (isReviewMode) {
                                if (isFinalSoal) {
                                    return isLastStep ? "Selesaikan Review" : "Lanjut Review Tahap Berikutnya";
                                }
                                return "Pembahasan Selanjutnya";
                            }

                            if (isCurrentStepReviewed) {
                                if (isFinalSoal) {
                                    const nextTargetStep = steps[currentStep + 1];
                                    return nextTargetStep ? `Lanjut ke Tahap ${nextTargetStep.toUpperCase()}` : "Selesaikan Pembelajaran";
                                }
                                return "Pembahasan Soal Selanjutnya";
                            }

                            // 4. Kondisi transisi tepat ketika soal paling terakhir di-submit (Pemicu untuk balik ke soal pertama)
                            if (isFinalSoal && !isCurrentStepReviewed) {
                                // Tahap predict dan run tidak ada reviu pembahasan per nomor, jadi langsung lanjut tahap
                                if (['predict', 'run'].includes(activeStep?.toLowerCase())) {
                                    const nextTarget = activeStep === 'predict' ? 'RUN' : 'INVESTIGATE';
                                    return `Lanjut ke Tahap ${nextTarget}`;
                                }
                                return "Lihat Pembahasan Tahap Ini";
                            }

                            // 5. Kondisi navigasi antar-soal biasa saat siswa masih proses menjawab soal
                            return "Lanjut ke Soal Berikutnya";
                        })()}
                    </button>
                </div>
            </footer>

           {subView === 'aktivitas' && !isReviewMode && !isCurrentStepReviewed && 
                    act?.questions?.length > 0 && 
                    ['investigate', 'modify', 'make'].includes(activeStep.toLowerCase()) &&
                    !((existingAnswers as any)?.[act.questions[activeQuestionIdx]?.id]) &&
                    !isJustSubmitted && (
                        <div className="fixed bottom-14 right-15 z-[100] transition-all duration-500 ease-out animate-in slide-in-from-bottom-10">
                            <ChatAI 
                                key={act.questions[activeQuestionIdx].id} 
                                pertanyaanId={act.questions[activeQuestionIdx].id}
                                hintUrl={hintUrl}
                                activeStep={activeStep}
                            />
                        </div>
                    )
                }

            {showSuccessModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 animate-in fade-in duration-300">
                    <div className="bg-white rounded-[40px] p-10 max-w-md w-full text-center shadow-2xl relative">
                        <div className="mb-6 inline-flex items-center justify-center w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full"><CheckCircle2 size={48} /></div>
                        <h2 className="text-2xl font-black text-gray-900 uppercase tracking-tight mb-2">Hore! Selesai</h2>
                        <p className="text-gray-500 text-sm mb-8">Kamu telah menuntaskan tantangan PRIMM materi ini.</p>
                        <div className="flex flex-col gap-3">
                            {course.link_drive && (
                                <a href={course.link_drive} target="_blank" className="flex items-center justify-center gap-2 bg-emerald-600 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all"><ExternalLink size={18} /> Unduh Materi Lengkap</a>
                            )}
                            <button onClick={() => router.visit('/siswa/courseSiswa')} className="bg-gray-500 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all">Kembali ke Menu</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}