import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY as string | undefined;
const reverbHost = import.meta.env.VITE_REVERB_HOST as string | undefined;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 80);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

export const echo =
    reverbKey && reverbHost
        ? new Echo({
              broadcaster: 'reverb',
              key: reverbKey,
              wsHost: reverbHost,
              wsPort: reverbPort,
              wssPort: reverbPort,
              forceTLS: reverbScheme === 'https',
              enabledTransports: ['ws', 'wss'],
          })
        : null;
