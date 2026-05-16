# QA-MASTER.md — Prokerin
## Senior QA Engineer — Full Platform Test Plan, Bug Report Template & Improvement Recommendations

> **When to use this document:** After all modules M01–M20, M28.5, SA01, and L01 have been marked `[x]` in `features.md`.
> Run this QA pass before any public beta launch or paid plan enablement.
> This document is the single source of truth for: manual test cases, automated test strategy, regression scope, UX/flow findings, and improvement recommendations.
> Any agent or developer executing this must have read `AGENTS.md` and `features.md` in full first.

---

## 0. How to Use This Document

### Reading Order
1. Read `Section 1` — Environment Setup (do not skip)
2. Read `Section 2` — Test User Matrix (seed these accounts first)
3. Execute `Section 3` — Regression (automated, run once before starting manual)
4. Execute `Section 4–18` — Manual test cases per module (top to bottom)
5. Execute `Section 19` — Cross-Module Integration Flows (most important)
6. Execute `Section 20` — Security & Multi-Tenancy Audit
7. Execute `Section 21` — Performance & Load Checks
8. Execute `Section 22` — Mobile & PWA Checks
9. Read `Section 23` — UX/Flow Improvement Recommendations
10. Read `Section 24` — Technical Debt & Architecture Recommendations
11. Record all findings in `Section 25` — Bug Report Log

### Status Symbols
| Symbol | Meaning |
|--------|---------|
| `[ ]` | Not yet tested |
| `[P]` | Pass |
| `[F]` | Fail — log in Section 25 |
| `[S]` | Skip (not applicable in this environment) |

### Severity Levels (for bug reports)
| Level | Definition |
|-------|-----------|
| `CRITICAL` | Data loss, security breach, authentication bypass, cross-tenant leak |
| `HIGH` | Core workflow broken (cannot create proker, cannot submit proposal) |
| `MEDIUM` | Feature partially broken, workaround exists |
| `LOW` | Minor UI issue, copy error, cosmetic defect |
| `ENHANCEMENT` | Not a bug — improvement suggestion |

---

## 1. Environment Setup

### 1.1 Prerequisites Before Starting QA

```bash
# 1. Confirm PHP version
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php --version
# Must show PHP 8.4.x

# 2. Run full migration fresh (staging/QA environment only — never production)
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate:fresh --seed

# 3. Run full test suite — must be 100% green before starting manual QA
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test

# 4. Build frontend — no TypeScript or Vite errors allowed
npm run build

# 5. Start queue worker in a separate terminal (needed for export/email/WhatsApp jobs)
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan queue:work

# 6. Start the application
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan serve
npm run dev
```

### 1.2 Browser Matrix

Run every manual test case in at minimum:
- Chrome (latest stable) on desktop
- Safari (latest stable) on desktop
- Chrome on Android (mobile viewport simulation: 375px and 414px)
- Safari on iOS (mobile viewport simulation: 375px)

### 1.3 Viewport Breakpoints to Check for Every Page

| Breakpoint | Width | Notes |
|-----------|-------|-------|
| Mobile S | 375px | iPhone SE — minimum supported |
| Mobile L | 414px | iPhone Pro Max |
| Tablet | 768px | iPad portrait |
| Desktop | 1280px | Standard laptop |
| Wide | 1536px | External monitor |

---

## 2. Test User Matrix

Ensure all the following accounts exist after `php artisan migrate:fresh --seed`:

| Email | Role | Purpose |
|-------|------|---------|
| `owner@prokerin.test` | organization_owner | Full access, approval authority |
| `admin@prokerin.test` | organization_admin | Delegated approval |
| `secretary@prokerin.test` | secretary | Document/meeting focus |
| `treasurer@prokerin.test` | treasurer | Finance focus |
| `lead@prokerin.test` | project_lead | Assigned to at least 1 proker |
| `coordinator@prokerin.test` | division_coordinator | Division scope |
| `member@prokerin.test` | member | Assigned tasks only |
| `viewer@prokerin.test` | viewer | Read-only, no actions |
| `owner2@prokerin.test` | organization_owner | **Different organization** — for cross-tenant tests |
| `superadmin@prokerin.internal` | super_admin | Filament admin panel |

All passwords: `password` (dev/staging only).

---

## 3. Automated Regression Gate

Run before every manual QA session. If any test fails, stop and fix before continuing.

```bash
# Full suite
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test

# Key test files (spot-check)
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test \
  tests/Feature/Dashboard/DashboardVariantRoutingTest.php \
  tests/Feature/Dashboard/SidebarMenuActionTest.php \
  tests/Feature/MultiLevelApprovalWorkflowTest.php \
  tests/Feature/ProposalApprovalTest.php \
  tests/Feature/LpjApprovalTest.php \
  tests/Feature/BudgetApprovalDecisionTest.php \
  tests/Feature/HandoverPackageTest.php \
  tests/Feature/DigitalCertificateTest.php \
  tests/Feature/SponsorVendorTest.php \
  tests/Feature/WorkspaceRouteSmokeTest.php \
  tests/Feature/WorkspacePayloadTest.php \
  tests/Unit/Dashboard/DashboardRoleResolverActionTest.php

# TypeScript
npm run build

# Lint
npm run lint
./vendor/bin/pint --test
```

Expected baseline: **256+ passed, 1287+ assertions** (add new tests as SA01 completes).

---

## 4. Auth & Account Management (M01)

### 4.1 Registration Flow

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 4.1.1 | Register with valid data | Fill name, email, password, confirm password → Submit | Account created, redirect to email verification or dashboard | `[ ]` |
| 4.1.2 | Register with duplicate email | Use existing email | Validation error "email sudah digunakan" | `[ ]` |
| 4.1.3 | Register with weak password | Password < 8 chars | Validation error | `[ ]` |
| 4.1.4 | Register with mismatched confirm | Different confirm password | Validation error | `[ ]` |
| 4.1.5 | Google OAuth register | Click "Masuk dengan Google" | OAuth flow completes, account created/linked, redirect to dashboard | `[ ]` |
| 4.1.6 | Email verification prompt | Register without verifying | Prompt to verify shown, restricted access | `[ ]` |

### 4.2 Login Flow

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 4.2.1 | Login with valid credentials | Email + password | Redirect to /dashboard | `[ ]` |
| 4.2.2 | Login with wrong password | Wrong password | Error, NOT revealing if email exists | `[ ]` |
| 4.2.3 | Login with unregistered email | Random email | Error shown | `[ ]` |
| 4.2.4 | "Remember me" functionality | Check remember me, close browser, reopen | Still logged in | `[ ]` |
| 4.2.5 | Logout | Click logout | Session cleared, redirect to login | `[ ]` |
| 4.2.6 | Access protected route as guest | Visit /dashboard directly | Redirect to /login | `[ ]` |

### 4.3 Password Reset

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 4.3.1 | Request reset with valid email | Submit forgot password form | Success message (no email enumeration leak) | `[ ]` |
| 4.3.2 | Reset with valid token | Click email link, enter new password | Password updated, redirect to login | `[ ]` |
| 4.3.3 | Reset with expired token | Use old/expired link | Error: token expired | `[ ]` |
| 4.3.4 | Profile update | Edit name, email | Changes saved, reflected in header | `[ ]` |

---

