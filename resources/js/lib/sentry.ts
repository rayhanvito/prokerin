import * as Sentry from '@sentry/react';

export function initSentry(): void {
    const dsn = import.meta.env.VITE_SENTRY_DSN;

    if (!dsn) {
        return;
    }

    Sentry.init({
        dsn,
        environment: import.meta.env.VITE_SENTRY_ENVIRONMENT,
        release: import.meta.env.VITE_SENTRY_RELEASE,
        tracesSampleRate: Number(
            import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE ?? 0,
        ),
        beforeSend(event, hint) {
            const statusCode = hint.originalException
                && typeof hint.originalException === 'object'
                && 'status' in hint.originalException
                ? Number(hint.originalException.status)
                : undefined;

            if (statusCode !== undefined && [401, 403, 404, 422].includes(statusCode)) {
                return null;
            }

            return event;
        },
    });
}
