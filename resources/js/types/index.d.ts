import type Pusher from 'pusher-js';

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
    currentStep: number;
    completedSteps: number[];
    steps: OnboardingStep[];
    completeUrl: string;
    stepCompleteUrl: string;
    skipUrl: string;
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

declare global {
    interface Window {
        Pusher: typeof Pusher;
    }
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
            mode: 'organization' | 'kepanitiaan';
            eventDate: string | null;
            autoArchiveAt: string | null;
        };
        webPush: {
            enabled: boolean;
            publicKey: string | null;
            subscribed: boolean;
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

// === SEO ===
export interface SeoProps {
    title: string;
    description: string;
    ogImage: string; // absolute URL, mis. https://prokerin.id/og-image.png
    canonical: string; // absolute URL, mis. https://prokerin.id/
}

// === Landing page-specific props (extend the shared PageProps and add `seo`) ===
export type LandingHomeProps = PageProps<{ seo: SeoProps }>;
export type LandingFeaturesProps = PageProps<{ seo: SeoProps }>;
export type LandingPricingProps = PageProps<{ seo: SeoProps }>;

// === Domain types untuk Landing components ===
export interface PricingTier {
    id: 'free' | 'starter' | 'pro' | 'campus';
    name: string;
    price: string; // "Rp 0", "Rp 49.000", dll.
    period: string; // "/bulan", "/tahun", "—"
    description: string;
    features: string[]; // bullet yang dimiliki tier ini
    missing: string[]; // bullet yang tidak dimiliki
    badge: string | null; // "Paling Populer" pada Pro, null untuk yang lain
    ctaLabel: string;
    ctaHref: string;
}

export interface FeatureItem {
    icon:
        | 'FolderKanban'
        | 'CheckSquare'
        | 'FileText'
        | 'Wallet'
        | 'QrCode'
        | 'Award';
    title: string;
    description: string;
}

export interface ProblemItem {
    icon: 'FileX' | 'LayoutDashboard' | 'Calculator';
    title: string;
    description: string;
}

export interface HowItWorksStep {
    number: number;
    title: string;
    description: string;
}

export interface FaqItem {
    question: string;
    answer: string;
}

// === Analytics ===
export type AnalyticsEvent =
    | 'landing_cta_primary_clicked'
    | 'landing_cta_secondary_clicked'
    | 'landing_pricing_tier_clicked'
    | 'landing_video_played'
    | 'landing_signup_completed'
    | 'landing_scroll_25'
    | 'landing_scroll_50'
    | 'landing_scroll_75'
    | 'landing_scroll_100';

export type AnalyticsEventProps = Record<
    string,
    string | number | boolean | null
>;