## 5. Organization Management (M02)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 5.1 | Create organization | Fill name, slug, type → Submit | Org created, redirect to setup | `[ ]` |
| 5.2 | Duplicate slug | Use same slug as existing org | Validation error | `[ ]` |
| 5.3 | Upload org logo | Upload PNG/JPG ≤ 2MB | Logo saved, displayed in sidebar header | `[ ]` |
| 5.4 | Upload invalid logo | Upload .exe or >2MB file | MIME/size validation error | `[ ]` |
| 5.5 | Create active period | Name + start date + end date | Period created, becomes active | `[ ]` |
| 5.6 | Switch organization | User in multiple orgs → switch via switcher | Dashboard data changes to switched org | `[ ]` |
| 5.7 | Organization calendar | Navigate to calendar view | Events/proker displayed on calendar | `[ ]` |
| 5.8 | Edit organization name | Change name → Save | Updated name in sidebar and all pages | `[ ]` |

---

## 6. Member & Role Management (M03)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 6.1 | Invite member | Enter email + role → Send | Invitation queued, appears in invite list | `[ ]` |
| 6.2 | Duplicate invite | Invite already-invited email | Duplicate blocked | `[ ]` |
| 6.3 | Accept invitation | Log in as invited user, accept | User added to org with correct role | `[ ]` |
| 6.4 | Decline invitation | Invited user declines | Removed from invite queue | `[ ]` |
| 6.5 | Role promotion | Owner promotes member to treasurer | Role updated, treasurer sees finance in sidebar | `[ ]` |
| 6.6 | Role demotion | Owner demotes admin to member | Admin loses approval access | `[ ]` |
| 6.7 | Last owner protection | Owner tries to remove themselves as last owner | Blocked with clear error | `[ ]` |
| 6.8 | Remove member | Owner removes a member | User loses access to org | `[ ]` |
| 6.9 | Member cannot change roles | Member visits /members | No role-edit controls visible | `[ ]` |
| 6.10 | Role matrix display | Owner visits /members/roles | Permission matrix renders correctly per role | `[ ]` |

---

## 7. Proker / Event Management (M04)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 7.1 | Create proker manually | Fill all fields, no template | Proker created, appears in index | `[ ]` |
| 7.2 | Create with template | Choose template → one-click generate | Proker + tasks + RAB + proposal scaffold created atomically | `[ ]` |
| 7.3 | Proker detail page | Click on any proker | Detail loads with progress, tasks, members, finance | `[ ]` |
| 7.4 | Edit proker | Change name, dates → Save | Updated, slug regenerated if name changed | `[ ]` |
| 7.5 | Status transition | Planning → Active → Completed | Status changes, progress reflected | `[ ]` |
| 7.6 | Archive proker | Change status to Archived | Proker hidden from active list, accessible in archive | `[ ]` |
| 7.7 | Member cannot create proker | Log in as member → try /proker/create | 403 or redirect | `[ ]` |
| 7.8 | Proker progress calculation | Complete all tasks | Progress shows 100% | `[ ]` |
| 7.9 | Duplicate slug protection | Create two prokers with same name in same org | Second gets unique slug | `[ ]` |

---

## 8. Template Proker (M05)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 8.1 | View template library | Navigate to /templates | All seeded templates visible | `[ ]` |
| 8.2 | One-click template generate | Select template → Generate | Creates proker + tasks + RAB lines + proposal outline + LPJ checklist atomically | `[ ]` |
| 8.3 | Template fields prefill | After generate, check proker | Name, description, timeline prefilled from template | `[ ]` |
| 8.4 | Customize after generate | Edit generated proker | All fields editable | `[ ]` |
| 8.5 | Generate twice from same template | Use same template again | Two separate prokers created, no conflict | `[ ]` |

---

## 9. Timeline & Task Management (M06)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 9.1 | Kanban board loads | Navigate to proker → Tasks | Board displays with columns: Belum Dimulai, Sedang Dikerjakan, Selesai | `[ ]` |
| 9.2 | Task status advance | Click task to advance status | Status changes, progress updated | `[ ]` |
| 9.3 | Assign PIC | Assign member as PIC | Assignee sees task in their dashboard | `[ ]` |
| 9.4 | Assign non-member as PIC | Try to assign user not in org | Blocked — membership guard | `[ ]` |
| 9.5 | Calendar view | Switch to calendar view | Tasks appear on correct dates | `[ ]` |
| 9.6 | Overdue task display | Task past deadline, not complete | Overdue state visible (red badge or label) | `[ ]` |
| 9.7 | Task quick-add | Quick-add task from board | Task created in correct column | `[ ]` |
| 9.8 | Member sees only assigned tasks | Log in as member → dashboard | Only own assigned tasks visible, not all org tasks | `[ ]` |

---

## 10. RAB & Finance (M07)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 10.1 | Create budget line | Add line item with amount | Line appears in RAB table with correct total | `[ ]` |
| 10.2 | Edit budget line | Change amount | Total recalculates | `[ ]` |
| 10.3 | Delete budget line | Remove a line | Removed, total updates | `[ ]` |
| 10.4 | Upload receipt | Upload receipt image for realization | Receipt stored, download link generated | `[ ]` |
| 10.5 | Receipt signed URL | Click download receipt | Signed URL generated, file downloaded | `[ ]` |
| 10.6 | Upload non-image receipt | Upload .exe file | MIME validation blocks | `[ ]` |
| 10.7 | Submit realization for approval | Submit transaction | Status → Pending Approval | `[ ]` |
| 10.8 | Treasurer approves | Log in as treasurer → approve | Status → Approved, budget updated | `[ ]` |
| 10.9 | Treasurer rejects | Reject with note | Status → Rejected, submitter notified | `[ ]` |
| 10.10 | RAB vs Realization summary | View finance overview | Bar chart shows RAB total vs approved realization | `[ ]` |
| 10.11 | Member cannot access finance | Log in as member → /finance | 403 or redirect | `[ ]` |
| 10.12 | Remaining budget calculation | After approvals | Remaining = RAB total − approved realization (correct math) | `[ ]` |

---

## 11. Proposal Generator (M08)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 11.1 | Auto-fill from project data | Open proposal for proker with data | Sections pre-filled with project name, dates, objectives | `[ ]` |
| 11.2 | Edit section body | Click on section, type content | Content saved on blur/save | `[ ]` |
| 11.3 | Submit proposal | Change status to Submitted | Status locked, editing disabled | `[ ]` |
| 11.4 | Owner approves proposal | Log in as owner → Approve | Status → Approved | `[ ]` |
| 11.5 | Owner requests revision | Log in as owner → Request Revision | Status → Revision Requested, editor re-enabled | `[ ]` |
| 11.6 | Export proposal to PDF | Trigger export | Job queued, file generated, download link appears | `[ ]` |
| 11.7 | Export proposal to DOCX | Trigger DOCX export | File generated and downloadable | `[ ]` |
| 11.8 | Cannot edit submitted proposal | Submit → try to edit | Fields are locked/read-only | `[ ]` |
| 11.9 | Member cannot approve | Log in as member → try to approve | No approve button visible | `[ ]` |

---

