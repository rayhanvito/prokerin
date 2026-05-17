export function isOverdue(dueAt: string | null): boolean {
    if (!dueAt) {
        return false;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const dueDate = new Date(`${dueAt}T00:00:00`);

    return dueDate < today;
}
