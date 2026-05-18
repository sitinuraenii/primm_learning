import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Monitor, Trophy, BookOpen, ChevronRight, Info, Activity, Target } from 'lucide-react';

interface Props {
    auth: {
        user: { name: string; role: string; };
    };
    stats: {
        totalMateri: number;
        totalAktivitas: number;
        progresSiswa: number;
        hasilAkhir: number;
    };
}

export default function DashboardSiswa({ auth, stats }: Props) {
    const [showWelcome, setShowWelcome] = useState(false);

    useEffect(() => {
        const hasSeen = sessionStorage.getItem('welcome_shown');
        if (!hasSeen) setShowWelcome(true);
    }, []);

    const closeWelcome = () => {
        setShowWelcome(false);
        sessionStorage.setItem('welcome_shown', 'true');
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Dashboard Siswa', href: '/siswa/dashboard' }]}>
            <Head title="Dashboard Siswa" />

            {/* Modal Info Penting */}
            {showWelcome && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
                    <div className="bg-white rounded-[32px] max-w-md w-full overflow-hidden shadow-2xl border border-white animate-in zoom-in duration-300">
                        <div className="bg-white p-8 text-black text-center relative">
                            <h3 className="text-2xl font-black uppercase tracking-tight relative">INFO PENTING!</h3>
                            <p className="text-slate-500 text-sm mt-1">Alur penggunaan aplikasi:</p>
                        </div>
                        <div className="p-6 pt-0 space-y-4">
                            <ul className="space-y-3 text-sm text-slate-600 font-medium">
                                <li className="flex gap-3"><span>1.</span> <span>Slahkan lihat di menu <b>Test</b> apakah ada test, jika ada lakukan!</span></li>
                                <li className="flex gap-3"><span>2.</span> <span>Lanjutkan ke <b>Edit Profil</b> jika ingin memperbarui data.</span></li>
                                <li className="flex gap-3"><span>3.</span> <span>Pilih menu <b>Course</b> untuk mulai belajar.</span></li>
                                <li className="flex gap-3 border-t border-dashed pt-3 font-bold text-slate-800"><span>4.</span> <span>Cek <b>Grade</b> untuk melihat hasil.</span></li>
                                <li className="flex gap-3 text-rose-600"><span>5.</span> <span>Wajib <b>Logout</b> jika sudah selesai.</span></li>
                            </ul>
                            <button onClick={closeWelcome} className="w-full mt-4 py-3 bg-[#0F828C] hover:bg-[#0d6d74] text-white rounded-2xl font-black text-sm uppercase transition-all flex items-center justify-center gap-2">
                                Mengerti <ChevronRight size={18} />
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <div className="p-6 md:p-10 bg-[#F8FAFC] min-h-screen font-sans">
                {/* Header Section */}
                <div className="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <h1 className="text-3xl font-black text-slate-800">Halo, <span className="text-blue-600 uppercase">{auth.user.name}!</span></h1>
                        </div>
                        <p className="text-slate-500 font-medium max-w-lg leading-relaxed">
                            Selamat belajar dan semoga sukses! Jangan lupa untuk berdoa terlebih dahulu.
                        </p>
                    </div>
                </div>

                {/* Stats Grid - Menyesuaikan Referensi Gambar */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    {/* Card 1: Total Materi */}
                    <StatCard 
                        title="Total Materi"
                        value={stats.totalMateri}
                        subtitle="Materi Aktif"
                        icon={<BookOpen className="text-blue-600" />}
                        iconBg="bg-blue-100"
                        trendColor="text-emerald-500"
                    />

                    {/* Card 2: Aktivitas */}
                    <StatCard 
                        title="Aktivitas"
                        value={`${stats.progresSiswa}/${stats.totalAktivitas}`}
                        subtitle="Selesai"
                        icon={<Activity className="text-emerald-600" />}
                        iconBg="bg-emerald-100"
                        trendColor="text-emerald-500"
                    />

                    <StatCard 
                        title="Rata-rata"
                        value={stats.hasilAkhir || 0}
                        subtitle={stats.hasilAkhir > 0 ? "Target tercapai" : "Belum tersedia"}
                        icon={<Trophy className="text-amber-600" />}
                        iconBg="bg-amber-100"
                        trendColor="text-emerald-500"
                    />

                </div>
            </div>
        </AppLayout>
    );
}

/* Sub-komponen Card agar kode lebih bersih */
function StatCard({ title, value, subtitle, icon, iconBg, trendColor }: any) {
    return (
        <div className="
            bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 
            cursor-pointer font-sans
            /* Animasi Hover (Maju ke depan) */
            hover:-translate-y-2 hover:shadow-xl hover:shadow-slate-200/50 hover:border-white
            /* Animasi Klik (Membal/Press effect) */
            active:scale-95 active:translate-y-0
            /* Transisi Halus */
            transition-all duration-300 ease-out 
            group
        ">
            <div className="flex justify-between items-start mb-4">
                <div className={`
                    ${iconBg} p-3 rounded-2xl 
                    transition-transform duration-500 
                    group-hover:scale-110 group-hover:rotate-3
                `}>
                    {React.cloneElement(icon, { size: 24, strokeWidth: 2.5 })}
                </div>
                <div className={`p-1.5 rounded-lg bg-emerald-50 ${trendColor} transition-colors group-hover:bg-white`}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                </div>
            </div>
            <div>
                <p className="text-slate-500 text-[10px] font-black uppercase tracking-[0.1em] mb-1 transition-colors group-hover:text-slate-400">
                    {title}
                </p>
                <h3 className="text-3xl font-black text-slate-800 mb-1 tracking-tight">
                    {value}
                </h3>
                <p className="text-slate-400 text-[11px] font-semibold">
                    {subtitle}
                </p>
            </div>
        </div>
    );
}