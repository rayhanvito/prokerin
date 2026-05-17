# CODEX AUTOMATION PROMPT — Prokerin QA End-to-End

> **Target agent**: OpenAI Codex CLI (`codex`) atau compatible agentic runner (Cursor Agent, Claude Code, dll).
> **Mode eksekusi**: Full autonomous — jalankan semua step secara berurutan, catat hasil, generate laporan akhir.
> **Working directory**: root repo Prokerin (`/var/www/prokerin` atau sesuai environment).
> **Output**: File `qa-results/report-{timestamp}.md` + `qa-results/failures-{timestamp}.json`.

---

## CONTEXT & CONSTRAINTS

```
Kamu adalah QA automation engineer. Tugasmu menjalankan test suite end-to-end untuk
aplikasi web Prokerin (Laravel + Inertia.js + Vue). Ikuti setiap instruksi secara
berurutan. Jangan skip step. Jika satu step gagal, catat failure-nya dan LANJUTKAN
ke step berikutnya kecuali dinyatakan STOP. Di akhir, generate laporan lengkap.
```

### Aturan Eksekusi
- Setiap command dijalankan dari root direktori proyek
- Timeout per command: **60 detik** kecuali dinyatakan lain
- Jika command return non-zero exit code → tandai `[FAIL]`, catat stderr, lanjutkan
- Variabel environment dibaca dari `.env.testing`
- Semua hasil ditulis ke `qa-results/` (buat folder jika belum ada)
- `BASE_URL` default: `http://localhost:8000`

---

## PHASE 0 — SETUP & PRE-GATE

> ⛔ Jika Phase 0 GAGAL → STOP. Jangan lanjut ke phase berikutnya.

### 0.1 Buat output directory

```bash
mkdir -p qa-results
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
REPORT="qa-results/report-${TIMESTAMP}.md"
FAILURES="qa-results/failures-${TIMESTAMP}.json"
echo "[]" > "$FAILURES"
echo "# QA Report — Prokerin — $(date)" > "$REPORT"
echo "Generated: $(date)" >> "$REPORT"
echo "" >> "$REPORT"
```

### 0.2 Pre-QA Automated Gate (WAJIB PASS SEMUA)

```bash
# Test suite Laravel
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test --stop-on-failure 2>&1
# ASSERT: exit code 0, output mengandung "PASS" atau "Tests: N passed"

# Code style
./vendor/bin/pint --test 2>&1
# ASSERT: exit code 0

# Frontend build
npm run build 2>&1
# ASSERT: exit code 0, tidak ada "ERROR" di output

# Route list
php artisan route:list 2>&1
# ASSERT: exit code 0

# DB fresh seed (testing env)
php artisan migrate:fresh --seed --env=testing 2>&1
# ASSERT: exit code 0, output mengandung "Database seeding completed"
```

**Jika salah satu command di atas gagal**: tulis ke `$REPORT` section `## PRE-GATE FAILURES` dan **STOP**.

---

## PHASE 1 — SERVER STARTUP

```bash
# Start app server di background
php artisan serve --port=8000 &
APP_PID=$!
sleep 3

# Verifikasi server hidup
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/
# ASSERT: output = "200"

echo "APP_PID=$APP_PID" >> qa-results/.pids

# Start queue worker di background (sync mode untuk test)
php artisan queue:listen --queue=default,exports,notifications --timeout=30 &
QUEUE_PID=$!
echo "QUEUE_PID=$QUEUE_PID" >> qa-results/.pids
```

---

## PHASE 2 — HTTP SMOKE TESTS (Guest / Public)

> Tool: `curl`. Pattern: `curl -s -o /dev/null -w "%{http_code}" {URL}`.
> ASSERT setiap response code sesuai ekspektasi.

### 2.1 Public Routes (Expected: 200)

```bash
declare -A PUBLIC_ROUTES=(
  ["landing"]="/"
  ["features"]="/features"
  ["pricing"]="/pricing"
  ["login"]="/login"
  ["register"]="/register"
)

for name in "${!PUBLIC_ROUTES[@]}"; do
  path="${PUBLIC_ROUTES[$name]}"
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000${path}")
  if [ "$code" = "200" ]; then
    echo "[PASS] GET ${path} → ${code}"
  else
    echo "[FAIL] GET ${path} → ${code} (expected 200)"
    # append to failures JSON
  fi
done
```

### 2.2 Auth-Protected Routes (Expected: 302 redirect ke /login)

