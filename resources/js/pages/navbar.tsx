import { Button } from "@/components/ui/button";
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '../components/app-logo-icon';

export default function Navbar({ canRegister = true }: { canRegister?: boolean }) {
  const page = usePage<SharedData>();
  const auth = page.props.auth;
  const { url } = usePage();

  const isActive = (path: string) => {
    if (path === '/') {
      return url === '/';
    }
    return url.startsWith(path);
  };

  return (
    <header className="bg-[#0F828C] text-white border-b border-[#0d6d74]">
      <nav className="max-w-7xl mx-auto flex items-center justify-between p-4 gap-6">
        <div className="flex items-center">
          <AppLogoIcon className="size-7 text-white" />
          <span className="ml-2 text-lg font-semibold">PrimmLearn</span>
        </div>

        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            className={`px-4 py-1.5 ${isActive('/') && 'bg-[#78B9B5]'}`}
            asChild
          >
            <Link href="/">Beranda</Link>
          </Button>

          <Button
            variant="ghost"
            className={`px-4 py-1.5 ${isActive('/petunjuk') && 'bg-[#78B9B5]'}`}
            asChild
          >
            <Link href="/petunjuk">Petunjuk</Link>
          </Button>

          <Button
            variant="ghost"
            className={`px-4 py-1.5 ${isActive('/tentang') && 'bg-[#78B9B5]'}`}
            asChild
          >
            <Link href="/tentang">Tentang</Link>
          </Button>

          <Button
            variant="ghost"
            className={`px-4 py-1.5 ${isActive('/kontak') && 'bg-[#78B9B5]'}`}
            asChild
          >
            <Link href="/kontak">Kontak</Link>
          </Button>

          {auth?.user ? (
            <Button
              variant="ghost"
              className="px-4 py-1.5 bg-[#78B9B5] hover:bg-[#68a6a2] text-white"
              asChild
            >
              <Link href={auth.user.role === 'guru' ? '/guru/dashboard' : '/siswa/dashboard'}>
                Dashboard
              </Link>
            </Button>
          ) : (
            <>
              <Button variant="ghost" className={`px-4 py-1.5 ${isActive('/login') && 'bg-[#78B9B5]'}`} asChild>
                <Link href="/login">Login</Link>
              </Button>

              {canRegister && (
                <Button variant="ghost" className={`px-4 py-1.5 ${isActive('/register') && 'bg-[#78B9B5]'}`} asChild>
                  <Link href="/register">Daftar</Link>
                </Button>
              )}
            </>
          )}
        </div>
      </nav>
    </header>
  );
}
