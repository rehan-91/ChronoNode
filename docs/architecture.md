# System Architecture & Dataflow Logic

## 1. Employee Check-In Pipeline (Mobile Location API to MySQL Commit)

This flowchart maps the strict, type-safe execution path taken when an employee initiates a geospatial check-in.

```text
[1] CLIENT (Vue.js + Browser Geolocation API)
 ├── User triggers "Check In". 
 ├── Browser prompts for GPS permissions.
 ├── Captures: { latitude: float, longitude: float, accuracy: float }
 └── Transmits POST /api/attendance/check-in with Bearer Token / Session Cookie.
         │
[2] ROUTER & MIDDLEWARE (Laravel Sanctum/Session)
 ├── Verifies active authentication.
 ├── Validates active Employee Role.
 └── Rejects payload if unauthenticated (401).
         │
[3] FORM REQUEST (StoreAttendanceRequest)
 ├── Strict Type Validation: Enforces lat/lon as numeric coordinates.
 ├── Accuracy Gate: Rejects request if `accuracy` exceeds global config (e.g., > 200m).
 └── Rejects invalid payloads instantly (422).
         │
[4] SERVICE LAYER: CONCURRENCY LOCK (AttendanceService @ DB::transaction)
 ├── Initiates atomic database transaction.
 ├── Acquires Pessimistic Lock (`lockForUpdate()`) on the Employee's daily attendance row.
 └── Bounces request (400) if an active check-in already exists for `Carbon::today()`.
         │
[5] DOMAIN LOGIC: GEOFENCE MATH (GeofenceService)
 ├── Fetches assigned `OfficeLocation` for the User.
 ├── Executes Haversine Spherical Calculation matching ping against Office Lat/Lon.
 └── Gate: If distance > Office `radius` + global `buffer`, transaction aborts (403 Forbidden).
         │
[6] DOMAIN LOGIC: SHIFT CALCULATION (ShiftService)
 ├── Compares `Carbon::now()` against assigned Shift `start_time`.
 ├── Applies global `late_arrival_buffer_minutes`.
 └── Determines state: 'present' OR 'late' (and calculates `late_minutes`).
         │
[7] PERSISTENCE (MySQL 8.4)
 ├── Commits `Attendance` record (user_id, date, check_in_time, location_id, status).
 └── Releases DB transaction lock.
         │
[8] ASYNC EVENTS (Laravel Queues)
 ├── Dispatches Audit Log event for immutable tracking.
 ├── Triggers UI WebSocket / polling update for Admin Live Location dashboard.
 └── Returns 201 Created -> Client displays Success Notification.
```

## 2. Critical Repository Directory Layout

The following directory tree maps the exact structure and location of all architectural components generated across the 11 phases.

```text
/
├── app/
│   ├── Enums/
│   │   ├── Role.php                  (super_admin, hr_admin, manager, employee)
│   │   └── AttendanceStatus.php      (present, late, absent, leave, weekend)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AuditLogController.php
│   │   │   │   ├── LiveLocationController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── SettingsController.php
│   │   │   └── Api/
│   │   │       ├── AttendanceController.php
│   │   │       └── LocationPingController.php
│   │   └── Requests/
│   │       ├── StoreAttendanceRequest.php
│   │       ├── StoreShiftRequest.php
│   │       └── Report/GenerateReportRequest.php
│   ├── Jobs/
│   │   ├── CompileReportJob.php
│   │   └── PurgeStaleLocationLogsJob.php
│   ├── Models/
│   │   ├── Attendance.php
│   │   ├── AuditLog.php              (Immutable, append-only)
│   │   ├── Department.php
│   │   ├── InternalNotification.php
│   │   ├── LocationLog.php
│   │   ├── OfficeLocation.php
│   │   ├── SystemSetting.php
│   │   └── User.php
│   └── Services/
│       ├── AttendanceService.php     (Atomic DB transaction locks)
│       ├── AuditLoggerService.php    (Variance tracking)
│       ├── GeofenceService.php       (Haversine math)
│       ├── LocationTrackingConfigService.php
│       ├── NotificationService.php
│       ├── ReportExportService.php   (Chunked CSV memory management)
│       └── SettingsRegistryService.php (Cache::rememberForever implementations)
│
├── database/
│   ├── factories/
│   │   ├── DepartmentFactory.php
│   │   ├── OfficeLocationFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_create_users_table.php
│   │   ├── 0002_create_office_locations_table.php
│   │   ├── 0003_create_attendances_table.php
│   │   ├── 0004_create_location_logs_table.php
│   │   └── 0005_create_audit_logs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php        (Synthetic 150-user realistic corporate matrix)
│
├── docs/
│   ├── architecture.md               (This file)
│   └── developer-guide.md            (Operations & Onboarding)
│
├── resources/
│   └── js/
│       ├── Components/
│       ├── Layouts/
│       │   └── AuthenticatedLayout.vue
│       ├── Pages/
│       │   ├── Admin/
│       │   │   ├── AuditLogs/Index.vue
│       │   │   ├── Locations/Live.vue
│       │   │   ├── Reports/Index.vue
│       │   │   └── Settings/Index.vue
│       │   ├── Dashboard.vue
│       │   └── Employee/
│       │       └── Attendance/Index.vue
│       └── types/
│           └── index.d.ts            (Strict TS interfaces binding PHP Enums)
│
├── tests/
│   ├── Feature/
│   │   └── AttendanceConstraintsTest.php (Validates 400/403/401 API rejections)
│   └── Unit/
│       └── GeofenceMathTest.php          (Validates Haversine Spherical calculations)
│
├── package.json
└── composer.json
```
