export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface ImpersonationContext {
    active: boolean;
    impersonator: string | null;
    leaveUrl: string;
}

export interface OnboardingStep {
    key: string;
    label: string;
    complete: boolean;
    href: string | null;
}

export interface OnboardingContext {
    show: boolean;
    organizationId: number | null;
    organizationName: string | null;
    steps: OnboardingStep[];
    completeUrl: string;
}

export interface NotificationDropdownItem {
    id: string;
    title: string;
    body: string;
    url: string | null;
    readAt: string | null;
    createdAt: string;
}

export interface NotificationsContext {
    unreadCount: number;
    recent: NotificationDropdownItem[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    app: {
        name: string;
        activeOrganization: {
            name: string;
            period: string;
            role: string;
        };
    };
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
        status?: string;
        aiSuggestion?: Record<string, unknown>;
        attendanceQrToken?: {
            sessionId: number;
            tokenId: number;
            plainToken: string;
            expiresAt: string;
        };
    };
    impersonating: ImpersonationContext | null;
    onboarding: OnboardingContext | null;
    notifications: NotificationsContext | null;
};
