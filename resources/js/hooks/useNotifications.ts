import { router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useEffect, useMemo, useState } from 'react';

import { echo } from '@/lib/echo';
import type {
    NotificationDropdownItem,
    NotificationsContext,
    PageProps,
} from '@/types';

interface NotificationCreatedPayload extends NotificationDropdownItem {
    type: string;
}

interface RecentNotificationsResponse {
    success: boolean;
    data: NotificationsContext;
    message: string;
}

interface UseNotificationsResult {
    notifications: NotificationDropdownItem[];
    unreadCount: number;
    markAsRead: (id: string, url: string | null) => void;
    markAllRead: () => void;
    loadInitial: () => Promise<void>;
}

export function useNotifications(): UseNotificationsResult {
    const { props } = usePage<PageProps>();
    const userId = props.auth.user?.id ?? null;
    const initial = useMemo(
        () => props.notifications ?? { unreadCount: 0, recent: [] },
        [props.notifications],
    );
    const [notifications, setNotifications] = useState(initial.recent);
    const [unreadCount, setUnreadCount] = useState(initial.unreadCount);

    useEffect(() => {
        setNotifications(initial.recent);
        setUnreadCount(initial.unreadCount);
    }, [initial]);

    const loadInitial = useCallback(async (): Promise<void> => {
        const response = await axios.get<RecentNotificationsResponse>(
            route('notifications.recent'),
        );

        setNotifications(response.data.data.recent);
        setUnreadCount(response.data.data.unreadCount);
    }, []);

    const markAsRead = useCallback((id: string, url: string | null): void => {
        router.patch(
            route('notifications.read', { notification: id }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setNotifications((current) =>
                        current.map((item) =>
                            item.id === id
                                ? {
                                      ...item,
                                      readAt:
                                          item.readAt ??
                                          new Date().toISOString(),
                                  }
                                : item,
                        ),
                    );
                    setUnreadCount((current) => Math.max(current - 1, 0));

                    if (url) {
                        window.location.href = url;
                    }
                },
            },
        );
    }, []);

    const markAllRead = useCallback((): void => {
        router.patch(
            route('notifications.read-all'),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    const readAt = new Date().toISOString();

                    setNotifications((current) =>
                        current.map((item) => ({
                            ...item,
                            readAt: item.readAt ?? readAt,
                        })),
                    );
                    setUnreadCount(0);
                },
            },
        );
    }, []);

    useEffect(() => {
        if (userId === null || echo === null) {
            return;
        }

        const realtime = echo;
        const channelName = `App.Models.User.${userId}`;
        const channel = realtime.private(channelName);

        channel.listen(
            '.UserNotificationCreated',
            (payload: NotificationCreatedPayload) => {
                setNotifications((current) => [
                    payload,
                    ...current.filter((item) => item.id !== payload.id),
                ].slice(0, 5));
                setUnreadCount((current) =>
                    payload.readAt === null ? current + 1 : current,
                );
            },
        );

        return () => {
            realtime.leave(channelName);
        };
    }, [userId]);

    return {
        notifications,
        unreadCount,
        markAsRead,
        markAllRead,
        loadInitial,
    };
}
