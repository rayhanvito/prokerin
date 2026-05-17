<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Task\UpdateTaskPicAction;
use App\Http\Requests\AssignTaskPicRequest;
use Illuminate\Http\RedirectResponse;

final class TaskPicController extends Controller
{
    public function update(
        AssignTaskPicRequest $request,
        int $task,
        UpdateTaskPicAction $updateTaskPic,
    ): RedirectResponse {
        $updateTaskPic->execute(
            actorUserId: (int) $request->user()->id,
            taskId: $task,
            picUserId: (int) $request->validated('pic_user_id'),
        );

        return back()->with('success', 'PIC task berhasil diperbarui.');
    }
}
