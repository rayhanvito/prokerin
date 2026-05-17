import InventoryShow from './Show';
import type { InventoryPayload } from './types';

export default function InventoryQrLookup(props: InventoryPayload) {
    return <InventoryShow {...props} />;
}
