import { router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export const useHint = (stage: string, questionId: string) => {
    const [hints, setHints] = useState<{level: number, text: string}[]>([]);
    const [hintLevel, setHintLevel] = useState(0);
    const [exhausted, setExhausted] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setHints([]);
        setHintLevel(0);
        setExhausted(false);
    }, [stage, questionId]);

    const requestHint = (currentAnswer: string) => {
        if (exhausted || hintLevel >= 3 || loading) return;

        // Ganti route('hint.request') dengan string URL langsung
        router.post('/hint', { 
            question: currentAnswer,
            pertanyaan_id: questionId,
            // stage juga dikirim jika controller membutuhkannya
            stage: stage 
        }, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
            onSuccess: (page) => {
                // Mengambil data dari session flash 'aiResponse' yang dikirim Controller
                const aiResponse = (page.props.flash as any)?.aiResponse;
                
                if (aiResponse) {
                    setHints(prev => [...prev, {
                        level: hintLevel + 1,
                        text: aiResponse,
                    }]);
                    
                    const nextLevel = hintLevel + 1;
                    setHintLevel(nextLevel);
                    
                    if (nextLevel >= 3) setExhausted(true);
                }
            },
            onError: (errors) => {
                console.error("Gagal mengambil hint:", errors);
            }
        });
    };

    return { hints, hintLevel, loading, exhausted, requestHint };
};