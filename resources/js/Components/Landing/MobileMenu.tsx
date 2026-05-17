import { Link } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { X } from 'lucide-react';
import { useEffect, useRef } from 'react';

interface MobileMenuProps {
    isOpen: boolean;
    onClose: () => void;
}

const navItems = [
    { label: 'Fitur', href: route('landing.features') },
    { label: 'Harga', href: route('landing.pricing') },
];

export default function MobileMenu({ isOpen, onClose }: MobileMenuProps) {
    const closeButtonRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        closeButtonRef.current?.focus();

        const handleKeyDown = (event: KeyboardEvent): void => {
            if (event.key === 'Escape') {
                onClose();
            }

            if (event.key === 'Tab') {
                const focusableElements = Array.from(
                    document.querySelectorAll<HTMLElement>(
                        '[data-mobile-menu] a[href], [data-mobile-menu] button:not([disabled])',
                    ),
                );
                const firstElement = focusableElements[0];
                const lastElement =
                    focusableElements[focusableElements.length - 1];

                if (!firstElement || !lastElement) {
                    return;
                }

                if (event.shiftKey && document.activeElement === firstElement) {
                    event.preventDefault();
                    lastElement.focus();
                }

                if (!event.shiftKey && document.activeElement === lastElement) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onClose]);

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    data-mobile-menu
                    className="fixed inset-0 z-[80] bg-white p-6"
                    initial={{ opacity: 0, x: 32 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: 32 }}
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu navigasi mobile"
                >
                    <div className="flex items-center justify-between">
                        <Link
                            href={route('landing.home')}
                            onClick={onClose}
                            className="font-['Plus_Jakarta_Sans'] text-xl font-bold text-[#24695c]"
                        >
                            Proker<span className="text-[#ba895d]">in</span>
                        </Link>
                        <button
                            ref={closeButtonRef}
                            type="button"
                            aria-label="Tutup menu"
                            onClick={onClose}
                            className="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#f5f7fb] text-[#242934]"
                        >
                            <X className="h-5 w-5" />
                        </button>
                    </div>
                    <nav className="mt-10 space-y-3">
                        {navItems.map((item) => (
                            item.href === null ? (
                                <span
                                    key={item.label}
                                    aria-disabled="true"
                                    className="block cursor-not-allowed rounded-2xl bg-[#f5f7fb] px-5 py-4 text-base font-semibold text-[#59667a]/50"
                                >
                                    {item.label}
                                </span>
                            ) : (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    onClick={onClose}
                                    className="block rounded-2xl bg-[#f5f7fb] px-5 py-4 text-base font-semibold text-[#242934]"
                                >
                                    {item.label}
                                </Link>
                            )
                        ))}
                    </nav>
                    <div className="mt-8 grid gap-3">
                        <Link
                            href={route('login')}
                            onClick={onClose}
                            className="inline-flex justify-center rounded-xl border border-[#e6edef] px-5 py-3 text-sm font-semibold text-[#242934]"
                        >
                            Masuk
                        </Link>
                        <Link
                            href={route('register')}
                            onClick={onClose}
                            className="inline-flex justify-center rounded-xl bg-[#24695c] px-5 py-3 text-sm font-semibold text-white"
                        >
                            Coba Gratis →
                        </Link>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
