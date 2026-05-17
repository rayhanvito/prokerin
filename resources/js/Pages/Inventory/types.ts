export interface InventoryItem {
    id: number;
    name: string;
    category: string;
    location: string | null;
    condition: string;
    conditionLabel: string;
    status: string;
    statusLabel: string;
    qrToken: string;
    updatedAt: string;
    description?: string | null;
    photoPath?: string | null;
    purchasedAt?: string | null;
    purchaseAmount?: number | null;
    qrUrl?: string;
}

export interface InventoryLoan {
    id: number;
    status: string;
    borrowerName: string;
    projectName: string | null;
    approvedBy: string | null;
    loanedAt: string | null;
    expectedReturnAt: string;
    returnedAt: string | null;
    returnCondition: string | null;
    notes: string | null;
}

export interface InventoryOption {
    value: string;
    label: string;
}

export interface InventoryPayload {
    metrics: {
        total: number;
        available: number;
        loaned: number;
        needsAttention: number;
    };
    items: InventoryItem[];
    item: InventoryItem | null;
    loans: InventoryLoan[];
    projects: { id: number; name: string }[];
    canManage: boolean;
    options: {
        conditions: InventoryOption[];
        statuses: InventoryOption[];
        returnConditions: InventoryOption[];
    };
}
