import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useEffect, useState } from 'react';

import MobileMenu from '@/Components/Landing/MobileMenu';
import { landingNavigationItems } from '@/Data/landingNavigation';
import { cn } from '@/lib/utils';

export default function Navbar() {
    const [isScrolled, setIsScrolled] = useState(false);
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    useEffect(() => {
        const updateScrollState = (): void => {
            setIsScrolled(window.scrollY > 50);
        };

        updateScrollState();
        window.addEventListener('scroll', updateScrollState);

        return () => window.removeEventListener('scroll', updateScrollState);
    }, []);

    return (
        <>
            <header
                className={cn(
                    'fixed left-0 top-0 z-50 w-full transition-all duration-300',
                    isScrolled
                        ? 'bg-white/95 shadow-sm backdrop-blur-md'
                        : 'bg-transparent',
                )}
            >
                <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link
                        href={route('landing.home')}
                        className={cn(
                            "font-['Plus_Jakarta_Sans'] text-xl font-bold",
                            isScrolled ? 'text-[#24695c]' : 'text-white',
                        )}
                    >
                        Proker
                        <span className="text-[#ba895d]">in</span>
                    </Link>

                    <nav className="hidden items-center gap-8 md:flex">
                        {landingNavigationItems.map((item) => (
                            <Link
                                key={item.label}
                                href={item.href}
                                className={cn(
                                    'text-sm font-semibold transition-colors',
                                    isScrolled
                                        ? 'text-[#59667a] hover:text-[#24695c]'
                                        : 'text-white/80 hover:text-white',
                                )}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-3 md:flex">
                        <Link
                            href={route('login')}
                            className={cn(
                                'rounded-xl px-4 py-2 text-sm font-semibold transition-colors',
                                isScrolled
                                    ? 'text-[#242934] hover:bg-[#f5f7fb]'
                                    : 'text-white hover:bg-white/10',
                            )}
                        >
                            Masuk
                        </Link>
                        <Link
                            href={route('register')}
                            className="rounded-xl bg-[#24695c] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#24695c]/20 transition-colors hover:bg-[#1b4c43]"
                        >
                            Coba Gratis →
                        </Link>
                    </div>

                    <button
                        type="button"
                        aria-label="Buka menu navigasi"
                        onClick={() => setIsMenuOpen(true)}
                        className={cn(
                            'inline-flex h-11 w-11 items-center justify-center rounded-xl md:hidden',
                            isScrolled
                                ? 'bg-[#f5f7fb] text-[#242934]'
                                : 'bg-white/10 text-white',
                        )}
                    >
                        <Menu className="h-5 w-5" />
                    </button>
                </div>
            </header>
            <MobileMenu
                isOpen={isMenuOpen}
                onClose={() => setIsMenuOpen(false)}
            />
        </>
    );
}