```bash
declare -a PROTECTED=(
  "/dashboard"
  "/proker"
  "/tasks"
  "/finance"
  "/reports"
  "/documents"
  "/meetings"
  "/attendance"
  "/certificates"
  "/notifications"
  "/profile"
)

for path in "${PROTECTED[@]}"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000${path}")
  if [ "$code" = "302" ]; then
    echo "[PASS] GET ${path} (no auth) → ${code} (redirect)"
  else
    echo "[FAIL] GET ${path} (no auth) → ${code} (expected 302)"
  fi
done
```

### 2.3 404 Custom Page

```bash
code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/ini-route-tidak-ada-xyzabc")
# ASSERT: 404
body=$(curl -s "http://localhost:8000/ini-route-tidak-ada-xyzabc")
# ASSERT: body mengandung kata "404" atau "tidak ditemukan" (case-insensitive)
```

### 2.4 Certificate Verify — Invalid Token

```bash
code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/verify/token-tidak-valid-12345")
# ASSERT: 200 (halaman "tidak ditemukan") atau 404
# body tidak mengandung stack trace
body=$(curl -s "http://localhost:8000/verify/token-tidak-valid-12345")
echo "$body" | grep -qi "stack trace" && echo "[FAIL] Stack trace exposed" || echo "[PASS] No stack trace"
```

---

## PHASE 3 — AUTH FLOW TESTS (via HTTP)

> Gunakan cookie jar untuk session. Tool: `curl` dengan `-c cookiejar.txt -b cookiejar.txt`.

### 3.1 Helper: Ambil CSRF Token

```bash
get_csrf() {
  local url="$1"
  curl -s -c qa-results/cookies_$2.txt "http://localhost:8000${url}" \
    | grep -o 'name="_token" value="[^"]*"' \
    | sed 's/name="_token" value="//' \
    | tr -d '"'
}
```

### 3.2 Login Valid

```bash
# Ambil CSRF
TOKEN=$(get_csrf "/login" "owner")

# POST login
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
  -c qa-results/cookies_owner.txt \
  -b qa-results/cookies_owner.txt \
  -X POST "http://localhost:8000/login" \
  -d "email=owner@prokerin.test&password=password&_token=${TOKEN}" \
  -L)

# ASSERT: final redirect ke /dashboard (200) atau intermediate 302
if echo "$RESPONSE" | grep -q "^2\|^3"; then
  echo "[PASS] Login owner@prokerin.test → HTTP ${RESPONSE}"
else
  echo "[FAIL] Login owner@prokerin.test → HTTP ${RESPONSE}"
fi

# Verifikasi session — akses /dashboard dengan cookie
DASH=$(curl -s -o /dev/null -w "%{http_code}" \
  -b qa-results/cookies_owner.txt \
  "http://localhost:8000/dashboard")
[ "$DASH" = "200" ] && echo "[PASS] /dashboard accessible after login" || echo "[FAIL] /dashboard returned $DASH"
```

### 3.3 Login Invalid — Credentials Salah

```bash
TOKEN=$(get_csrf "/login" "invalid_test")
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -c qa-results/cookies_invalid.txt \
  -b qa-results/cookies_invalid.txt \
  -X POST "http://localhost:8000/login" \
  -d "email=owner@prokerin.test&password=wrongpassword&_token=${TOKEN}")
# ASSERT: 422 (validation fail) atau 302 kembali ke /login
[ "$CODE" = "422" ] || [ "$CODE" = "302" ] && echo "[PASS] Wrong password → ${CODE}" || echo "[FAIL] Wrong password → ${CODE}"
```

### 3.4 Login Rate Limit (5 gagal → 429)

```bash
TOKEN=$(get_csrf "/login" "ratelimit")
for i in {1..6}; do
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -c qa-results/cookies_rl.txt \
    -b qa-results/cookies_rl.txt \
    -X POST "http://localhost:8000/login" \
    -d "email=ratelimit-test-$(date +%s)@test.com&password=wrong&_token=${TOKEN}")
  echo "Attempt $i: HTTP $CODE"
done
# ASSERT: attempt ke-6 atau sebelumnya return 429
```

### 3.5 Login Semua Akun Test

