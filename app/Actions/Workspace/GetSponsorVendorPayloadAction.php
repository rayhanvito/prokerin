<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetSponsorVendorPayloadAction
{
    /**
     * @return array{filters: array{search: string, type: string}, canManage: bool, metrics: array{total: int, active: int, sponsors: int, vendors: int}, contacts: array<int, array{id: int, type: string, name: string, category: string, contactPerson: string, phone: string, email: string, address: string, status: string, notes: string, linkedProjects: int, totalAmount: int, documents: int}>}
     */
    public function execute(int $actorUserId, ?string $search = null, ?string $type = null): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->pluck('organization_id');
        $canManage = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->exists();

        $normalizedSearch = trim((string) $search);
        $normalizedType = in_array($type, ['sponsor', 'vendor'], true) ? (string) $type : 'all';

        $baseQuery = DB::table('sponsors_vendors')
            ->whereIn('organization_id', $organizationIds);

        $contacts = (clone $baseQuery)
            ->when($normalizedType !== 'all', static function ($query) use ($normalizedType): void {
                $query->where('type', $normalizedType);
            })
            ->when($normalizedSearch !== '', static function ($query) use ($normalizedSearch): void {
                $query->where(function ($nested) use ($normalizedSearch): void {
                    $nested->where('name', 'like', "%{$normalizedSearch}%")
                        ->orWhere('category', 'like', "%{$normalizedSearch}%")
                        ->orWhere('contact_person', 'like', "%{$normalizedSearch}%");
                });
            })
            ->leftJoin('sponsor_vendor_project_links', 'sponsor_vendor_project_links.sponsor_vendor_id', '=', 'sponsors_vendors.id')
            ->leftJoin('sponsor_vendor_documents', 'sponsor_vendor_documents.sponsor_vendor_id', '=', 'sponsors_vendors.id')
            ->select([
                'sponsors_vendors.id',
                'sponsors_vendors.type',
                'sponsors_vendors.name',
                'sponsors_vendors.category',
                'sponsors_vendors.contact_person',
                'sponsors_vendors.phone',
                'sponsors_vendors.email',
                'sponsors_vendors.address',
                'sponsors_vendors.status',
                'sponsors_vendors.notes',
                DB::raw('count(distinct sponsor_vendor_project_links.project_id) as linked_projects'),
                DB::raw('coalesce(sum(distinct sponsor_vendor_project_links.amount), 0) as total_amount'),
                DB::raw('count(distinct sponsor_vendor_documents.document_id) as documents'),
            ])
            ->groupBy([
                'sponsors_vendors.id',
                'sponsors_vendors.type',
                'sponsors_vendors.name',
                'sponsors_vendors.category',
                'sponsors_vendors.contact_person',
                'sponsors_vendors.phone',
                'sponsors_vendors.email',
                'sponsors_vendors.address',
                'sponsors_vendors.status',
                'sponsors_vendors.notes',
            ])
            ->orderByRaw("case sponsors_vendors.status when 'active' then 0 else 1 end")
            ->orderBy('sponsors_vendors.name')
            ->limit(50)
            ->get()
            ->map(static fn (object $contact): array => [
                'id' => (int) $contact->id,
                'type' => (string) $contact->type,
                'name' => (string) $contact->name,
                'category' => (string) $contact->category,
                'contactPerson' => (string) ($contact->contact_person ?? '-'),
                'phone' => (string) ($contact->phone ?? '-'),
                'email' => (string) ($contact->email ?? '-'),
                'address' => (string) ($contact->address ?? ''),
                'status' => (string) $contact->status,
                'notes' => (string) ($contact->notes ?? ''),
                'linkedProjects' => (int) $contact->linked_projects,
                'totalAmount' => (int) $contact->total_amount,
                'documents' => (int) $contact->documents,
            ])
            ->all();

        return [
            'filters' => [
                'search' => $normalizedSearch,
                'type' => $normalizedType,
            ],
            'canManage' => $canManage,
            'metrics' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('status', 'active')->count(),
                'sponsors' => (clone $baseQuery)->where('type', 'sponsor')->count(),
                'vendors' => (clone $baseQuery)->where('type', 'vendor')->count(),
            ],
            'contacts' => $contacts,
        ];
    }
}
