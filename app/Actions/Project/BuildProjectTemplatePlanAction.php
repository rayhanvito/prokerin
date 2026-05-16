<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Finance\BudgetStatus;
use App\Domain\Project\ProjectTemplateType;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Project\ProjectTemplatePlanData;
use App\DTOs\Project\TemplateTaskData;
use App\Support\ValueObjects\Money;

final class BuildProjectTemplatePlanAction
{
    public function execute(ProjectTemplateType $templateType): ProjectTemplatePlanData
    {
        return match ($templateType) {
            ProjectTemplateType::Seminar => $this->seminar(),
            ProjectTemplateType::Workshop => $this->workshop(),
            ProjectTemplateType::Competition => $this->competition(),
            ProjectTemplateType::Makrab => $this->makrab(),
        };
    }

    private function seminar(): ProjectTemplatePlanData
    {
        return new ProjectTemplatePlanData(
            templateType: ProjectTemplateType::Seminar,
            proposalOutline: 'Latar belakang, tujuan, sasaran peserta, profil narasumber, rundown, kebutuhan anggaran, dan indikator keberhasilan seminar.',
            tasks: [
                new TemplateTaskData('Susun TOR dan proposal seminar', 'Sekretaris', -30),
                new TemplateTaskData('Konfirmasi narasumber utama', 'Acara', -24),
                new TemplateTaskData('Finalisasi rundown dan moderator brief', 'Acara', -10),
                new TemplateTaskData('Publikasi poster dan registrasi peserta', 'Humas', -14),
                new TemplateTaskData('Kumpulkan dokumentasi dan materi', 'Dokumentasi', 1),
            ],
            budgetLines: [
                $this->budget('Honor narasumber', 'Program', 1500000),
                $this->budget('Konsumsi peserta', 'Konsumsi', 6500000),
                $this->budget('Publikasi dan printing', 'Marketing', 1750000),
            ],
            lpjChecklist: [
                'Daftar hadir peserta',
                'Dokumentasi kegiatan',
                'Materi narasumber',
                'Rekap realisasi anggaran',
            ],
        );
    }

    private function workshop(): ProjectTemplatePlanData
    {
        return new ProjectTemplatePlanData(
            templateType: ProjectTemplateType::Workshop,
            proposalOutline: 'Latar belakang, capaian belajar, struktur sesi praktik, kebutuhan mentor, perlengkapan kelas, dan evaluasi peserta.',
            tasks: [
                new TemplateTaskData('Susun kurikulum workshop', 'Acara', -28),
                new TemplateTaskData('Konfirmasi mentor dan asisten kelas', 'Acara', -21),
                new TemplateTaskData('Siapkan modul praktik', 'Materi', -14),
                new TemplateTaskData('Tes perangkat kelas dan koneksi', 'Logistik', -3),
            ],
            budgetLines: [
                $this->budget('Honor mentor', 'Program', 2500000),
                $this->budget('Modul dan sertifikat', 'Administrasi', 1200000),
                $this->budget('Konsumsi peserta', 'Konsumsi', 4000000),
            ],
            lpjChecklist: [
                'Rekap kehadiran peserta',
                'Dokumentasi sesi praktik',
                'Hasil evaluasi peserta',
                'Rekap realisasi anggaran',
            ],
        );
    }

    private function competition(): ProjectTemplatePlanData
    {
        return new ProjectTemplatePlanData(
            templateType: ProjectTemplateType::Competition,
            proposalOutline: 'Latar belakang lomba, ketentuan peserta, mekanisme registrasi, rubrik penilaian, kebutuhan juri, hadiah, dan publikasi.',
            tasks: [
                new TemplateTaskData('Finalisasi rulebook lomba', 'Acara', -35),
                new TemplateTaskData('Buka registrasi peserta', 'Humas', -28),
                new TemplateTaskData('Konfirmasi juri dan rubrik penilaian', 'Acara', -21),
                new TemplateTaskData('Siapkan hadiah dan sertifikat', 'Logistik', -7),
            ],
            budgetLines: [
                $this->budget('Honor juri', 'Program', 3000000),
                $this->budget('Hadiah pemenang', 'Hadiah', 5000000),
                $this->budget('Sertifikat dan publikasi', 'Marketing', 1500000),
            ],
            lpjChecklist: [
                'Daftar peserta dan pemenang',
                'Berita acara penjurian',
                'Dokumentasi final',
                'Rekap realisasi anggaran',
            ],
        );
    }

    private function makrab(): ProjectTemplatePlanData
    {
        return new ProjectTemplatePlanData(
            templateType: ProjectTemplateType::Makrab,
            proposalOutline: 'Latar belakang, tujuan internalisasi, agenda kegiatan, kebutuhan transportasi, konsumsi, penginapan, dan mitigasi risiko.',
            tasks: [
                new TemplateTaskData('Validasi peserta dan izin kegiatan', 'Sekretaris', -30),
                new TemplateTaskData('Booking lokasi dan transportasi', 'Logistik', -24),
                new TemplateTaskData('Susun agenda bonding dan refleksi', 'Acara', -14),
                new TemplateTaskData('Briefing panitia lapangan', 'Ketua Pelaksana', -2),
            ],
            budgetLines: [
                $this->budget('Transportasi', 'Logistik', 4500000),
                $this->budget('Penginapan dan venue', 'Venue', 8000000),
                $this->budget('Konsumsi peserta', 'Konsumsi', 7000000),
            ],
            lpjChecklist: [
                'Daftar hadir peserta',
                'Dokumentasi agenda',
                'Catatan evaluasi internal',
                'Rekap realisasi anggaran',
            ],
        );
    }

    private function budget(string $name, string $category, int $plannedAmount): BudgetLineData
    {
        return new BudgetLineData(
            name: $name,
            category: $category,
            plannedAmount: Money::rupiah($plannedAmount),
            realizedAmount: Money::rupiah(0),
            status: BudgetStatus::Draft,
        );
    }
}
