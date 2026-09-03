# API Manifest Documentation

This document details the standard REST API operational endpoints for the HRMS Platform, outlining expected payloads, data types, and status codes.

## General Considerations
- All successful requests return standard HTTP `2xx` responses with structured JSON.
- Validation errors return `422 Unprocessable Entity`.
- Server faults or database connection drops are captured and return `500 Internal Server Error`.

---

## 1. Infrastructure Checks

### **GET `/health`**
Executes an atomic query against the MySQL 8.4 database connection pool and reports server health.

*   **Responses:**
    *   `200 OK`: `{ "status": "healthy", "message": "Infrastructure operational", "database": "connected" }`
    *   `500 Internal Server Error`: `{ "status": "unhealthy", "message": "Internal infrastructure failure" }`

---

## 2. Authentication Filters

### **POST `/login`**
Authenticates an employee and issues session parameters.

*   **Payload (JSON):**
    *   `email` (string, required): Standard valid email format.
    *   `password` (string, required): Minimum 8 characters.
*   **Responses:**
    *   `200 OK`: Authentication succeeded.
    *   `422 Unprocessable Entity`: Invalid credentials.

### **POST `/logout`**
Terminates the active session and flushes state.

*   **Responses:**
    *   `200 OK`: Successfully logged out.

---

## 3. Geofenced Attendance Pipelines

### **POST `/api/attendance/check-in`**
Validates live location coordinates against the registered office radius via Haversine logic, logs the shift start time, and generates an atomic DB lock to prevent duplicates.

*   **Payload (JSON):**
    *   `latitude` (float, required): Precise active location footprint (e.g., `40.7128`).
    *   `longitude` (float, required): Precise active location footprint (e.g., `-74.0060`).
    *   `accuracy` (float, required): GPS accuracy threshold in meters.
*   **Responses:**
    *   `201 Created`: `{ "status": "success", "message": "Check-in successful", "data": { ... } }`
    *   `403 Forbidden`: User outside geofenced radius.
    *   `409 Conflict`: Duplicate check-in attempt for the active shift.

### **POST `/api/attendance/check-out`**
Closes the active attendance log and finalizes worked hour tabulations.

*   **Payload (JSON):**
    *   `latitude` (float, required): Precise location footprint.
    *   `longitude` (float, required): Precise location footprint.
    *   `accuracy` (float, required): GPS accuracy threshold.
*   **Responses:**
    *   `200 OK`: `{ "status": "success", "message": "Check-out successful", "data": { ... } }`
    *   `404 Not Found`: No active check-in record found to close.

---

## 4. Administrative Override & Corrections

### **POST `/api/corrections/{id}/review`**
Processes an Employee Attendance Correction request (Approve, Reject, or Edit).

*   **Payload (JSON):**
    *   `status` (string, required): Allowed values `['approved', 'rejected']`.
    *   `reviewer_reason` (string, required): Mandatory reasoning text for audit log compliance.
    *   `edited_check_in` (string, optional): ISO8601 DateTime for manual correction overriding.
    *   `edited_check_out` (string, optional): ISO8601 DateTime for manual correction overriding.
*   **Responses:**
    *   `200 OK`: Processing successful, state mutated, and notification dispatched.
    *   `403 Forbidden`: Insufficient manager/admin privileges.

---

## 5. Leave Workflow Pipelines

### **POST `/api/leaves`**
Submits a formalized leave request, triggering preliminary deduction checks against leave balances.

*   **Payload (JSON):**
    *   `type` (string, required): Sourced from internal enums (e.g., `annual`, `sick`, `unpaid`).
    *   `start_date` (string, required): Format `YYYY-MM-DD`.
    *   `end_date` (string, required): Format `YYYY-MM-DD`, must be `>= start_date`.
    *   `reason` (string, required): Explanation for the leave request.
*   **Responses:**
    *   `201 Created`: Request queued for manager review.

### **POST `/api/leaves/{id}/review`**
Administrative endpoint to finalize the outcome of a leave request, capturing reasoning and securely locking historical records.

*   **Payload (JSON):**
    *   `status` (string, required): Allowed values `['approved', 'rejected']`.
    *   `reviewer_reason` (string, required): Text explanation required for immutable auditing.
*   **Responses:**
    *   `200 OK`: Workflow pipeline complete and bounds updated securely.

---

## 6. Live Location Ping (Background Retention)

### **POST `/api/locations/ping`**
Receives high-frequency GPS coordinate footprints during active shifts to populate the live location matrix map, discarding data instantly if outside acceptable thresholds.

*   **Payload (JSON):**
    *   `latitude` (float, required)
    *   `longitude` (float, required)
    *   `accuracy` (float, required)
*   **Responses:**
    *   `200 OK`: `{"status": "logged"}` or `{"status": "stale"}` if precision criteria fail.
