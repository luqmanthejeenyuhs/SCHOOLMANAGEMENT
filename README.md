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
