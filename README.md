# School Management System

A full-stack school management system built with **Laravel 10**, **MySQL**, and **Bootstrap 5** — three roles (Admin, Teacher, Student/Parent), covering students & teachers, classes/sections/subjects, attendance, exams & grades, and fees & payments.

## What's included

| Module | Admin | Teacher | Student |
|---|---|---|---|
| Students & Teachers (CRUD) | ✅ full CRUD | — | view own profile |
| Classes / Sections / Subjects | ✅ full CRUD | view assignments | view own class |
| Attendance | view (via students) | ✅ mark daily | ✅ view own history |
| Exams & Grades | ✅ create exams, view report cards | ✅ enter marks per subject | ✅ view own results |
| Fees & Payments | ✅ generate invoices, record payments | — | ✅ view own invoices/balance |

## Requirements

- PHP 8.1+
- Composer
- MySQL 5.7+ / 8
- (Optional) Node not required — frontend uses Bootstrap via CDN, no build step

## Setup (run these on your own machine, in this project folder)

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file and set your MySQL credentials
cp .env.example .env
# edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Create the database (in MySQL)
mysql -u root -p -e "CREATE DATABASE school_management"

# 4. Generate the app key
php artisan key:generate

# 5. Run migrations + seed demo data
php artisan migrate --seed

