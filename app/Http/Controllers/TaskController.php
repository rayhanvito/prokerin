<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Task\CreateTaskAction;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\RedirectResponse;

final class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, CreateTaskAction $createTask): RedirectResponse
    {
        $createTask->execute(
            actorUserId: (int) $request->user()->id,
            projectId: (int) $request->validated('project_id'),
            title: (string) $request->validated('title'),
            dueAt: $request->validated('due_at') === null ? null : (string) $request->validated('due_at'),
        );

        return back()->with('success', 'Task berhasil dibuat.');
    }
}