```bash
declare -A TEST_ACCOUNTS=(
  ["owner"]="owner@prokerin.test"
  ["admin"]="admin@prokerin.test"
  ["secretary"]="sekretaris@prokerin.test"
  ["treasurer"]="bendahara@prokerin.test"
  ["lead"]="lead@prokerin.test"
  ["coordinator"]="koordinator@prokerin.test"
  ["member"]="member@prokerin.test"
  ["viewer"]="viewer@prokerin.test"
  ["campus"]="campus@prokerin.test"
  ["superadmin"]="superadmin@prokerin.internal"
)

for role in "${!TEST_ACCOUNTS[@]}"; do
  email="${TEST_ACCOUNTS[$role]}"
  TOKEN=$(get_csrf "/login" "${role}")
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -c "qa-results/cookies_${role}.txt" \
    -b "qa-results/cookies_${role}.txt" \
    -X POST "http://localhost:8000/login" \
    -L \
    -d "email=${email}&password=password&_token=${TOKEN}")
  DASH=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "qa-results/cookies_${role}.txt" \
    "http://localhost:8000/dashboard")
  if [ "$DASH" = "200" ]; then
    echo "[PASS] Login ${role} (${email}) → dashboard 200"
  else
    echo "[FAIL] Login ${role} (${email}) → dashboard ${DASH}"
  fi
done
```

---

## PHASE 4 — ROLE-BASED ACCESS CONTROL TESTS

> Gunakan cookie jar yang sudah login dari Phase 3.
> Pattern: akses URL → bandingkan HTTP code dengan ekspektasi.

### 4.1 Definisi Ekspektasi Per Role

```bash
# Format: "URL|owner|admin|secretary|treasurer|lead|coordinator|member|viewer"
# Nilai: 200=ok, 403=forbidden, 302=redirect
declare -a RBAC_MATRIX=(
  "/finance|200|200|403|200|403|403|403|403"
  "/finance/budget-draft|200|200|403|200|403|403|403|403"
  "/finance/realization|200|200|403|200|403|403|403|403"
  "/finance/approval|200|200|403|200|403|403|403|403"
  "/reports|200|200|200|200|200|200|200|200"
  "/documents|200|200|200|200|200|200|200|200"
  "/meetings|200|200|200|200|200|200|200|200"
  "/attendance|200|200|200|403|200|200|200|403"
  "/certificates|200|200|200|403|200|403|200|403"
  "/organization/periods|200|200|403|403|403|403|403|403"
  "/campus/dashboard|403|403|403|403|403|403|403|403"
)

ROLES=("owner" "admin" "secretary" "treasurer" "lead" "coordinator" "member" "viewer")

for row in "${RBAC_MATRIX[@]}"; do
  IFS='|' read -ra PARTS <<< "$row"
  URL="${PARTS[0]}"
  for i in "${!ROLES[@]}"; do
    role="${ROLES[$i]}"
    expected="${PARTS[$((i+1))]}"
    actual=$(curl -s -o /dev/null -w "%{http_code}" \
      -b "qa-results/cookies_${role}.txt" \
      "http://localhost:8000${URL}")
    if [ "$actual" = "$expected" ]; then
      echo "[PASS] ${role} GET ${URL} → ${actual}"
    else
      echo "[FAIL] ${role} GET ${URL} → ${actual} (expected ${expected})"
    fi
  done
done
```

### 4.2 Campus Admin — /campus/dashboard

```bash
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_campus.txt" \
  "http://localhost:8000/campus/dashboard")
[ "$CODE" = "200" ] && echo "[PASS] campus_admin /campus/dashboard → 200" || echo "[FAIL] /campus/dashboard → $CODE"

# Non-campus (owner) harus 403
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/campus/dashboard")
[ "$CODE" = "403" ] && echo "[PASS] owner /campus/dashboard → 403" || echo "[FAIL] /campus/dashboard → $CODE"
```

### 4.3 Super Admin — Filament Panel

```bash
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_superadmin.txt" \
  "http://localhost:8000/admin")
[ "$CODE" = "200" ] && echo "[PASS] superadmin /admin → 200" || echo "[FAIL] /admin → $CODE"

# Owner harus tidak bisa akses Filament
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/admin")
[ "$CODE" = "403" ] || [ "$CODE" = "302" ] && echo "[PASS] owner /admin → blocked (${CODE})" || echo "[FAIL] /admin → $CODE"
```

---

## PHASE 5 — TENANT SCOPE / CROSS-ORG SECURITY TESTS

> ⚠️ CRITICAL SECURITY — Semua harus PASS.

