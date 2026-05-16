<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EventRegistrationExportController extends Controller
{
    public function show(Request $request): StreamedResponse
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $request->user()?->id)
            ->pluck('organization_id');

        $fileName = 'event-registrations-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($organizationIds): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Event', 'Nama Peserta', 'Email', 'Telepon', 'Institusi', 'Status', 'Tanggal Registrasi']);

            DB::table('event_registrations')
                ->join('projects', 'projects.id', '=', 'event_registrations.project_id')
                ->whereIn('projects.organization_id', $organizationIds)
                ->orderBy('projects.name')
                ->orderBy('event_registrations.participant_name')
                ->get([
                    'projects.name as project_name',
                    'event_registrations.participant_name',
                    'event_registrations.participant_email',
                    'event_registrations.phone',
                    'event_registrations.institution',
                    'event_registrations.status',
                    'event_registrations.registered_at',
                ])
                ->each(static function (object $registration) use ($handle): void {
                    fputcsv($handle, [
                        $registration->project_name,
                        $registration->participant_name,
                        $registration->participant_email,
                        $registration->phone,
                        $registration->institution,
                        $registration->status,
                        $registration->registered_at,
                    ]);
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
