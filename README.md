# HRMS Attendance & Geofencing Platform

A production-ready Human Resources Management System focusing on strict geospatial boundaries, immutable audit logs, and highly optimized attendance tracking. 

## Quickstart Onboarding

This repository enforces strict typing and modern tooling. Ensure your local environment runs **PHP 8.2+**, **Node.js 20+**, and a relational database (preferably **MySQL 8.4** or PostgreSQL).

### Step 1: Clone & Install Dependencies
```bash
git clone <repository_url> hrms-attendance
cd hrms-attendance

# Install PHP dependencies
composer install --optimize-autoloader

# Install Node dependencies (Vue 3, Tailwind, Inertia)
npm install
```

### Step 2: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```
Open `.env` and configure your database and queue drivers:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms_attendance
DB_USERNAME=root
DB_PASSWORD=

# Background Jobs are critical for reports and location purging
QUEUE_CONNECTION=database # (or redis in production)
```

### Step 3: Database Migration & Synthetic Seeding
This will build the schema and populate the database with ~150 employees, offices, managers, and a dense 30-day historical attendance record.
```bash
php artisan migrate:fresh --seed
```
**Test Credentials:** 
*   **Super Admin:** `admin@company.com` / `password`
*   **HR Admin:** `hr@company.com` / `password`

### Step 4: Bootstrapping the Application
You will need two terminal tabs to run the backend engine and the Vite frontend compiler simultaneously.

*Terminal 1 (Laravel Backend & Queue):*
```bash
php artisan serve
# Open a separate tab for the background worker:
php artisan queue:work
```

*Terminal 2 (Vite Frontend):*
```bash
npm run dev
```

### Step 5: Running the Test Suite (QA)
To run the automated bounds checks and Haversine math validations:
```bash
php artisan test
```

For a deeper dive into the system logic, refer to the `/docs` directory.