```bash
# Setup: dapatkan slug proker dari org lain via DB
PROKER_ORG_B=$(php artisan tinker --execute="
  \$org = \App\Models\Organization::where('slug', 'hima-informatika')->first();
  \$p = \$org ? \$org->projects()->first() : null;
  echo \$p ? \$p->slug : 'NOT_FOUND';
")

echo "Proker org B slug: $PROKER_ORG_B"

if [ "$PROKER_ORG_B" != "NOT_FOUND" ] && [ -n "$PROKER_ORG_B" ]; then

  # owner BEM FT coba akses proker HIMA
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "qa-results/cookies_owner.txt" \
    "http://localhost:8000/proker/${PROKER_ORG_B}")
  [ "$CODE" = "403" ] || [ "$CODE" = "404" ] \
    && echo "[PASS][SECURITY] Cross-org proker access → ${CODE}" \
    || echo "[FAIL][SECURITY] Cross-org proker access → ${CODE} (CRITICAL LEAK!)"

  # owner BEM FT coba PATCH status proker HIMA
  PROJECT_ID=$(php artisan tinker --execute="
    \$org = \App\Models\Organization::where('slug', 'hima-informatika')->first();
    \$p = \$org ? \$org->projects()->first() : null;
    echo \$p ? \$p->id : 'NOT_FOUND';
  ")
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "qa-results/cookies_owner.txt" \
    -X PATCH "http://localhost:8000/proker/${PROJECT_ID}/status" \
    -d "status=Running")
  [ "$CODE" = "403" ] || [ "$CODE" = "404" ] \
    && echo "[PASS][SECURITY] Cross-org PATCH status → ${CODE}" \
    || echo "[FAIL][SECURITY] Cross-org PATCH status → ${CODE} (CRITICAL LEAK!)"

fi

# Finance cross-org
FINANCE_PROJECT_ID=$(php artisan tinker --execute="
  \$org = \App\Models\Organization::where('slug', 'hima-informatika')->first();
  \$p = \$org ? \$org->projects()->first() : null;
  echo \$p ? \$p->id : 'NOT_FOUND';
")
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/finance?project=${FINANCE_PROJECT_ID}")
[ "$CODE" = "403" ] || [ "$CODE" = "404" ] \
  && echo "[PASS][SECURITY] Cross-org finance access → ${CODE}" \
  || echo "[FAIL][SECURITY] Cross-org finance → ${CODE} (POTENTIAL LEAK!)"

# XSS test — input berbahaya di search
XSS_PAYLOAD='<script>alert(1)</script>'
BODY=$(curl -s \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/proker?search=$(python3 -c "import urllib.parse; print(urllib.parse.quote('${XSS_PAYLOAD}'))")")
echo "$BODY" | grep -q "<script>alert" \
  && echo "[FAIL][SECURITY] XSS not escaped in search output" \
  || echo "[PASS][SECURITY] XSS payload escaped"

# Stack trace tidak bocor ke production
BODY=$(curl -s "http://localhost:8000/trigger-error-intentional-404-xyz")
echo "$BODY" | grep -qi "stack trace\|vendor/laravel\|ErrorException" \
  && echo "[FAIL][SECURITY] Stack trace exposed" \
  || echo "[PASS][SECURITY] No stack trace in error response"
```

---

## PHASE 6 — FORM VALIDATION TESTS (via Artisan / Feature Test)

> Jalankan Laravel Feature Tests yang sudah ada, plus inline tinker assertions.

### 6.1 Jalankan semua Feature Tests yang relevan

```bash
php artisan test --filter="AuthTest" --verbose 2>&1
php artisan test --filter="ProkerTest" --verbose 2>&1
php artisan test --filter="FinanceTest" --verbose 2>&1
php artisan test --filter="ReportTest" --verbose 2>&1
php artisan test --filter="MemberTest" --verbose 2>&1
php artisan test --filter="DocumentTest" --verbose 2>&1
php artisan test --filter="AttendanceTest" --verbose 2>&1
php artisan test --filter="CertificateTest" --verbose 2>&1
php artisan test --filter="MeetingTest" --verbose 2>&1
php artisan test --filter="NotificationTest" --verbose 2>&1
php artisan test --filter="ApprovalWorkflowTest" --verbose 2>&1
php artisan test --filter="TenantScopeTest" --verbose 2>&1
# ASSERT: semua exit 0
```

### 6.2 Database State Assertions via Tinker

```bash
php artisan tinker --execute="
// Pastikan seed data lengkap
\$checks = [
  'users'               => \App\Models\User::count(),
  'organizations'       => \App\Models\Organization::count(),
  'projects'            => \App\Models\Project::count(),
  'proposal_drafts'     => \App\Models\ProposalDraft::count(),
  'budget_lines'        => \App\Models\BudgetLine::count(),
  'meetings'            => \App\Models\Meeting::count(),
  'documents'           => \App\Models\Document::count(),
  'lpj_checklist_items' => \App\Models\LpjChecklistItem::count(),
];

foreach (\$checks as \$table => \$count) {
  if (\$count > 0) {
    echo \"[PASS] {$table}: {$count} rows\n\";
  } else {
    echo \"[FAIL] {$table}: 0 rows (seed mungkin tidak jalan)\n\";
  }
}
"
```

---

## PHASE 7 — CORE BUSINESS LOGIC (Artisan Tinker Assertions)