# 6. Start the dev server
php artisan serve
```

Then open **http://localhost:8000**.

## Kenya-specific modules (new)

| Module | What it does | Setup needed |
|---|---|---|
| **CBC Assessment** | Admin defines Learning Areas → Strands → Sub-strands per MoE's Competency Based Curriculum. Teachers rate learners EE/ME/AE/BE per sub-strand each term. Printable Learner Progress Report combines all ratings. Junior/Senior School level + Senior pathway (STEM / Arts & Sports Science / Social Sciences) are tracked per student. | Works immediately, no external account needed. |
| **M-Pesa STK Push** | Admin clicks "M-Pesa" on any unpaid invoice, enters the guardian's phone, and it triggers a real Daraja STK push (sandbox mode by default). Safaricom's callback automatically records the payment and updates the invoice balance. | Free Daraja sandbox app at https://developer.safaricom.co.ke. See below. |
| **Bulk SMS** | Admin sends announcements/fee reminders/closure alerts to all parents, one class, or everyone with an unpaid balance, via Africa's Talking. Every message is logged with delivery status. | Free sandbox account at https://africastalking.com. Works with **no key at all** — messages are logged as "queued" so the whole flow demos correctly even before you add credentials. |
| **Payroll (PAYE/SHIF/NSSF/Housing Levy)** | Admin adds staff (teaching + non-teaching) with salary/allowances, then generates a monthly payslip with the full Kenyan statutory breakdown and a printable payslip. | Works immediately. Rates live in `config/payroll.php` — double-check them against current KRA/NSSF/SHIF publications before using for real payroll, since these are periodically revised. |

### Getting M-Pesa STK Push working for a live demo

Safaricom's sandbox needs a **publicly reachable HTTPS URL** for the callback — `localhost` won't work. Easiest option: use [ngrok](https://ngrok.com) alongside `php artisan serve`:

```bash
php artisan serve            # runs on http://localhost:8000
ngrok http 8000               # gives you e.g. https://abcd1234.ngrok-free.app
```

Then in `.env`:
```
MPESA_CALLBACK_URL=https://abcd1234.ngrok-free.app/mpesa/callback
```
Get your sandbox `MPESA_CONSUMER_KEY` / `MPESA_CONSUMER_SECRET` free from the Daraja portal (create an app under "My Apps" — the default sandbox shortcode/passkey in `.env.example` are Safaricom's public test values and already work). Test payments use Safaricom's sandbox test MSISDN `254708374149` with PIN `12345` — no real money moves in sandbox mode.

### Getting Bulk SMS working

Sign up free at africastalking.com, grab your **sandbox** API key from the dashboard, and set `AT_API_KEY` in `.env` (username stays `sandbox`). Sandbox SMS don't reach a real phone but do exercise the full API round-trip — useful to show it's a genuine integration, not a mock.

## Demo login accounts (password for all: `password`)

| Role | Email |
|---|---|
| Admin | admin@school.test |
| Teacher | teacher1@school.test (assigned Grade 10-A: Math) |
| Teacher | teacher2@school.test (assigned Grade 10-A: Science) |
| Student | student1@school.test through student5@school.test |

The seeder also creates 2 classes (Grade 9, Grade 10) with sections, subjects, 5 days of attendance history, a Mid-Term Exam with results for Grade 10, and fee invoices in paid/partial/unpaid states — so every screen has real data to show on Friday.

## Suggested demo flow

1. **Login as Admin** → Dashboard (stats) → Students (admit a new student live) → Classes/Sections/Subjects → Exams (open Mid-Term results) → Invoices & Payments (trigger a live M-Pesa STK push, or record a manual payment) → CBC Curriculum (show Learning Areas/Strands) → Bulk SMS (send a fee reminder broadcast) → Staff & Payroll (generate a payslip and show the PAYE/SHIF/NSSF/Housing Levy breakdown).
2. **Login as Teacher** (teacher1@school.test) → Take Attendance for Grade 10 → Enter Results for the Mid-Term exam → CBC Assessment (rate a Grade 9 learner on Mathematics sub-strands).
3. **Login as Student** (student1@school.test) → View attendance %, exam results, fee balance, and CBC Report.

## Notes

- Passwords are hashed with bcrypt; the seeder sets all demo accounts to `password`.
- Role-based access is enforced via a custom `role` middleware — visiting another role's URL returns a 403.
- If you want to reset the demo data at any point: `php artisan migrate:fresh --seed`.

## Multi-tenancy

This app now supports multiple schools on one installation (shared database,
row-level isolation). Summary of what changed and how it works:

**Data model**
- New `schools` table (`App\Models\School`) — one row per tenant.
- A `school_id` foreign key was added to every school-owned table (students,
  teachers, classes, exams, fees, payroll, attendance, CBC assessment
  records, SMS/M-Pesa logs, grading scales, etc.) — 20 tables in total.
- `cbc_learning_areas`, `cbc_strands`, `cbc_sub_strands` were left **shared**
  (no `school_id`) since they represent Kenya's national CBC curriculum,
  reused by every school. Actual student *assessments* against that
  curriculum (`cbc_competency_records`) are tenant-scoped as normal.
- `users.email` stays globally unique on purpose (one login page, no
  subdomain routing) — a person's email identifies them across the whole
  platform. `admission_no` and `employee_id` are now unique **per school**
  instead of globally.
- `users.role` gained a `super_admin` value for platform staff who manage
  schools themselves rather than belonging to one.

**How scoping works (`app/Models/Concerns/BelongsToTenant.php`)**
Every tenant-owned model uses this trait, which:
1. Adds a global query scope filtering to the current school automatically.
2. Auto-stamps `school_id` on `Model::create()` when it isn't set explicitly.

Because of this, almost none of the existing controllers needed to change —
`Student::create([...])`, `Exam::with(...)->get()`, `$student->update([...])`
etc. all "just work" per-tenant with zero extra code. The exceptions are
Laravel's `unique:`/`exists:` validation rules, which query the database
directly and bypass Eloquent scopes — those were updated to add an explicit
`->where('school_id', Tenant::id())` (see `Admin\StudentController`,
`TeacherController`, `SchoolClassController`, `SectionController`,
`SubjectController`, `ExamController`, `EmployeeController`,
`FeeInvoiceController`, `ActivityController`, `SmsController`).

**Resolving "which school is this?" (`App\Services\TenantManager` /
`App\Support\Facades\Tenant`)**
- Normal users: resolved from `auth()->user()->school_id`.
- A `super_admin` can temporarily "enter" a school via
  `SuperAdmin\ImpersonationController` (stored in the session).
- No tenant resolved → tenant-scoped queries return **zero rows** rather
  than guessing (fail closed). Console commands/queue jobs must set a tenant
  explicitly with `Tenant::runFor($schoolId, fn () => ...)`, and
  cross-tenant reporting must opt in explicitly with
  `Model::allSchools()->...` or `Tenant::runForAll(fn () => ...)`.
- Wired into both the `web` and `api` middleware groups as
  `App\Http\Middleware\IdentifyTenant` (`app/Http/Kernel.php`).

**Platform administration**
- `App\Http\Controllers\SuperAdmin\SchoolController` — onboard/edit/suspend/
  delete schools (deleting a school cascades and removes all its data).
- `App\Http\Controllers\SuperAdmin\ImpersonationController` — start/stop
  viewing the app as a given school.
- Guard these with the new `super_admin` middleware alias.

**⚠️ This upload didn't include `routes/`, `resources/views/`, or
`app/Http/Middleware/`**, so those couldn't be located and edited directly:
- I added `app/Http/Middleware/IdentifyTenant.php` and `EnsureSuperAdmin.php`
  since that folder wasn't present at all — copy over your project's other
  default middleware (`TrustProxies`, `EncryptCookies`, `Authenticate`, etc.)
  if this folder replaces an empty one.
- `routes/tenancy.php` contains the super-admin route group — merge it into
  your real `routes/web.php`.
- You'll need Blade views for `superadmin.schools.*` (index/create/edit) —
  none existed to extend, so they weren't created. A minimal admin CRUD
  screen (table + form) is enough to start.
- Existing views (admin/teacher/student) don't need changes — they already
  render whatever the (now tenant-scoped) controllers hand them.

**Setup**
```
php artisan migrate:fresh --seed
```
The seeder now creates a platform super admin (`superadmin@platform.test`),
two schools ("Greenwood Academy" with the original demo data, and a small
"Sunrise Junior School") to demonstrate that data doesn't leak between
tenants — e.g. both schools have a student with admission number
`ADM-0001`, and each only sees its own.

**Extending this**
- Per-school subdomains (`greenwood.yourapp.test`): `schools.slug` and
  `schools.domain` already exist for this; `TenantManager::resolve()` would
  need a host-based branch for guest/pre-login pages (currently it only
  resolves from the logged-in user, which is enough for a single shared
  login page).
- Per-school billing/plans: add fields to `schools` and gate features by
  reading `Tenant::current()`.