## 12. Document Management (M09)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 12.1 | Upload document | Upload PDF ≤ allowed size | Stored in S3, appears in documents list | `[ ]` |
| 12.2 | Upload oversized file | Upload > max size | Size validation error | `[ ]` |
| 12.3 | Download private document | As authorized member | Signed URL generated, file downloads | `[ ]` |
| 12.4 | Download restricted document | As member without access | 403 | `[ ]` |
| 12.5 | Visibility rules | Upload as 'private' | Only uploader and owner can see | `[ ]` |
| 12.6 | Committee document | Upload as 'committee' | Committee members can see, regular members cannot | `[ ]` |
| 12.7 | Public document | Upload as 'public' | All authenticated org members can see | `[ ]` |
| 12.8 | Folder structure | Navigate folders | Correct hierarchy shown | `[ ]` |
| 12.9 | Recent documents | Upload center | Shows 5 most recently uploaded | `[ ]` |
| 12.10 | Cross-tenant document | org2 member tries to download org1 document | 403 | `[ ]` |

---

## 13. LPJ Generator (M10)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 13.1 | LPJ checklist loads | Navigate to proker → LPJ | Checklist items shown | `[ ]` |
| 13.2 | Mark checklist item done | Check an item | Saved, progress bar updates | `[ ]` |
| 13.3 | Readiness guard | Submit with incomplete items | Blocked, shows which items missing | `[ ]` |
| 13.4 | Submit complete LPJ | Complete all items → Submit | Status → Review Submitted | `[ ]` |
| 13.5 | Owner approves LPJ | Log in as owner → Approve | Status → Approved | `[ ]` |
| 13.6 | Request revision | Owner requests revision | Status back to Draft, re-editable | `[ ]` |
| 13.7 | LPJ export PDF | Trigger PDF export | Job queued, file generated | `[ ]` |
| 13.8 | LPJ data from execution | Completed tasks appear in LPJ context | Task completion data referenced in checklist | `[ ]` |

---

## 14. Dashboard Monitoring (M11 + M28.5)

### 14.1 Role-Aware Variants

| # | Test Case | User | Expected Dashboard | Status |
|---|-----------|------|-------------------|--------|
| 14.1.1 | Owner gets Pimpinan | Login as owner | KPI cards + Approval Queue + Priority Projects + Finance Summary | `[ ]` |
| 14.1.2 | Admin gets Pimpinan | Login as admin | Same as owner variant | `[ ]` |
| 14.1.3 | Secretary gets Sekretaris | Login as secretary | Proposal Status + LPJ Checklist + Rapat Pending (NO finance widget) | `[ ]` |
| 14.1.4 | Treasurer gets Bendahara | Login as treasurer | Finance KPIs + Budget vs Realization (NO task kanban) | `[ ]` |
| 14.1.5 | Project Lead gets Operasional | Login as lead | Own proker tasks + progress (ONLY own proker data) | `[ ]` |
| 14.1.6 | Member gets Member | Login as member | Only own assigned tasks | `[ ]` |
| 14.1.7 | Viewer gets restricted view | Login as viewer | Read-only view, no action buttons | `[ ]` |
| 14.1.8 | Multi-role resolution | User with owner + member | Owner dashboard shown (highest role wins) | `[ ]` |

### 14.2 Sidebar Role Filtering

| # | Test Case | User | Expected | Status |
|---|-----------|------|----------|--------|
| 14.2.1 | Member sidebar | Login as member | RAB & Keuangan NOT visible | `[ ]` |
| 14.2.2 | Treasurer sidebar | Login as treasurer | Anggota (role management) NOT visible | `[ ]` |
| 14.2.3 | Secretary sidebar | Login as secretary | Serah Terima NOT visible | `[ ]` |
| 14.2.4 | Owner sidebar | Login as owner | All menu items visible including Serah Terima | `[ ]` |
| 14.2.5 | Approval badge | Pending approval items exist | Badge count correct in sidebar | `[ ]` |
| 14.2.6 | Task badge | Member has pending tasks | Badge count correct | `[ ]` |
| 14.2.7 | Org switch clears sidebar | Switch org | Sidebar menu reloads for new org context | `[ ]` |

### 14.3 Dashboard KPI Accuracy

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 14.3.1 | Proker count | Create 3 active proker | KPI shows 3 | `[ ]` |
| 14.3.2 | Member count | 5 members in org | KPI shows 5 | `[ ]` |
| 14.3.3 | Budget remaining | RAB 10M, approved realization 3M | KPI shows Rp7.000.000 | `[ ]` |
| 14.3.4 | Cross-org isolation | Owner of org A | Sees ONLY org A data on dashboard | `[ ]` |

---

## 15. Notifications (M12 + M17)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 15.1 | Task deadline reminder | Task due < 24h → trigger reminder | Notification created in database | `[ ]` |
| 15.2 | Email channel | Notification with email channel | Email queued (check queue log) | `[ ]` |
| 15.3 | WhatsApp channel | WhatsApp enabled, task due | WhatsApp job queued, delivery log created | `[ ]` |
| 15.4 | WhatsApp opt-out | Disable WhatsApp channel for org | No WhatsApp job queued | `[ ]` |
| 15.5 | WhatsApp delivery log | After WA job runs | `whatsapp_delivery_logs` record created with status | `[ ]` |
| 15.6 | Proposal approval notification | Owner approves proposal | Submitter receives notification | `[ ]` |
| 15.7 | LPJ revision notification | Owner requests revision | LPJ submitter notified | `[ ]` |
| 15.8 | Meeting alert | Trigger meeting alert | WhatsApp queued for all attendees with WA enabled | `[ ]` |
| 15.9 | Notification rules page | Visit /notifications | Rules, channels, and delivery log render | `[ ]` |
| 15.10 | Simulate reminder | Click "Simulate" button | Job dispatched, flash success shown | `[ ]` |

---

## 16. Meeting & Minutes (M14)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 16.1 | Meeting list loads | Navigate to /meetings | Meeting cards with agenda, date, attendee count | `[ ]` |
| 16.2 | Meeting metrics | Top of /meetings | Count of total meetings, published minutes, etc. | `[ ]` |
| 16.3 | Attendee count display | Click meeting | Shows total invited vs present | `[ ]` |
| 16.4 | Latest decisions display | Meeting with published minutes | Decisions and action items shown | `[ ]` |
| 16.5 | Tenant scope | Login as owner2 (different org) | Sees ONLY own org's meetings | `[ ]` |

---

## 17. QR Attendance (M15)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 17.1 | Attendance list loads | Navigate to /attendance | Session list, metrics shown | `[ ]` |
| 17.2 | Valid check-in | Submit valid token | Check-in recorded, success shown | `[ ]` |
| 17.3 | Duplicate scan | Submit same token twice | Error: already checked in | `[ ]` |
| 17.4 | Expired token | Submit expired token | Error: token expired | `[ ]` |
| 17.5 | Cross-tenant token | Submit token from org2 in org1 context | Error: rejected | `[ ]` |
| 17.6 | Manual attendance | Owner manually records attendance | Record created | `[ ]` |
| 17.7 | Manual attendance role guard | Member tries manual attendance | 403 | `[ ]` |
| 17.8 | Session metrics | View attendance session | QR count, manual count, present %, shown | `[ ]` |

---