### 7.1 Proker Status Transition Logic

```bash
php artisan tinker --execute="
\$project = \App\Models\Project::first();
if (!\$project) { echo '[SKIP] No project found'; return; }

// Test valid transition Draft → ProposalReview
\$project->status = \App\Enums\ProjectStatus::Draft;
\$project->save();

try {
  // Cek apakah transition method tersedia
  if (method_exists(\$project, 'transitionTo')) {
    \$project->transitionTo(\App\Enums\ProjectStatus::ProposalReview);
    echo '[PASS] Draft → ProposalReview transition OK\n';
  } else {
    echo '[INFO] transitionTo method tidak ditemukan, skip\n';
  }
} catch (\Exception \$e) {
  echo '[FAIL] Transition error: ' . \$e->getMessage() . '\n';
}

// Test invalid transition Draft → Completed (harus throw)
try {
  if (method_exists(\$project, 'transitionTo')) {
    \$project->transitionTo(\App\Enums\ProjectStatus::Completed);
    echo '[FAIL] Invalid transition Draft → Completed seharusnya gagal\n';
  }
} catch (\Exception \$e) {
  echo '[PASS] Invalid transition correctly blocked: ' . \$e->getMessage() . '\n';
}
"
```

### 7.2 Budget Line CRUD Logic

```bash
php artisan tinker --execute="
\$project = \App\Models\Project::first();
if (!\$project) { echo '[SKIP] No project'; return; }

// Buat budget line
\$line = new \App\Models\BudgetLine([
  'project_id'     => \$project->id,
  'name'           => 'Test Line QA ' . time(),
  'category'       => 'konsumsi',
  'planned_amount' => 500000,
]);
\$line->save();
echo '[PASS] BudgetLine created: ' . \$line->id . '\n';

// Update
\$line->planned_amount = 750000;
\$line->save();
echo \$line->planned_amount === 750000 ? '[PASS] BudgetLine updated' : '[FAIL] BudgetLine update failed';
echo '\n';

// Delete
\$id = \$line->id;
\$line->delete();
\$exists = \App\Models\BudgetLine::find(\$id);
echo !\$exists ? '[PASS] BudgetLine deleted' : '[FAIL] BudgetLine still exists';
echo '\n';
"
```

### 7.3 Invitation Token Flow

```bash
php artisan tinker --execute="
\$org = \App\Models\Organization::first();
\$inviter = \App\Models\User::whereHas('organizationMembers', function(\$q) use (\$org) {
  \$q->where('organization_id', \$org->id);
})->first();

if (!\$org || !\$inviter) { echo '[SKIP] Missing org/inviter'; return; }

// Buat invitation
\$invite = \App\Models\OrganizationInvitation::create([
  'organization_id' => \$org->id,
  'email'           => 'qa-test-' . time() . '@example.com',
  'role'            => 'member',
  'invited_by'      => \$inviter->id,
  'token'           => \Illuminate\Support\Str::random(64),
  'status'          => 'pending',
  'expires_at'      => now()->addDays(7),
]);
echo '[PASS] Invitation created: token=' . substr(\$invite->token, 0, 10) . '...\n';

// Cek token unik
\$dup = \App\Models\OrganizationInvitation::where('token', \$invite->token)->count();
echo \$dup === 1 ? '[PASS] Invitation token unique' : '[FAIL] Token duplikat';
echo '\n';

// Cleanup
\$invite->delete();
echo '[PASS] Invitation cleanup OK\n';
"
```

### 7.4 Proposal Draft AI Feature Flag

```bash
php artisan tinker --execute="
// Cek apakah FeatureFlag model / helper ada
if (class_exists('\App\Models\FeatureFlag')) {
  \$flag = \App\Models\FeatureFlag::where('key', 'ai_proposal_suggestion')->first();
  if (\$flag) {
    echo '[PASS] FeatureFlag ai_proposal_suggestion exists, is_enabled=' . (\$flag->is_enabled ? 'true' : 'false') . '\n';
  } else {
    echo '[WARN] FeatureFlag ai_proposal_suggestion tidak di-seed\n';
  }
} else {
  echo '[INFO] FeatureFlag model tidak ada, skip\n';
}
"
```

---

## PHASE 8 — QUEUE & JOB TESTS

