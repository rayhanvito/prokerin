import { Head } from '@inertiajs/react';
import { Award, CheckCircle2, QrCode, ShieldCheck, XCircle } from 'lucide-react';
import { QRCodeSVG } from 'qrcode.react';

interface VerifiedCertificate {
    certificateNumber: string;
    recipientName: string;
    recipientEmail: string | null;
    templateName: string;
    organizationName: string;
    projectName: string | null;
    meetingTitle: string | null;
    issuedAt: string;
    signatureLabel: string | null;
    signatureName: string | null;
    hasPdf: boolean;
    verificationUrl: string;
}

interface CertificateVerifyProps {
    isValid: boolean;
    certificate: VerifiedCertificate | null;
}

export default function CertificateVerify({
    isValid,
    certificate,
}: CertificateVerifyProps) {
    return (
        <main className="min-h-screen bg-[#f5f7fb] px-4 py-10 text-[#242934] sm:px-6 lg:px-8">
            <Head title="Verifikasi Sertifikat" />

            <div className="mx-auto max-w-3xl">
                <div className="mb-6 flex items-center gap-3">
                    <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-[#24695c] text-white">
                        <Award className="h-6 w-6" />
                    </span>
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                            Prokerin Verify
                        </p>
                        <h1 className="text-2xl font-semibold">
                            Verifikasi Sertifikat
                        </h1>
                    </div>
                </div>

                {isValid && certificate ? (
                    <section className="rounded-[4px] bg-white p-6 shadow-sm ring-1 ring-[#e6edef]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <CheckCircle2 className="h-8 w-8 text-[#24695c]" />
                                <div>
                                    <p className="font-semibold">
                                        Sertifikat valid
                                    </p>
                                    <p className="mt-1 text-sm text-[#59667a]">
                                        Nomor {certificate.certificateNumber}
                                    </p>
                                </div>
                            </div>
                            <span className="inline-flex items-center gap-2 rounded-[4px] bg-[rgba(36,105,92,0.1)] px-3 py-2 text-sm font-semibold text-[#24695c]">
                                <ShieldCheck className="h-4 w-4" />
                                Public record
                            </span>
                        </div>

                        <div className="mt-8 grid gap-6 lg:grid-cols-[1fr_220px]">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Detail label="Penerima" value={certificate.recipientName} />
                                <Detail label="Organisasi" value={certificate.organizationName} />
                                <Detail label="Template" value={certificate.templateName} />
                                <Detail label="Tanggal terbit" value={certificate.issuedAt} />
                                <Detail
                                    label="Proker"
                                    value={certificate.projectName ?? 'Tidak terkait proker'}
                                />
                                <Detail
                                    label="Rapat"
                                    value={certificate.meetingTitle ?? 'Tidak terkait rapat'}
                                />
                            </div>

                            <div className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4 text-center">
                                <div className="mx-auto inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#24695c]">
                                    <QrCode className="h-4 w-4" />
                                    QR Verifikasi
                                </div>
                                <div className="mt-4 inline-flex rounded-[4px] bg-white p-3 ring-1 ring-[#e6edef]">
                                    <QRCodeSVG
                                        value={certificate.verificationUrl}
                                        size={156}
                                        level="M"
                                        includeMargin
                                        title={`QR verifikasi ${certificate.certificateNumber}`}
                                    />
                                </div>
                                <p className="mt-3 break-all text-xs leading-5 text-[#59667a]">
                                    {certificate.verificationUrl}
                                </p>
                            </div>
                        </div>

                        <div className="mt-8 rounded-[4px] bg-[#f5f7fb] p-4">
                            <p className="text-sm font-semibold text-[#59667a]">
                                Penanda tangan
                            </p>
                            <p className="mt-2 text-lg font-semibold">
                                {certificate.signatureName ?? '-'}
                            </p>
                            <p className="mt-1 text-sm text-[#717171]">
                                {certificate.signatureLabel ?? '-'}
                            </p>
                        </div>
                    </section>
                ) : (
                    <section className="rounded-[4px] bg-white p-6 shadow-sm ring-1 ring-[#e6edef]">
                        <div className="flex items-center gap-3">
                            <XCircle className="h-8 w-8 text-[#d22d3d]" />
                            <div>
                                <p className="font-semibold">
                                    Sertifikat tidak ditemukan
                                </p>
                                <p className="mt-1 text-sm text-[#59667a]">
                                    Token verifikasi tidak cocok dengan data
                                    sertifikat Prokerin.
                                </p>
                            </div>
                        </div>
                    </section>
                )}
            </div>
        </main>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-[4px] bg-[#f5f7fb] p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                {label}
            </p>
            <p className="mt-2 font-semibold text-[#242934]">{value}</p>
        </div>
    );
}
