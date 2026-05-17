import { router } from '@inertiajs/react';
import { Camera, CameraOff, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

interface Props {
    open: boolean;
    onClose: () => void;
}

interface ScanState {
    status: 'idle' | 'starting' | 'scanning' | 'submitting' | 'error';
    message: string | null;
}

export default function QrCameraScanner({ open, onClose }: Props) {
    const containerRef = useRef<HTMLDivElement | null>(null);
    const scannerRef = useRef<unknown>(null);
    const cooldownRef = useRef<number | null>(null);
    const isSubmittingRef = useRef(false);
    const [state, setState] = useState<ScanState>({
        status: 'idle',
        message: null,
    });

    useEffect(() => {
        if (!open) {
            return;
        }

        let cancelled = false;

        const start = async () => {
            setState({ status: 'starting', message: 'Memuat kamera...' });

            try {
                const mod = await import('html5-qrcode');
                const Html5Qrcode = mod.Html5Qrcode;

                if (cancelled || containerRef.current === null) {
                    return;
                }

                const scanner = new Html5Qrcode('prokerin-qr-reader');
                scannerRef.current = scanner;

                await scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 8,
                        qrbox: { width: 240, height: 240 },
                    },
                    (decodedText: string) => {
                        handleDecode(decodedText);
                    },
                    () => {
                        // ignore decode failures
                    },
                );

                if (!cancelled) {
                    setState({
                        status: 'scanning',
                        message: 'Arahkan kamera ke QR code peserta.',
                    });
                }
            } catch (error) {
                const reason =
                    error instanceof Error
                        ? error.message
                        : 'Tidak bisa mengakses kamera.';
                setState({ status: 'error', message: reason });
            }
        };

        start();

        return () => {
            cancelled = true;
            isSubmittingRef.current = false;

            const scanner = scannerRef.current as
                | { stop: () => Promise<void>; clear: () => void }
                | null;

            if (scanner) {
                scanner
                    .stop()
                    .catch(() => undefined)
                    .finally(() => {
                        try {
                            scanner.clear();
                        } catch {
                            // noop
                        }
                    });
            }

            if (cooldownRef.current !== null) {
                window.clearTimeout(cooldownRef.current);
                cooldownRef.current = null;
            }
        };
    }, [open]);

    const handleDecode = (token: string) => {
        if (isSubmittingRef.current) {
            return;
        }

        isSubmittingRef.current = true;
        setState({ status: 'submitting', message: 'Mengirim check-in...' });

        router.post(
            route('attendance.check-in.store'),
            { token, method: 'qr_camera' },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Check-in tercatat.');
                    setState({
                        status: 'scanning',
                        message: 'Check-in tercatat. Siap scan berikutnya.',
                    });
                    cooldownRef.current = window.setTimeout(() => {
                        isSubmittingRef.current = false;
                    }, 2000);
                },
                onError: () => {
                    toast.error('Token tidak valid atau sudah expired.');
                    setState({
                        status: 'error',
                        message:
                            'Token tidak valid atau sudah expired. Coba lagi.',
                    });
                    cooldownRef.current = window.setTimeout(() => {
                        isSubmittingRef.current = false;
                        setState({
                            status: 'scanning',
                            message:
                                'Arahkan kamera ke QR code peserta.',
                        });
                    }, 2500);
                },
            },
        );
    };

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div className="w-full max-w-md rounded-[6px] bg-white p-5 shadow-lg">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2 text-sm font-semibold text-[#242934]">
                        <Camera className="h-4 w-4 text-[#24695c]" />
                        Scan QR Absensi
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Tutup scanner"
                        className="text-[#59667a] hover:text-[#242934]"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <div
                id="prokerin-qr-reader"
                ref={containerRef}
                    className="mt-4 min-h-[240px] overflow-hidden rounded-[4px] bg-black"
                />

                <p
                    role="status"
                    aria-live="polite"
                    className="mt-3 text-xs text-[#59667a]"
                >
                    {state.message ?? 'Menunggu...'}
                </p>

                {state.status === 'error' && (
                    <p className="mt-2 inline-flex items-center gap-2 rounded-[4px] bg-[rgba(210,45,61,0.05)] px-2 py-1 text-xs font-semibold text-[#d22d3d]">
                        <CameraOff className="h-3.5 w-3.5" />
                        {state.message}
                    </p>
                )}

                <button
                    type="button"
                    onClick={onClose}
                    className="mt-4 inline-flex w-full items-center justify-center rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm font-semibold text-[#24695c] ring-1 ring-[#e6edef] hover:bg-white"
                >
                    Pakai Manual Code
                </button>
            </div>
        </div>
    );
}
