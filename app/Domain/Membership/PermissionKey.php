<?php

declare(strict_types=1);

namespace App\Domain\Membership;

enum PermissionKey: string
{
    case ManageOrganization = 'manage_organization';
    case ManageMembers = 'manage_members';
    case ManageProjects = 'manage_projects';
    case ManageFinance = 'manage_finance';
    case ApproveBudget = 'approve_budget';
    case ManageDocuments = 'manage_documents';
    case ViewReports = 'view_reports';
    case ViewCampusDashboard = 'view_campus_dashboard';
}
