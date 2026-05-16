import { AnimatePresence, motion } from 'framer-motion';
import { Mail, X } from 'lucide-react';
import { useEffect, useRef } from 'react';

interface DemoVideoModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export default function DemoVideoModal({
    isOpen,
    onClose,
}: DemoVideoModalProps) {
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
        };

        document.addEventListener('keydown', handleKeyDown);

        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onClose]);

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    className="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="demo-modal-title"
                    onMouseDown={onClose}
                >
                    <motion.div
                        className="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl"
                        initial={{ opacity: 0, scale: 0.92 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.92 }}
                        transition={{ duration: 0.2 }}
                        onMouseDown={(event) => event.stopPropagation()}
                    >
                        <button
                            ref={closeButtonRef}
                            type="button"
                            onClick={onClose}
                            aria-label="Tutup demo video"
                            className="absolute right-4 top-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 text-[#242934] shadow-sm ring-1 ring-[#e6edef]"
                        >
                            <X className="h-5 w-5" />
                        </button>
                        <div className="aspect-video bg-[#f5f7fb] p-8">
                            <div className="flex h-full flex-col items-center justify-center rounded-2xl border border-dashed border-[#e6edef] bg-white text-center">
                                <p
                                    id="demo-modal-title"
                                    className="font-['Plus_Jakarta_Sans'] text-2xl font-bold text-[#242934]"
                                >
                                    Demo video akan segera tersedia.
                                </p>
                                <p className="mt-3 max-w-md text-sm leading-6 text-[#59667a]">
                                    Ingin melihat demo langsung? Kami bisa
                                    atur sesi 30 menit khusus untuk pengurus
                                    inti organisasimu.
                                </p>
                                <a
                                    href="mailto:halo@prokerin.id"
                                    className="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#24695c] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#24695c]/20"
                                >
                                    <Mail className="h-4 w-4" />
                                    Hubungi Kami
                                </a>
                            </div>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