## 18. Digital Certificate (M16)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 18.1 | Certificate list | Navigate to /certificates | Issued certificates with stats shown | `[ ]` |
| 18.2 | Create template | Fill template HTML + signature → Save | Template created, appears in list | `[ ]` |
| 18.3 | Edit template | Edit existing template | Changes saved | `[ ]` |
| 18.4 | Activate/deactivate template | Toggle is_active | Status changes | `[ ]` |
| 18.5 | Issue to members | Select template + members → Issue | PDF job queued, certificates appear | `[ ]` |
| 18.6 | Certificate number format | Check issued certificate number | Format: PRK-{YEAR}-{ORG_SLUG}-{SEQUENCE} | `[ ]` |
| 18.7 | Download certificate | Authenticated member downloads | Signed URL → PDF downloaded | `[ ]` |
| 18.8 | Public verification | Visit /verify/{token} without login | Verification page loads, shows recipient details + QR | `[ ]` |
| 18.9 | Invalid verification token | Visit /verify/fake-token | 404 or "Certificate not found" | `[ ]` |
| 18.10 | Cross-tenant | org2 tries to download org1 certificate | 403 | `[ ]` |
| 18.11 | Non-owner cannot issue | Login as member → try to issue | No issue button visible / 403 | `[ ]` |

---

## 19. Multi-Level Approval Workflow (M18)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 19.1 | Full workflow execution | Proposal → submit → step 1 approves → step 2 approves | Final status: Approved | `[ ]` |
| 19.2 | Rejection at step 2 | Step 1 approves, step 2 rejects | Workflow terminated, proposal Rejected | `[ ]` |
| 19.3 | Revision request | Step 1 requests revision | Subject status → Revision Requested, submitter can edit | `[ ]` |
| 19.4 | Workflow timeline display | View proposal with active workflow | Timeline shows step sequence and current step | `[ ]` |
| 19.5 | Cross-tenant approval | org2 approver tries org1 workflow | Blocked — 403 | `[ ]` |
| 19.6 | Delegate approval | Approver delegates to another member | New approver assigned, step advances correctly | `[ ]` |
| 19.7 | Delegation logged | After delegation | `approval_delegations` record created | `[ ]` |
| 19.8 | Next-step notification | Workflow advances to step 2 | Step 2 approver notified (in-app + WhatsApp if enabled) | `[ ]` |
| 19.9 | Finance approval queue | Log in as treasurer | Pending finance items with approve/reject controls | `[ ]` |

---

## 20. Handover Kepengurusan (M19)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 20.1 | Create handover package | Owner at /organization/handover → Create | Draft package with generated checklist | `[ ]` |
| 20.2 | Mark checklist item done | Check an item | Item status → done, reverted | `[ ]` |
| 20.3 | Assign transition | Owner assigns incoming owner + recipient period | Transition details saved | `[ ]` |
| 20.4 | Submit package | All items done → Submit | Status → Submitted | `[ ]` |
| 20.5 | Accept package | Incoming owner accepts | Status → Accepted | `[ ]` |
| 20.6 | Export handover PDF | Accepted package → Export | PDF job queued, downloadable | `[ ]` |
| 20.7 | Non-owner cannot initiate | Login as member | No create handover button | `[ ]` |
| 20.8 | Incoming owner view | Login as designated incoming owner | Sees incoming summary on handover page | `[ ]` |

---

## 21. Sponsor & Vendor Database (M20)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 21.1 | Vendor list | Navigate to /organization/sponsors-vendors | List with search, type filter | `[ ]` |
| 21.2 | Filter by type | Filter "vendor" | Only vendors shown | `[ ]` |
| 21.3 | Search | Search "Audio" | Matching contacts returned | `[ ]` |
| 21.4 | Create contact | Owner fills form → Save | Contact created | `[ ]` |
| 21.5 | Edit contact | Owner edits → Save | Changes persisted | `[ ]` |
| 21.6 | View detail page | Click contact | Profile, project history, linked documents shown | `[ ]` |
| 21.7 | Member cannot create | Login as member | No create button / 403 | `[ ]` |
| 21.8 | Cross-tenant | org2 tries to access org1 vendor | 403 | `[ ]` |

---

## 22. Super Admin Panel (SA01)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 22.1 | Guest redirect | Visit /internal-admin without login | Redirect to login | `[ ]` |
| 22.2 | Non-super access | Login as organization_owner → visit /internal-admin | 403 | `[ ]` |
| 22.3 | Super admin access | Login as superadmin@prokerin.internal | Lands on /internal-admin dashboard | `[ ]` |
| 22.4 | Platform stats | Dashboard | User count, org count, active projects count shown | `[ ]` |
| 22.5 | Users list | /internal-admin/users | All users from all orgs listed | `[ ]` |
| 22.6 | Edit user | Edit name → Save | Updated | `[ ]` |
| 22.7 | Force verify email | Toggle email verified | Updated | `[ ]` |
| 22.8 | Organizations list | /internal-admin/organizations | All orgs listed | `[ ]` |
| 22.9 | Change plan tier | Edit org plan tier | Changed, logged to activity_logs | `[ ]` |
| 22.10 | Impersonate user | Click Impersonate on a member | Redirected to /dashboard as that member, banner visible | `[ ]` |
| 22.11 | Impersonation banner | During impersonation | "You are impersonating [Name]" banner visible in Inertia app | `[ ]` |
| 22.12 | Stop impersonating | Click "Stop Impersonating" | Back to /internal-admin, banner gone | `[ ]` |
| 22.13 | Cannot impersonate super_admin | Try to impersonate another super_admin | Blocked | `[ ]` |
| 22.14 | Activity logs written | After impersonation and plan change | Records in activity_logs table | `[ ]` |
| 22.15 | Projects read-only | /internal-admin/projects | List visible, no edit/delete buttons | `[ ]` |

---

## 23. Landing Page (L01)

| # | Test Case | Steps | Expected | Status |
|---|-----------|-------|----------|--------|
| 23.1 | Home page loads | Visit / | Hero, all sections, footer visible | `[ ]` |
| 23.2 | Navbar sticky | Scroll down | Navbar turns white with shadow | `[ ]` |
| 23.3 | Mobile menu | At 375px → click hamburger | Full-screen overlay opens | `[ ]` |
| 23.4 | Mobile menu close | Click X or outside | Menu closes | `[ ]` |
| 23.5 | Demo video modal | Click "Lihat Demo" | Modal opens with YouTube embed | `[ ]` |
| 23.6 | Modal close | Click X, click backdrop, press Escape | Modal closes | `[ ]` |
| 23.7 | CTA links | All "Coba Gratis" buttons | Link to /register | `[ ]` |
| 23.8 | FAQ accordion | Click a question | Answer expands smoothly | `[ ]` |
| 23.9 | Pricing toggle | Toggle Bulanan/Tahunan | Prices update | `[ ]` |
| 23.10 | Features page | Visit /features | All feature sections with correct hierarchy | `[ ]` |
| 23.11 | Pricing page | Visit /pricing | Pricing table, FAQ section shown | `[ ]` |
| 23.12 | No horizontal overflow | At 375px | No horizontal scroll bar | `[ ]` |
| 23.13 | All images have alt text | Inspect DOM | No img without alt | `[ ]` |
| 23.14 | External links | Check all external links | Have rel="noopener noreferrer" and target="_blank" | `[ ]` |
| 23.15 | SEO meta tags | View page source | Title, description, OG tags, canonical present | `[ ]` |
| 23.16 | No console errors | Open DevTools during full page tour | Zero console errors | `[ ]` |