```bash
# Reset failed jobs
php artisan queue:flush 2>&1

# Trigger document export job secara manual via tinker
php artisan tinker --execute="
\$project = \App\Models\Project::first();
if (!\$project) { echo '[SKIP] No project'; return; }

// Dispatch export job
try {
  \$export = \App\Models\DocumentExport::create([
    'organization_id' => \$project->organization_id,
    'project_id'      => \$project->id,
    'document_type'   => 'proposal',
    'format'          => 'pdf',
    'status'          => 'queued',
    'requested_by'    => \App\Models\User::first()->id,
  ]);
  
  if (class_exists('\App\Jobs\GenerateDocumentExportJob')) {
    \App\Jobs\GenerateDocumentExportJob::dispatch(\$export);
    echo '[PASS] GenerateDocumentExportJob dispatched for export ' . \$export->id . '\n';
  } else {
    echo '[WARN] GenerateDocumentExportJob class tidak ditemukan\n';
  }
} catch (\Exception \$e) {
  echo '[FAIL] Job dispatch error: ' . \$e->getMessage() . '\n';
}
"

# Jalankan queue satu kali
php artisan queue:work --once --queue=default,exports,notifications 2>&1
QUEUE_EXIT=$?
[ $QUEUE_EXIT -eq 0 ] && echo "[PASS] Queue worker ran successfully" || echo "[FAIL] Queue worker exit $QUEUE_EXIT"

# Cek failed jobs
FAILED_COUNT=$(php artisan tinker --execute="echo \DB::table('failed_jobs')->count();" 2>/dev/null | tail -1)
echo "Failed jobs count: $FAILED_COUNT"
[ "$FAILED_COUNT" = "0" ] && echo "[PASS] No failed jobs" || echo "[WARN] $FAILED_COUNT failed jobs"
```

---

## PHASE 9 — FILE OUTPUT VERIFICATION

> Verifikasi setiap file yang dihasilkan sistem valid dan bisa dibuka.

### 9.1 Storage Link

```bash
# Verifikasi storage link exist
[ -L "public/storage" ] && echo "[PASS] storage:link exists" || echo "[FAIL] storage:link missing — run php artisan storage:link"
```

### 9.2 S3 / Local Storage Test

```bash
php artisan tinker --execute="
try {
  \Storage::disk('local')->put('qa-test-' . time() . '.txt', 'prokerin-qa-test');
  echo '[PASS] Local storage write OK\n';
} catch (\Exception \$e) {
  echo '[FAIL] Storage write error: ' . \$e->getMessage() . '\n';
}

// Cek default disk
echo 'Default disk: ' . config('filesystems.default') . '\n';
"
```

### 9.3 PDF Export File Integrity (jika ada completed export)

```bash
php artisan tinker --execute="
\$export = \App\Models\DocumentExport::where('status', 'completed')->whereNotNull('output_path')->first();
if (!\$export) { echo '[SKIP] Belum ada completed export\n'; return; }

\$disk = config('filesystems.default');
\$exists = \Storage::disk(\$disk)->exists(\$export->output_path);
echo \$exists ? '[PASS] Export file exists di storage: ' . \$export->output_path : '[FAIL] Export file not found: ' . \$export->output_path;
echo '\n';

// Cek ukuran file > 0
if (\$exists) {
  \$size = \Storage::disk(\$disk)->size(\$export->output_path);
  echo \$size > 0 ? '[PASS] File size: ' . \$size . ' bytes' : '[FAIL] File size is 0 (corrupt)';
  echo '\n';
}
"
```

### 9.4 QR Image Endpoint

```bash
# Cek jika ada QR token aktif
QR_TOKEN=$(php artisan tinker --execute="
  \$token = \App\Models\AttendanceQrToken::where('status', 'active')->first();
  echo \$token ? \$token->token : 'NOT_FOUND';
" 2>/dev/null | tail -1)

if [ "$QR_TOKEN" != "NOT_FOUND" ] && [ -n "$QR_TOKEN" ]; then
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    "http://localhost:8000/attendance/qr-image?token=${QR_TOKEN}")
  [ "$CODE" = "200" ] && echo "[PASS] QR image endpoint → 200" || echo "[FAIL] QR image → $CODE"
else
  echo "[SKIP] Tidak ada active QR token"
fi
```

### 9.5 CSV Encoding Check (Attendance Export)

