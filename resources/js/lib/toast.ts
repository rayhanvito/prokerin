import { toast } from 'sonner';

import { PageProps } from '@/types';

export function showFlashToast(flash: PageProps['flash']): void {
    if (flash.success) {
        toast.success(flash.success);

        return;
    }

    if (flash.error) {
        toast.error(flash.error);

        return;
    }

    if (flash.status) {
        toast.message(flash.status);
    }
}