---

## 24. Cross-Module Integration Flows

These are the most important tests. They verify that modules work together correctly in real user journeys.

### Flow 1 — Full Proker Lifecycle (End-to-End)

```
Owner creates org → Sets active period → Invites members →
Member accepts invite →
Owner creates proker from template →
  → Tasks auto-created → RAB auto-created → Proposal auto-drafted → LPJ scaffold created
Lead assigns tasks to members →
Members update task statuses →
Lead submits proposal for approval →
Owner approves proposal (M18 workflow executes) →
Treasurer records RAB realization → Uploads receipt →
Finance approval workflow executes →
All tasks completed →
Lead submits LPJ →
Owner approves LPJ →
Owner generates LPJ PDF export →
Owner issues digital certificates to all members who completed tasks →
Members verify certificates at public /verify/{token}
```

| Step | Test | Status |
|------|------|--------|
| F1.1 | All steps execute without error | `[ ]` |
| F1.2 | Progress percentage reaches 100% when all tasks done | `[ ]` |
| F1.3 | Certificate verification link works publicly | `[ ]` |
| F1.4 | No orphan records in database after full cycle | `[ ]` |

### Flow 2 — Multi-Level Approval Chain

```
Project Lead submits proposal →
M18 workflow starts at step 1 (Treasurer) →
Treasurer approves → notification sent to Secretary (step 2) →
Secretary approves → notification sent to Owner (step 3) →
Owner approves → Proposal status → Approved
```

| Step | Test | Status |
|------|------|--------|
| F2.1 | Each step notifications fire correctly | `[ ]` |
| F2.2 | WhatsApp notification queued at each step (if enabled) | `[ ]` |
| F2.3 | Final approval syncs proposal status to Approved | `[ ]` |
| F2.4 | Rejection at any step terminates workflow | `[ ]` |

### Flow 3 — Board Transition (Handover)

```
Active period ends →
Owner initiates handover package →
Fills in all checklist items →
Assigns incoming owner →
Submits package →
Incoming owner accepts →
Exports handover PDF →
New period starts with incoming owner as new organization_owner
```

| Step | Test | Status |
|------|------|--------|
| F3.1 | Handover checklist items generated from actual org data | `[ ]` |
| F3.2 | Incoming owner sees transition summary before accepting | `[ ]` |
| F3.3 | PDF export contains all checklist items and completion status | `[ ]` |

### Flow 4 — QR Attendance at a Meeting

```
Secretary creates meeting → Adds attendees →
Attendance session created → QR token generated → QR code displayed →
Members scan QR with camera (M27) or enter token manually →
Check-in recorded per member →
Secretary publishes meeting minutes →
After event: Owner issues digital certificates to attendees
```

| Step | Test | Status |
|------|------|--------|
| F4.1 | QR token → check-in → attendance record created | `[ ]` |
| F4.2 | Duplicate scan rejected | `[ ]` |
| F4.3 | Certificate issued to attendees only | `[ ]` |

### Flow 5 — Dashboard Data Accuracy After Actions

```
At T=0: Owner dashboard shows 0 pending approvals
→ Member submits proposal
At T=1: Owner dashboard shows 1 pending approval (badge updates)
→ Owner approves
At T=2: Owner dashboard shows 0 pending approvals
→ Approval badge resets
```

| Step | Test | Status |
|------|------|--------|
| F5.1 | Approval queue count accurate | `[ ]` |
| F5.2 | Sidebar badge count accurate | `[ ]` |
| F5.3 | Finance remaining budget updates after approval | `[ ]` |

### Flow 6 — Cross-Org Data Isolation (Most Critical)

```
org1_owner and org2_owner both log in →
All data checks below must pass simultaneously
```

| Step | What to Verify | Status |
|------|---------------|--------|
| F6.1 | org2_owner visits /dashboard — sees ZERO org1 data | `[ ]` |
| F6.2 | org2_owner visits /proker — sees ZERO org1 projects | `[ ]` |
| F6.3 | org2_owner visits /finance — sees ZERO org1 budget lines | `[ ]` |
| F6.4 | org2_owner tries GET /proker/{org1_proker_slug} — 404 or 403 | `[ ]` |
| F6.5 | org2_owner sidebar badges show ONLY org2 data | `[ ]` |
| F6.6 | org2_owner's approval queue contains ONLY org2 items | `[ ]` |
| F6.7 | org2_owner cannot download org1 document | `[ ]` |
| F6.8 | org2_owner cannot verify org1 certificate (auth download route) | `[ ]` |

---

## 25. Security & Authorization Audit

### 25.1 Authentication Bypass Attempts

| # | Test | Expected | Status |
|---|------|----------|--------|
| S1.1 | Access /dashboard without session | Redirect to /login | `[ ]` |
| S1.2 | Access /proker/create without session | Redirect to /login | `[ ]` |
| S1.3 | Access /finance without session | Redirect to /login | `[ ]` |
| S1.4 | Access /internal-admin without session | Redirect to login | `[ ]` |
| S1.5 | Manually craft session cookie from another user | No access — session validation | `[ ]` |

### 25.2 Role Escalation Attempts

| # | Test | Expected | Status |
|---|------|----------|--------|
| S2.1 | Member POSTs to /proposals/{id}/approve | 403 | `[ ]` |
| S2.2 | Member POSTs to /finance/approve | 403 | `[ ]` |
| S2.3 | Member tries to invite new members | 403 | `[ ]` |
| S2.4 | Member tries to create proker | 403 | `[ ]` |
| S2.5 | Viewer tries any mutation | 403 on all POST/PUT/PATCH/DELETE | `[ ]` |

### 25.3 Insecure Direct Object Reference (IDOR)

| # | Test | Expected | Status |
|---|------|----------|--------|
| S3.1 | org2_member accesses /proker/{org1_slug} | 404 or 403 | `[ ]` |
| S3.2 | org2_member accesses /documents/{org1_doc_id}/download | 403 | `[ ]` |
| S3.3 | org2_member submits POST with org1's organization_id in body | Ignored — server derives org from session | `[ ]` |
| S3.4 | org2_member accesses /certificates/{org1_cert_id}/download | 403 | `[ ]` |
| S3.5 | org2_member accesses /organization/handover (org1 route) | Scoped to own org, org1 data not visible | `[ ]` |

### 25.4 File Upload Security

| # | Test | Expected | Status |
|---|------|----------|--------|
| S4.1 | Upload PHP file as document | MIME validation blocks | `[ ]` |
| S4.2 | Upload .exe as receipt | MIME validation blocks | `[ ]` |
| S4.3 | Upload SVG with XSS payload | Blocked or sanitized | `[ ]` |
| S4.4 | Direct S3 URL access | Unsigned URL returns 403 from S3 | `[ ]` |
| S4.5 | Signed URL expiry | Use signed URL after expiry | Access denied | `[ ]` |

### 25.5 CSRF Protection

| # | Test | Expected | Status |
|---|------|----------|--------|
| S5.1 | POST /proposals without CSRF token | 419 CSRF mismatch | `[ ]` |
| S5.2 | POST /finance without CSRF token | 419 | `[ ]` |
| S5.3 | DELETE /documents/{id} without CSRF token | 419 | `[ ]` |

