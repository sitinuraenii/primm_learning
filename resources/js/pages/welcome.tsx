import hero from '@/assets/hero.png';
import PublicLayout from '@/layouts/nav-layout';
import Footer from './footer';
import About from './about';
import Guide from './petunjuk';
import Contact from './contact';

import { ArrowDown, MessageCircle, Send, Instagram,  } from 'lucide-react';

export default function Welcome(){
    return (
        <>
        <PublicLayout>
            <section id="home" className="w-full min-h-screen flex items-center bg-white">
               <div className="relative isolate px-5  lg:px-10">
                <div className="mx-auto max-w-7xl py-24">
                    <div className="grid grid-cols-1 lg:grid-cols-2 items-center gap-10">
                    <div>
                        <div className="text-center w-full"> 
                            <h1 
                                className="text-6xl font-black uppercase tracking-tighter pr-2"
                                style={{
                                background: 'linear-gradient(to right, #0F828C, #F7CA89)',
                                WebkitBackgroundClip: 'text',
                                WebkitTextFillColor: 'transparent',
                                backgroundClip: 'text',
                                color: 'transparent',
                                display: 'inline-block',
                                lineHeight: '1.2', 
                                marginBottom: '10px'
                                }}
                            >
                                PRIMMLEARN
                            </h1>
                            </div>
                        <p className="mt-8 text-center text-lg font-medium text-black sm:text-xl">
                        Website ini merupakan tempat belajar pemrograman dengan berbagai fitur seperti test, lembar kerja siswa, daftar nilai dan feedback.
                        </p>
                        <div className=" animate-bounce justify-center mt-10 flex items-center gap-x-6 ">
                            <a 
                                href="#guide"
                                className="w-32 px-5 py-1.5 rounded-md bg-[#78B9B5] font-medium text-white 
                                            hover:bg-gray-300 hover:text-black flex items-center justify-between 
                                            transition-colors duration-300"
                                >
                                <span>Jelajahi</span>
                                <ArrowDown size={20} />
                            </a>
                        </div>
                    </div>

                    <div className="flex justify-center animate-pulse">
                        <img src={hero}  
                        alt="Hero Illustration"
                        className="w-[60%] max-w-sm rounded-xl "/>
                    </div>
                    </div>
                </div>
                </div>
            </section>

           <Guide /> 
            <About/>
            <Contact />
        </PublicLayout>
        <Footer />
        </>
    );
}
