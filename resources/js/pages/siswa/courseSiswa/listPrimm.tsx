import React, { useState, useEffect } from "react"; 
import { Link, Head } from '@inertiajs/react';
import { 
    Lock, CheckCircle2, ChevronRight, 
    Telescope, PlayCircle, SearchCode, Pencil, Cpu, BookOpen, Users, 
    ArrowRight, ArrowLeft
} from "lucide-react";
import AppLayout from '@/layouts/app-layout';
import { useVoice } from '@/hooks/useVoice';

export default function ListPrimm({ course, progress, isAllFinished }: any) {
    const [showInstructions, setShowInstructions] = useState(!isAllFinished);
    const { speak } = useVoice();
    const steps = ["predict", "run", "investigate", "modify", "make"];
    const stepIcons = [Telescope, PlayCircle, SearchCode, Pencil, Cpu];
    const deskripsiTahap = [
        "Mari memprediksi hasil keluaran kode program berdasarkan pemahaman logika awal kamu.",
        "Jalankan kode program sesungguhnya untuk melihat apakah prediksi kamu sudah tepat.",
        "Mari menelusuri baris demi baris kode untuk memahami bagaimana program bekerja.",
        "Ubah sedikit bagian kode untuk melihat perubahan apa yang terjadi pada program.",
        "Saatnya membuat program baru sendiri menggunakan konsep yang telah kamu pelajari."
    ];

    useEffect(() => {
        if (showInstructions) {
            const welcomeMessage = `Selamat datang di halaman aktivitas belajar. 
                Di sini kamu akan belajar pemrograman tentang ${course.title}. Silakan baca petunjuk pengerjaan dan langkah kegiatan. Jika sudah dibaca, klik tombol mulai sekarang 
                untuk belajar yang dimulai dari tahap predict.`;

            const timer = setTimeout(() => {
                speak(welcomeMessage);
            }, 200);

            return () => {
                clearTimeout(timer);
                window.speechSynthesis.cancel();
            };
        }
    }, [showInstructions, course.title]);

    const handleCloseInstructions = () => {
        setShowInstructions(false);
        window.speechSynthesis.cancel(); 
    };

    return (
        <AppLayout breadcrumbs={[{ title: course.title, href: '#' }]}>
            <Head title={`Daftar Tahap - ${course.title}`} />
            
            <div className="w-full mx-auto p-6 md:p-12 bg-white min-h-screen">
                
                {showInstructions ? (
                    <div className="fixed inset-0 z-50 h-screen w-full bg-gray-50/80 backdrop-blur-sm overflow-hidden flex items-center justify-center p-4">
                        <div className="max-w-4xl w-full animate-in fade-in zoom-in duration-500">
                            <div className="bg-blue-200 p-6 md:p-8 shadow-2xl border border-white/50 relative rounded-[40px]">
                                <div className="text-center mb-6">
                                    <h2 className="text-2xl font-black text-gray-900 tracking-tight">Panduan Aktivitas</h2>
                                    <p className="text-gray-600 text-sm font-medium">Ikuti langkah-langkah di bawah ini dengan cermat.</p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-10">
                                    <div className="space-y-4">
                                        <h3 className="text-sm font-bold text-gray-800 flex items-center gap-3">
                                            <span className="w-7 h-7 bg-amber-400 text-white rounded-lg flex items-center justify-center shadow-sm">A</span>
                                            Petunjuk Pengerjaan
                                        </h3>
                                        <div className="space-y-2">
                                            {[
                                                { text: "Kerjakan LKPD secara berkelompok.", icon: Users },
                                                { text: "Diskusikan soal bersama teman sekelompok.", icon: BookOpen },
                                                { text: "Setiap anggota wajib berkontribusi aktif.", icon: CheckCircle2 },
                                                { text: "Periksa kembali jawaban sebelum dikirim.", icon: CheckCircle2 }
                                            ].map((item, i) => (
                                                <div key={i} className="flex gap-3 p-3 rounded-xl bg-white/60 border border-white items-center">
                                                    <item.icon className="w-4 h-4 text-blue-600 flex-shrink-0" />
                                                    <p className="text-gray-700 text-xs font-bold leading-tight">{item.text}</p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <h3 className="text-sm font-bold text-gray-800 flex items-center gap-3">
                                            <span className="w-7 h-7 bg-blue-500 text-white rounded-lg flex items-center justify-center shadow-sm">B</span>
                                            Langkah Kegiatan
                                        </h3>
                                        <div className="space-y-2">
                                            {[
                                                { t: "Memprediksi Program", i: Telescope, c: "bg-amber-500" },
                                                { t: "Menjalankan Program", i: PlayCircle, c: "bg-emerald-500" },
                                                { t: "Menelusuri Program", i: SearchCode, c: "bg-blue-500" },
                                                { t: "Mengubah Program", i: Pencil, c: "bg-purple-500" },
                                                { t: "Membuat Program", i: Cpu, c: "bg-rose-500" }
                                            ].map((item, i) => (
                                                <div key={i} className="flex items-center gap-3 p-2.5 bg-white rounded-xl shadow-sm border border-gray-100">
                                                    <div className={`w-7 h-7 ${item.c} text-white rounded-lg flex items-center justify-center flex-shrink-0`}>
                                                        <item.i size={14} />
                                                    </div>
                                                    <p className="text-xs font-black text-gray-700">{i + 1}. {item.t}</p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-8 flex justify-end">
                                    <button
                                        onClick={handleCloseInstructions}
                                        className="group px-6 py-3 bg-[#0F828C] hover:bg-[#0d6d74] text-white rounded-2xl font-black text-sm transition-all shadow-lg flex items-center gap-2 active:scale-95"
                                    >
                                        MULAI SEKARANG <ArrowRight size={18} className="group-hover:translate-x-1 transition-transform" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
               ) : (
                    /* MAIN CONTENT - OPTIMAL 3 KOLOM */
                    <div className="animate-in fade-in duration-700 max-w-6xl mx-auto">

                        <div className="mb-8 flex flex-col gap-2"> 
                            <div className="text-left">
                                <h1 className="text-2xl font-black text-gray-900 tracking-tight">
                                    Aktivitas yang akan dipelajari
                                </h1>
                                <p className="text-gray-500 text-m font-medium">
                                    Pada Materi <span className="text-blue-600 font-bold">{course.title}</span>
                                </p>
                            </div>
  
                            <Link 
                                href="/siswa/courseSiswa" 
                                className="flex items-center gap-1.5 text-gray-600 hover:text-black transition-colors font-bold text-[10px] tracking-widest uppercase pt-4"
                            >
                                <ArrowLeft size={14} /> Kembali
                            </Link>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            {steps.map((step, index) => {
                                const isFirstStep = index === 0;
                                const previousStep = steps[index - 1];

                                const isCompleted = progress[step] === true || progress[step] === 1;
                                
                                const isUnlocked = isFirstStep || (progress[previousStep] === true || progress[previousStep] === 1);

                                const IconComponent = stepIcons[index];

                                return (
                                    <div 
                                        key={step} 
                                        className={`relative flex flex-col justify-between p-6 rounded-[32px] border-2 transition-all duration-300 min-h-[220px] ${
                                            isUnlocked 
                                            ? 'border-gray-100 bg-white shadow-sm hover:shadow-md hover:-translate-y-1' 
                                            : 'border-gray-50 bg-gray-50/50 opacity-60'
                                        }`}
                                    >
                                        <div>
                                            <div className="flex justify-between items-start mb-4">
                                                <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                                                    isCompleted ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500'
                                                }`}>
                                                    <IconComponent size={20} strokeWidth={2.5} />
                                                </div>

                                                {/* Badge SELESAI muncul jika isCompleted true */}
                                                {isCompleted && (
                                                    <span className="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[8px] font-black tracking-widest uppercase">
                                                        SELESAI
                                                    </span>
                                                )}
                                            </div>

                                            <h3 className={`text-lg font-black mb-1.5 capitalize ${isUnlocked ? 'text-gray-800' : 'text-gray-400'}`}>
                                                {step}
                                            </h3>
                                            
                                            <p className="text-gray-500 text-[12px] leading-relaxed font-medium line-clamp-2">
                                                {deskripsiTahap[index]}
                                            </p>
                                        </div>

                                        <div className="mt-4 pt-4 border-t border-gray-50 flex justify-end items-center">
                                            {isUnlocked ? (
                                                <Link 
                                                    href={`/siswa/courseSiswa/showPrimm/${course.id}/${step}`}
                                                    className="text-blue-600 font-black text-[11px] hover:text-blue-800 transition-all flex items-center gap-1 group"
                                                >
                                                    {/* Jika sudah selesai, teks berubah menjadi LIHAT LAGI */}
                                                    {isCompleted ? 'LIHAT LAGI' : 'KERJAKAN'}
                                                    <ChevronRight className="group-hover:translate-x-0.5 transition-transform" size={14} />
                                                </Link>
                                            ) : (
                                                <div className="flex items-center gap-1 text-gray-300 font-bold text-[10px] uppercase tracking-widest">
                                                    <Lock size={12} /> Terkunci
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}