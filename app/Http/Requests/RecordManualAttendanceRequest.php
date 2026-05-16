<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class RecordManualAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $sessionId = $this->route('session');

        if ($user === null || ! is_numeric($sessionId)) {
            return false;
        }

        return DB::table('attendance_sessions')
            ->join('organization_members', 'organization_members.organization_id', '=', 'attendance_sessions.organization_id')
            ->where('attendance_sessions.id', (int) $sessionId)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary', 'project_lead'])
            ->exists();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'meeting_attendee_id' => ['required', 'integer', 'exists:meeting_attendees,id'],
        ];
    }
}
