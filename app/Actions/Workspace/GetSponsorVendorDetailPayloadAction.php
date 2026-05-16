<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetSponsorVendorDetailPayloadAction
{
    /**
     * @return array{contact: array{id: int, type: string, name: string, category: string, contactPerson: string, phone: string, email: string, address: string, status: string, notes: string}, projects: array<int, array{id: int, name: string, slug: string, roleDescription: string, amount: int, linkedAt: string}>, documents: array<int, array{id: int, name: string, folder: string, visibility: string, status: string}>}
     */
    public function execute(int $actorUserId, int $sponsorVendorId): array
    {
        $contact = DB::table('sponsors_vendors')
            ->join('organization_members', 'organization_members.organization_id', '=', 'sponsors_vendors.organization_id')
            ->where('sponsors_vendors.id', $sponsorVendorId)
            ->where('organization_members.user_id', $actorUserId)
            ->first([
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
            ]);

        if ($contact === null) {
            throw new NotFoundHttpException('Sponsor/vendor contact was not found for this workspace.');
        }

        return [
            'contact' => [
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
            ],
            'projects' => $this->projects((int) $contact->id),
            'documents' => $this->documents((int) $contact->id),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, roleDescription: string, amount: int, linkedAt: string}>
     */
    private function projects(int $sponsorVendorId): array
    {
        return DB::table('sponsor_vendor_project_links')
            ->join('projects', 'projects.id', '=', 'sponsor_vendor_project_links.project_id')
            ->where('sponsor_vendor_project_links.sponsor_vendor_id', $sponsorVendorId)
            ->orderByDesc('sponsor_vendor_project_links.linked_at')
            ->get([
                'projects.id',
                'projects.name',
                'projects.slug',
                'sponsor_vendor_project_links.role_description',
                'sponsor_vendor_project_links.amount',
                'sponsor_vendor_project_links.linked_at',
            ])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
                'slug' => (string) $project->slug,
                'roleDescription' => (string) $project->role_description,
                'amount' => (int) $project->amount,
                'linkedAt' => (string) $project->linked_at,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, folder: string, visibility: string, status: string}>
     */
    private function documents(int $sponsorVendorId): array
    {
        return DB::table('sponsor_vendor_documents')
            ->join('documents', 'documents.id', '=', 'sponsor_vendor_documents.document_id')
            ->where('sponsor_vendor_documents.sponsor_vendor_id', $sponsorVendorId)
            ->orderBy('documents.name')
            ->get([
                'documents.id',
                'documents.name',
                'documents.folder',
                'documents.visibility',
                'documents.status',
            ])
            ->map(static fn (object $document): array => [
                'id' => (int) $document->id,
                'name' => (string) $document->name,
                'folder' => (string) $document->folder,
                'visibility' => (string) $document->visibility,
                'status' => (string) $document->status,
            ])
            ->all();
    }
}
