import axios from 'axios';

interface PushSubscriptionPayload {
    endpoint: string;
    expirationTime: number | null;
    keys: {
        p256dh: string;
        auth: string;
    };
}

export async function requestPermissionAndSubscribe(
    publicKey: string,
): Promise<void> {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        throw new Error('Browser belum mendukung web push.');
    }

    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        throw new Error('Izin notifikasi belum diberikan.');
    }

    const registration = await navigator.serviceWorker.ready;
    const existingSubscription =
        await registration.pushManager.getSubscription();
    const subscription =
        existingSubscription ??
        (await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey),
        }));

    await axios.post(route('webpush.subscribe'), serialize(subscription));
}

export async function unsubscribeWebPush(): Promise<void> {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    if (subscription === null) {
        return;
    }

    await axios.delete(route('webpush.unsubscribe'), {
        data: { endpoint: subscription.endpoint },
    });
    await subscription.unsubscribe();
}

function serialize(subscription: PushSubscription): PushSubscriptionPayload {
    const payload = subscription.toJSON();

    return {
        endpoint: subscription.endpoint,
        expirationTime: payload.expirationTime ?? null,
        keys: {
            p256dh: String(payload.keys?.p256dh ?? ''),
            auth: String(payload.keys?.auth ?? ''),
        },
    };
}

function urlBase64ToUint8Array(value: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (value.length % 4)) % 4);
    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(new ArrayBuffer(rawData.length));

    for (let index = 0; index < rawData.length; index += 1) {
        outputArray[index] = rawData.charCodeAt(index);
    }

    return outputArray;
}