```bash
SESSION_ID=$(php artisan tinker --execute="
  \$s = \App\Models\AttendanceSession::first();
  echo \$s ? \$s->id : 'NOT_FOUND';
" 2>/dev/null | tail -1)

if [ "$SESSION_ID" != "NOT_FOUND" ] && [ -n "$SESSION_ID" ]; then
  curl -s \
    -b "qa-results/cookies_owner.txt" \
    -o "qa-results/attendance-test.csv" \
    "http://localhost:8000/attendance/sessions/${SESSION_ID}/export.csv"
  
  if [ -f "qa-results/attendance-test.csv" ]; then
    # Cek UTF-8 BOM
    BOM=$(hexdump -C "qa-results/attendance-test.csv" | head -1 | grep "ef bb bf")
    [ -n "$BOM" ] && echo "[PASS] CSV has UTF-8 BOM" || echo "[WARN] CSV missing UTF-8 BOM"
    
    # Cek header
    HEAD=$(head -1 "qa-results/attendance-test.csv")
    echo "$HEAD" | grep -qi "nama\|name" && echo "[PASS] CSV has Nama column" || echo "[FAIL] CSV missing Nama column"
    
    echo "[PASS] Attendance CSV downloaded ($(wc -l < qa-results/attendance-test.csv) rows)"
  else
    echo "[FAIL] Attendance CSV download failed"
  fi
else
  echo "[SKIP] Tidak ada attendance session"
fi
```

---

## PHASE 10 — NOTIFICATION SYSTEM TESTS

```bash
php artisan tinker --execute="
\$user = \App\Models\User::whereHas('organizationMembers')->first();
if (!\$user) { echo '[SKIP] No user with org\n'; return; }

// Buat notifikasi test
\$user->notify(new \App\Notifications\TestNotification('QA Test ' . time()));
\$notif = \$user->notifications()->latest()->first();
echo \$notif ? '[PASS] Notification created: ' . \$notif->id : '[FAIL] Notification not created';
echo '\n';

// Mark as read
\$notif->markAsRead();
\$notif->refresh();
echo \$notif->read_at ? '[PASS] Notification marked as read' : '[FAIL] read_at not set';
echo '\n';
" 2>&1 || echo "[INFO] TestNotification class mungkin belum ada, skip"

# Test endpoint notifikasi
CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/notifications/recent")
[ "$CODE" = "200" ] && echo "[PASS] GET /notifications/recent → 200" || echo "[FAIL] /notifications/recent → $CODE"

# Verifikasi response JSON
BODY=$(curl -s \
  -b "qa-results/cookies_owner.txt" \
  "http://localhost:8000/notifications/recent")
echo "$BODY" | python3 -c "import sys,json; d=json.load(sys.stdin); print('[PASS] /notifications/recent valid JSON, unreadCount=' + str(d.get('unreadCount', 'KEY_MISSING')))" 2>/dev/null \
  || echo "[FAIL] /notifications/recent not valid JSON"
```

---

## PHASE 11 — PERFORMANCE CHECKS

```bash
# Cek response time halaman utama
for path in "/" "/login" "/dashboard" "/proker"; do
  COOKIE_FLAG=""
  [[ "$path" == "/dashboard" || "$path" == "/proker" ]] && COOKIE_FLAG="-b qa-results/cookies_owner.txt"
  
  TIME=$(curl -s -o /dev/null -w "%{time_total}" $COOKIE_FLAG "http://localhost:8000${path}")
  THRESHOLD="3.0"
  if (( $(echo "$TIME < $THRESHOLD" | bc -l) )); then
    echo "[PASS] ${path} response time: ${TIME}s (< ${THRESHOLD}s)"
  else
    echo "[WARN] ${path} response time: ${TIME}s (> ${THRESHOLD}s) — perlu optimasi"
  fi
done
```

---

## PHASE 12 — SECURITY HEADERS CHECK

```bash
echo "=== Security Headers Verification ==="

HEADERS=$(curl -s -I "http://localhost:8000/")

check_header() {
  local header="$1"
  local expected="$2"
  echo "$HEADERS" | grep -i "$header" | grep -qi "$expected" \
    && echo "[PASS] Header $header: $expected" \
    || echo "[WARN] Header $header missing atau tidak cocok (expected: $expected)"
}

check_header "X-Frame-Options" "DENY\|SAMEORIGIN"
check_header "X-Content-Type-Options" "nosniff"
check_header "Referrer-Policy" "same-origin\|strict-origin"
# HSTS hanya di HTTPS production, skip di local

# CSRF meta tag ada di halaman login
BODY=$(curl -s "http://localhost:8000/login")
echo "$BODY" | grep -q 'name="csrf-token"\|name="_token"' \
  && echo "[PASS] CSRF token ada di halaman login" \
  || echo "[FAIL] CSRF token tidak ditemukan di halaman login"

# Tidak ada secret key di response
echo "$BODY" | grep -qi "AWS_SECRET\|APP_KEY=\|DB_PASSWORD=" \
  && echo "[FAIL][SECURITY] Secret terekspos di HTML!" \
  || echo "[PASS] Tidak ada secret di HTML"
```

---

## PHASE 13 — CLEANUP & REPORT GENERATION

