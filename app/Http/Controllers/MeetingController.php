<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Meeting\CreateMeetingAction;
use App\Actions\Meeting\UpdateMeetingAction;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class MeetingController extends Controller
{
    public function store(StoreMeetingRequest $request, CreateMeetingAction $createMeeting): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $organizationId = $this->resolveActiveOrganizationId($userId);

        if ($organizationId === null) {
            throw new AuthorizationException('Tidak ada organisasi aktif untuk membuat rapat.');
        }

        $payload = $request->validated();

        $createMeeting->execute($userId, $organizationId, [
            'title' => (string) $payload['title'],
            'agenda' => (string) $payload['agenda'],
            'starts_at' => (string) $payload['starts_at'],
            'ends_at' => $payload['ends_at'] ?? null,
            'location' => $payload['location'] ?? null,
            'project_id' => isset($payload['project_id']) ? (int) $payload['project_id'] : null,
            'attendee_user_ids' => array_map(static fn ($id): int => (int) $id, $payload['attendee_user_ids'] ?? []),
        ]);

        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dibuat.');
    }

    public function update(
        UpdateMeetingRequest $request,
        int $meeting,
        UpdateMeetingAction $updateMeeting,
    ): RedirectResponse {
        $updateMeeting->execute((int) $request->user()->id, $meeting, $request->validated());

        return back()->with('success', 'Rapat berhasil diperbarui.');
    }

    private function resolveActiveOrganizationId(int $userId): ?int
    {
        $organization = DB::table('organization_members')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->first(['organization_id']);

        return $organization === null ? null : (int) $organization->organization_id;
    }
}
