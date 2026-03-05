import hero from '@/assets/hero.png';
import Navbar from'@/layouts/nav-layout';
import Footer from './footer';
import { ArrowRight } from 'lucide-react';
export default function Welcome(){
    return (
        <>
        <Navbar>
            <section>
               <div className="relative isolate px-5 pt-20 lg:px-10">
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
                        <p className="mt-8 text-center text-lg font-medium text-gray-500 sm:text-xl">
                        Website ini merupakan tempat belajar pemrograman dengan berbagai fitur seperti test, lembar kerja siswa dan sekaligus nilainya 
                        </p>
                        <div className=" animate-bounce justify-center mt-10 flex items-center gap-x-6 ">
                            <button className="w-32 px-4 py-1.5 rounded-md bg-[#78B9B5] font-medium text-white 
                            hover:bg-gray-300 hover:text-black flex items-center justify-between">
                                <span>Jelajahi</span>
                                <ArrowRight size={20} />
                            </button>
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
        </Navbar>
        <Footer />
        </>
    );
}