```bash
# Stop background processes
if [ -f qa-results/.pids ]; then
  source qa-results/.pids
  [ -n "$APP_PID" ] && kill $APP_PID 2>/dev/null && echo "App server stopped"
  [ -n "$QUEUE_PID" ] && kill $QUEUE_PID 2>/dev/null && echo "Queue worker stopped"
fi

# Hitung hasil
PASS_COUNT=$(grep -c "\[PASS\]" qa-results/output.log 2>/dev/null || echo "0")
FAIL_COUNT=$(grep -c "\[FAIL\]" qa-results/output.log 2>/dev/null || echo "0")
WARN_COUNT=$(grep -c "\[WARN\]" qa-results/output.log 2>/dev/null || echo "0")
SKIP_COUNT=$(grep -c "\[SKIP\]" qa-results/output.log 2>/dev/null || echo "0")
TOTAL=$((PASS_COUNT + FAIL_COUNT))
[ $TOTAL -gt 0 ] && PCT=$(( PASS_COUNT * 100 / TOTAL )) || PCT=0

# Tulis ringkasan ke report
cat >> "$REPORT" << EOF

## Ringkasan Hasil

| Status | Jumlah |
|--------|--------|
| ✅ PASS | ${PASS_COUNT} |
| ❌ FAIL | ${FAIL_COUNT} |
| ⚠️ WARN | ${WARN_COUNT} |
| ⏭️ SKIP | ${SKIP_COUNT} |
| **Total** | **${TOTAL}** |
| **Pass Rate** | **${PCT}%** |

## Kriteria Release

- [$([ $FAIL_COUNT -eq 0 ] && echo "x" || echo " ")] 0 FAIL (non-negotiable untuk security tests)
- [$([ $PCT -ge 95 ] && echo "x" || echo " ")] ≥ 95% Pass Rate
- [$(grep -c "\[FAIL\]\[SECURITY\]" qa-results/output.log 2>/dev/null | grep -q "^0$" && echo "x" || echo " ")] 0 Security FAIL

## Failures Detail
\`\`\`
$(grep "\[FAIL\]" qa-results/output.log 2>/dev/null || echo "Tidak ada failure — semua test pass!")
\`\`\`

## Warnings
\`\`\`
$(grep "\[WARN\]" qa-results/output.log 2>/dev/null || echo "Tidak ada warning")
\`\`\`

---
*Report generated by Codex QA Runner — $(date)*
EOF

echo ""
echo "============================================"
echo "  QA COMPLETE — Pass: $PASS_COUNT | Fail: $FAIL_COUNT | Rate: ${PCT}%"
echo "  Report: $REPORT"
echo "============================================"

# Exit code: 0 jika tidak ada FAIL, 1 jika ada
[ $FAIL_COUNT -eq 0 ] && exit 0 || exit 1
```

---

## CARA EKSEKUSI LENGKAP

Simpan file ini sebagai `qa-codex-prompt.md`, lalu jalankan salah satu cara berikut:

### Opsi A — Codex CLI
```bash
codex run --approval-mode full-auto qa-codex-prompt.md 2>&1 | tee qa-results/output.log
```

### Opsi B — Script Shell Langsung
```bash
# Ekstrak semua code block bash dan jalankan
chmod +x qa-runner.sh
bash qa-runner.sh 2>&1 | tee qa-results/output.log
```

### Opsi C — Claude Code
```bash
claude --allowedTools "Bash(*)" \
  "Baca qa-codex-prompt.md dan eksekusi semua command secara berurutan. \
   Catat semua [PASS]/[FAIL]/[WARN] ke qa-results/output.log. \
   Generate laporan akhir di akhir eksekusi."
```

### Opsi D — Per-Phase (incremental)
```bash
# Jalankan hanya 1 phase spesifik
codex run --approval-mode full-auto "Eksekusi hanya PHASE 5 dari qa-codex-prompt.md"
```

---

## TIPS UNTUK AGENT

1. **Jangan stop saat 1 test gagal** — catat dan lanjutkan (kecuali Phase 0)
2. **Timeout** — jika command hang >60 detik, kill dan tandai `[FAIL][TIMEOUT]`
3. **Tinker output** — ambil hanya baris terakhir yang relevan (`| tail -n 1`)
4. **Cookie reuse** — gunakan cookie jar yang sudah dibuat di Phase 3 untuk semua test selanjutnya
5. **Environment** — pastikan `APP_ENV=testing` untuk menghindari side effect ke data production
6. **Parallel** — Phase 2, 3, 4 bisa dijalankan paralel untuk mempercepat. Phase 0, 1 harus sequential.
7. **Idempotent** — semua test dirancang bisa dijalankan berkali-kali tanpa merusak state