### 25.6 SQL Injection Probes (Basic)

| # | Test | Expected | Status |
|---|------|----------|--------|
| S6.1 | Search input: `'; DROP TABLE organizations; --` | No DB error, result empty or normal | `[ ]` |
| S6.2 | Slug in URL: `/proker/' OR '1'='1` | 404, no SQL error exposed | `[ ]` |
| S6.3 | Budget amount field: `1; DELETE FROM budget_lines` | Validation error (not a number) | `[ ]` |

---

## 26. Performance & Load Checks

### 26.1 Page Load Benchmarks

Run with browser DevTools → Network tab. Test on production build (`npm run build` + `APP_ENV=production`).

| Page | Target Load | Status |
|------|-------------|--------|
| /dashboard (owner) | < 1.5s | `[ ]` |
| /proker (list, 20 prokers) | < 1.5s | `[ ]` |
| /finance (full RAB table) | < 2s | `[ ]` |
| /attendance (full session list) | < 1.5s | `[ ]` |
| Landing page / | < 2s LCP | `[ ]` |

### 26.2 N+1 Query Check

Use Laravel Debugbar or Telescope in local to check query count per page:

| Page | Max Acceptable Queries | Status |
|------|----------------------|--------|
| /dashboard | < 15 queries | `[ ]` |
| /proker (index) | < 10 queries | `[ ]` |
| /proker/{slug} (detail) | < 20 queries | `[ ]` |
| /finance | < 15 queries | `[ ]` |
| /certificates | < 15 queries | `[ ]` |

### 26.3 Queue Job Performance

| Job | Expected Max Completion | Status |
|-----|------------------------|--------|
| GenerateProposalPdfJob | < 30s | `[ ]` |
| GenerateLpjPdfJob | < 30s | `[ ]` |
| GenerateCertificatePdfJob | < 20s | `[ ]` |
| SendWhatsAppReminderJob | < 10s | `[ ]` |

---

## 27. Mobile & PWA Checks

| # | Test | Steps | Expected | Status |
|---|------|-------|----------|--------|
| M1 | Sidebar on mobile | Open app at 375px | Sidebar collapses, hamburger toggle works | `[ ]` |
| M2 | Dashboard readable | 375px | No text overflow, KPI cards stack correctly | `[ ]` |
| M3 | Finance table mobile | View RAB table at 375px | Horizontal scroll on table (not whole page) | `[ ]` |
| M4 | Kanban board mobile | View task kanban at 375px | Columns scroll horizontally | `[ ]` |
| M5 | Forms usable mobile | Fill any form at 375px | Inputs not obstructed by keyboard | `[ ]` |
| M6 | PWA installable | Chrome on Android → Add to Home Screen | App installs, opens in standalone mode | `[ ]` |
| M7 | PWA manifest | Check /manifest.json | Valid JSON with name, icons, start_url | `[ ]` |
| M8 | PWA icon quality | Launch from home screen | 192x192 icon renders crisply | `[ ]` |

---

## 28. UX / Flow Improvement Recommendations

> These are non-blocking improvements recommended after QA. Prioritized from highest user impact to lowest.

---

### UX-01 · Empty States Are Missing or Generic

**Priority: HIGH**

**Current state:** When an organization has no proker, no tasks, no documents — most pages show blank white space or a generic "No data" message.

**Problem:** New users (first 10 minutes) will see empty pages and not know what to do next. This directly kills activation rate.

**Recommendation:**
- Every empty state must have: illustration + headline + sub-copy + primary CTA button.
- Example for empty /proker page: Illustration of a person planning → "Belum ada program kerja" → "Buat proker pertama kamu dan mulai rencanakan dengan lebih rapi." → `[+ Buat Proker Pertama]`
- Example for empty /tasks (no tasks assigned): "Tidak ada tugas aktif" → "Semua beres! Atau mungkin belum ada yang di-assign ke kamu?"
- Implement before M28 (Onboarding Wizard) — they are complementary.

**Files to update:**
- Every `Pages/*/Index.tsx` — add `EmptyState` component.
- Create `resources/js/Components/ui/EmptyState.tsx` — reusable with `illustration`, `title`, `description`, `action` props.

---

### UX-02 · No Global Loading/Skeleton States

**Priority: HIGH**

**Current state:** Page transitions with Inertia show a brief white flash before content appears. No skeleton loaders on any page.

**Problem:** Feels slow even when the actual data load is fast. Especially noticeable on dashboard and finance pages.

**Recommendation:**
- Add Inertia progress bar (already available via `@inertiajs/react` — just needs enabling in `app.tsx`).
- Add skeleton placeholders for:
  - Dashboard KPI cards (gray rectangles while loading)
  - Proker index cards
  - Finance table rows
  - Task kanban columns
- Create `resources/js/Components/ui/Skeleton.tsx` — simple shimmer component.

---

### UX-03 · Form Validation Feedback is Inconsistent

**Priority: HIGH**

**Current state:** Some forms show Inertia's `form.errors` inline, others only show a flash message at the top. Some forms clear on failed submission, losing user input.

**Problem:** Users re-type valid data they already entered because a different field failed validation.

**Recommendation:**
- Standardize: all forms must show inline field-level errors (red text below the input, not just a top flash).
- Never clear form state on validation failure — only clear on success.
- Add visual indicator (red border) on fields with errors.
- Create a `FormField.tsx` wrapper component: `<FormField label="Nama Proker" error={form.errors.name} required>`.
- Audit all forms: Proposal editor, RAB form, Member invite, Certificate issue, Sponsor/Vendor create/edit.

---

### UX-04 · No Confirmation Dialogs for Destructive Actions

**Priority: HIGH**

**Current state:** Actions like "Arsipkan Proker", "Hapus Anggaran", "Tolak Proposal" execute immediately on click.

**Problem:** One accidental click can cause irreversible data changes. This is especially dangerous for "Archive Proker" (which hides the proker from active view) and "Reject Proposal" (which resets approval flow).

**Recommendation:**
- All destructive actions must show a confirmation dialog before executing.
- Use shadcn/ui `AlertDialog` component (already available).
- Severity levels:
  - **Soft confirm** (e.g., Archive Proker): "Yakin ingin mengarsipkan proker ini? Kamu bisa membukanya kembali nanti." → `[Batalkan] [Arsipkan]`
  - **Hard confirm** (e.g., Delete member, Reject proposal): Type the name or "CONFIRM" to proceed.
- Apply to: Archive Proker, Remove Member, Reject Proposal, Reject LPJ, Delete Budget Line, Delete Document.

---

### UX-05 · Proposal & LPJ Editors Need Rich Text (M25 — Already Planned)

**Priority: HIGH**

**Current state:** Plain `<textarea>` for all proposal and LPJ section bodies.

**Problem:** Users write in Microsoft Word, copy-paste losing formatting. Prokerin becomes just a submission system, not a writing tool. This is already logged as M25 (Tiptap).

**Recommendation (additional to M25 plan):**
- Add auto-save to both editors: save section body every 30 seconds without user action.
- Show "Terakhir disimpan 2 menit lalu" timestamp.
- Add word/character count per section.
- Add a "Preview Mode" toggle — shows the section as it will appear in the PDF export.

---

