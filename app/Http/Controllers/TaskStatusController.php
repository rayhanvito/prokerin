<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Task\UpdateTaskStatusAction;
use App\Domain\Task\TaskStatus;
use App\Http\Requests\UpdateTaskStatusRequest;
use Illuminate\Http\RedirectResponse;

final class TaskStatusController extends Controller
{
    public function update(
        UpdateTaskStatusRequest $request,
        int $task,
        UpdateTaskStatusAction $updateTaskStatus,
    ): RedirectResponse {
        $updateTaskStatus->execute(
            actorUserId: (int) $request->user()->id,
            taskId: $task,
            status: TaskStatus::from((string) $request->validated('status')),
        );

        return back()->with('success', 'Status task berhasil diperbarui.');
    }
}
