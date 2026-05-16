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
};