### UX-06 · No Toast Notification System

**Priority: MEDIUM**

**Current state:** Success/error feedback uses Inertia flash messages that appear at the top of the page — easy to miss on long pages.

**Problem:** After submitting a form or triggering an export, users scroll down and miss the flash message. They don't know if the action succeeded.

**Recommendation:**
- Implement a toast notification system using `sonner` or `react-hot-toast`.
- Position: bottom-right of screen.
- Types: success (green), error (red), info (blue), loading (spinner for async jobs).
- Replace Inertia flash `FlashBanner` with toasts for all mutation feedback.
- Special case: when PDF/DOCX export is triggered — show a persistent "Sedang memproses..." loading toast that resolves to "File siap didownload!" when job completes (via polling or WebSocket M26).

---

### UX-07 · Approval Queue Needs Inline Preview

**Priority: MEDIUM**

**Current state:** Owner's approval queue shows a list of pending items. To review and approve, the owner must navigate to each item's detail page separately.

**Problem:** An owner with 5 pending items must navigate back-and-forth 5 times. Kills approval speed which is a core product promise.

**Recommendation:**
- Add expandable preview panel to each approval queue item:
  - Proposal: shows last 3 sections of content inline.
  - Finance: shows transaction amount, vendor name, receipt thumbnail.
  - LPJ: shows completion percentage and flagged checklist items.
- Approve/Reject/Request Revision buttons directly in the preview — no full page navigation required.
- This can be implemented as a `Sheet` (shadcn/ui slide-over panel from the right).

---

### UX-08 · Dashboard Quick Actions Need Context

**Priority: MEDIUM**

**Current state:** Dashboard shows quick action buttons ("Buat Proker Baru", "Undang Anggota") but only on the Pimpinan dashboard.

**Problem:** Project leads and members have no quick actions from their dashboard. They must navigate via sidebar.

**Recommendation:**
- Every dashboard variant needs 2–3 contextual quick action buttons.
  - Lead dashboard: "Lihat Tugasku", "Update Status Proker", "Buat Meeting"
  - Member dashboard: "Tugasku Hari Ini", "Scan QR Absensi"
  - Treasurer dashboard: "Tambah Realisasi", "Lihat Approval Queue"
  - Secretary dashboard: "Buat Meeting", "Upload Dokumen"

---

### UX-09 · Finance RAB Table UX Improvements

**Priority: MEDIUM**

**Current state:** RAB table is a static list. Totals and calculations are server-rendered.

**Problem:** When treasurer edits multiple line items, they have to save and reload to see updated totals. Also, no indication of which lines are over-budget.

**Recommendation:**
- Live total calculation: update subtotals and grand total in real-time as user types amounts (client-side JS, no server round-trip).
- Color code rows: green if realization ≤ budget, yellow if 80–100% used, red if over budget.
- Add "% terpakai" column showing realization as percentage of RAB line.
- Add a visual indicator (warning icon) when total realization exceeds total RAB.

---

### UX-10 · Certificate Template Editor Needs Visual Preview

**Priority: MEDIUM**

**Current state:** Certificate template is created/edited by writing raw HTML. No live preview.

**Problem:** Non-technical users (most BEM secretaries) cannot write HTML. This makes M16 unusable for the majority of the target audience.

**Recommendation (two options):**
- **Option A (MVP):** Add a "Preview" button that renders the template HTML in an iframe or Blade partial. At minimum, the user can see what the certificate will look like before issuing.
- **Option B (Post-MVP):** Replace HTML editor with a drag-and-drop certificate builder — variable blocks, font picker, logo upload placement, signature zone.
- Immediately actionable: add a "Template Variabel" reference card below the editor listing available placeholders: `{{recipient_name}}`, `{{certificate_number}}`, `{{issued_at}}`, `{{project_name}}`, `{{organization_name}}`.

---

### UX-11 · No "What's New" or Feature Discovery

**Priority: LOW**

**Current state:** New modules (M14–M28.5) are added silently. Existing users are not notified.

**Recommendation:**
- Add a simple changelog/what's new panel (accessible from a "🆕 Fitur Baru" badge in the header or sidebar).
- On first login after a deployment with new features: show a one-time modal "Ada fitur baru!" with 2-3 highlights.
- This is especially important for existing orgs who registered before M14–M20 existed.

---

### UX-12 · Search is Missing from Key Pages

**Priority: MEDIUM (becomes HIGH when M29 is built)**

**Current state:** No search on: /proker (index), /members, /documents, /certificates.

**Problem:** Once an org has 20+ projects, 50+ members, 100+ documents — finding anything requires scrolling through paginated lists.

**Recommendation:**
- Immediately: add a client-side filter input to every list page (filter by name, status, date). This requires no backend changes — filter the existing Inertia-passed array on the client.
- Long-term: M29 (Global Search with MeiliSearch) for cross-module search.
- Do NOT wait for M29 to add basic list filtering — ship client-side filter first.

---

### UX-13 · Breadcrumbs Are Missing

**Priority: LOW**

**Current state:** No breadcrumbs on any page. Users on nested pages (proker → task → edit) don't know where they are in the hierarchy.

**Recommendation:**
- Add breadcrumb to all pages with depth > 1.
- Examples:
  - `Dashboard > Proker > Ospek Maba 2025 > Tasks`
  - `Dashboard > Keuangan > Realisasi > Tambah`
  - `Dashboard > Sertifikat > Templates > Edit`
- Use `resources/js/Components/ui/Breadcrumb.tsx` (shadcn/ui — already available).

---

### UX-14 · Notification Bell Has No Dropdown

**Priority: MEDIUM (blocked by M26 — Real-Time Notifications)**

**Current state:** Notification bell in header links to /notifications full page.

**Problem:** Clicking the bell to see "did my proposal get approved?" forces a full page navigation. Users expect a dropdown preview.

**Recommendation (implement alongside M26):**
- Bell icon → dropdown preview of 5 most recent notifications.
- Each notification: icon (type) + short description + relative time ("2 menit lalu").
- Unread notifications highlighted with dot.
- "Mark all as read" button.
- "Lihat Semua" → /notifications full page.

---

### UX-15 · No Pagination or Virtualization on Long Lists

**Priority: MEDIUM**

**Current state:** Lists (proker, tasks, documents, members) likely return all records. No visible pagination.

**Problem:** An org with 100+ projects or 200+ members will receive a massive payload and slow rendering.

**Recommendation:**
- Add `paginate(20)` in all index Actions.
- Add Inertia pagination links using the built-in `<Link>` component with page param.
- Or: implement cursor-based infinite scroll for task boards.
- Test with 100+ seeded records per resource to verify pagination works before launch.

---

## 29. Technical Debt & Architecture Recommendations

> For the engineering team. Not blocking launch but should be addressed before scaling.

---

### TECH-01 · Spatie Permission Not Fully Enforced

**Current state:** M28.5 notes: "Current implementation uses local `organization_members.role` and `project_members.role` columns because that is the active project data model; Spatie can be layered later."

**Risk:** As more modules add role-based guards, the dual-system (local columns + Spatie) will create inconsistencies. One check uses `hasRole()`, another checks `organization_members.role` directly.

**Recommendation:**
- Before shipping M21 or later, audit all authorization checks: standardize on either Spatie OR local role column — not both.
- If keeping local columns: create a `UserOrganizationRole` value object or service that centralizes all role checks, so there is exactly ONE place to change when roles evolve.

---

### TECH-02 · No Error Tracking (Sentry or Equivalent)

**Current state:** No error tracking configured.

**Risk:** Production errors are invisible. Users will experience failures silently.

**Recommendation:**
- Install Sentry for Laravel + Sentry for React before any beta launch.
- `.env.example`: `SENTRY_LARAVEL_DSN=`, `VITE_SENTRY_DSN=`.
- Filter out non-critical exceptions (404, validation errors).
- Alert on: 5xx errors, queue job failures, payment webhook failures.

---

### TECH-03 · No Rate Limiting on Sensitive Routes

**Current state:** Not verified. Likely using Laravel's default throttle middleware.

**Risk:** Brute force on login, spam on invite endpoint, abuse of WhatsApp delivery endpoint.

**Recommendation:**
- Verify and document rate limits per route:
  - Login: 5 attempts per minute per IP.
  - Forgot password: 3 requests per 15 minutes per IP.
  - Invite member: 20 invites per hour per org.
  - WhatsApp send: 100 messages per hour per org.
  - /internal-admin login: 5 attempts per minute per IP.

---

### TECH-04 · PDF Export Quality with DomPDF

**Current state:** M16 uses DomPDF as the MVP engine. Complex certificate templates with CSS grid or flexbox will not render correctly.

**Risk:** When orgs design advanced certificate templates or complex proposal PDFs, the output quality will be poor.

**Recommendation:**
- Plan the migration to Browsershot before M25 (Rich Text Editor) ships — Tiptap JSON → HTML → Browsershot produces browser-quality PDF output.
- Keep DomPDF for simple certificates (M16) where the template is controlled.
- Use Browsershot for Proposal and LPJ exports where content is user-generated rich text.

---

### TECH-05 · No Soft Deletes on Core Models

**Current state:** Not verified across all models.

**Risk:** Hard deletes of Projects, Members, or Documents could cascade and lose data permanently. Recovery would require database-level restore.

**Recommendation:**
- Add `SoftDeletes` trait to: `Organization`, `Project`, `Task`, `Document`, `CertificateRecipient`, `SponsorVendor`.
- Add `deleted_at` index to all soft-deletable tables.
- Add a "Trash / Recycle Bin" page (owner only) for recovering recently deleted items within 30 days.

---

### TECH-06 · Redis Cache Invalidation Not Documented

**Current state:** M28.5 notes Redis caching with TTL 60s for sidebar badges. Other modules may cache data without clear invalidation strategy.

**Risk:** Stale cache causes incorrect badge counts, wrong KPI numbers — especially after approval actions.

**Recommendation:**
- Document all Redis cache keys and their invalidation triggers in a `CACHE.md` file.
- Ensure all cache keys are org-scoped: `org:{org_id}:sidebar:badge:{user_id}`.
- Add cache invalidation calls in all relevant Actions (approval, task status update, etc.).

---

### TECH-07 · Queue Job Failure Handling

**Current state:** M17 has retry logic (3 attempts, exponential backoff). Not verified for other jobs.

**Risk:** If PDF export, certificate generation, or email jobs fail permanently, users have no way to know or retry.

**Recommendation:**
- Add a "Failed Jobs" monitor (Laravel Horizon or basic /internal-admin/failed-jobs Filament resource).
- All jobs must implement `failed()` method — send an in-app notification to the user who triggered the job.
- Add a "Retry" button in relevant UI (export page, certificate page) for jobs in `failed` state.

---

### TECH-08 · Missing Database Indexes

**Current state:** Foreign keys are indexed but frequently queried columns may not be.

**Risk:** As data grows (100+ orgs, 1000+ projects), slow queries on unindexed columns will degrade performance.

**Recommendation — add indexes:**
```sql
-- Projects
projects: (organization_id, status)
projects: (organization_id, event_start_date)

-- Tasks
tasks: (project_id, status)
tasks: (assignee_id, status)

-- Approval
approval_instances: (subject_type, subject_id, status)
approval_step_records: (instance_id, step_order)

-- Notifications
notifications: (notifiable_id, read_at)

-- Attendance
attendance_records: (session_id, user_id)
attendance_qr_tokens: (token_hash, expires_at)
```

---

## 30. Bug Report Log

Use this section to record all findings during QA execution.

### Bug Report Template

```
---
ID       : BUG-[number]
Date     : YYYY-MM-DD
Tester   : [Name]
Severity : CRITICAL / HIGH / MEDIUM / LOW / ENHANCEMENT
Module   : [M01, M07, SA01, etc.]
Title    : [Short description]

Steps to Reproduce:
1.
2.
3.

Expected Result:
[What should happen]

Actual Result:
[What actually happened]

Environment:
- Browser: [Chrome 124 / Safari 17]
- Viewport: [375px / 1280px]
- User Role: [owner / member / etc.]
- URL: [exact URL]

Screenshot/Video: [attach or link]

Notes: [Any additional context]
---
```

### Active Bug Log

| ID | Severity | Module | Title | Status | Assignee |
|----|----------|--------|-------|--------|----------|
| — | — | — | No bugs logged yet | — | — |

---

## 31. Pre-Launch Final Checklist

Run this as the final gate before any public beta or paid plan activation.

```
# Automated
[ ] php artisan test → 100% green
[ ] npm run build → no errors
[ ] npm run lint → no errors
[ ] ./vendor/bin/pint --test → no formatting violations

# Security
[ ] All /internal-admin routes inaccessible to non-super_admin
[ ] All organization data routes return 403 for unauthenticated requests
[ ] Cross-tenant isolation verified: org2 cannot see org1 data
[ ] File upload MIME validation active on all upload endpoints
[ ] S3 signed URLs working — raw S3 paths return 403
[ ] CSRF protection active on all POST/PUT/PATCH/DELETE routes

# Data Integrity
[ ] migrate:fresh --seed runs cleanly with no errors
[ ] No orphan records after full proker lifecycle test
[ ] Soft deletes in place for all core models

# Functionality
[ ] All 6 dashboard variants render correctly (owner, admin, secretary, treasurer, lead, member)
[ ] Full proker lifecycle flow completes end-to-end (Flow 1 above)
[ ] Multi-level approval chain executes correctly (Flow 2 above)
[ ] Board handover flow completes (Flow 3 above)
[ ] Certificate issuance + public verification works
[ ] PDF and DOCX export jobs complete successfully
[ ] WhatsApp delivery log shows correct status
[ ] Super admin panel accessible, impersonation works, audit log written

# UX
[ ] No empty states show blank white space — all have illustration + CTA
[ ] No console errors on any page across all 6 role variants
[ ] All pages tested at 375px and 1280px — no horizontal overflow
[ ] Landing page (/): no console errors, all CTAs link correctly
[ ] Lighthouse Performance > 85 on landing page

# Monitoring
[ ] Sentry DSN configured for both Laravel and React
[ ] Queue worker running (Supervisor configured for staging/production)
[ ] Failed job handler configured — failed() method on all jobs
[ ] Redis connection confirmed stable
```

---

*Last updated: 2026-05-17. This document must be updated whenever a new module is added to features.md. Any new module = new section in Sections 4–24 and new items in Section 31.*
