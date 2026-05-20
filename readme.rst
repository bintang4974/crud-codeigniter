# Tenant Users API Documentation
 
## Overview

This document describes the new **Tenant Users Management API** endpoints that allow you to list, manage, and control users within a specific tenant in the OMNIX system.

**Total Endpoints:** 6

| # | Endpoint | Method | Description | Auth |
|----|----------|--------|-------------|------|
| 1 | `/tenant/:tenant_code/users` | GET | List users by tenant | JWT |
| 2 | `/tenant/:tenant_code/users/:userId` | GET | Get user detail | JWT |
| 3 | `/tenant/:tenant_code/users/reset-password` | POST | Reset user password | JWT + APPROVER |
| 4 | `/tenant/:tenant_code/users/unlock` | POST | Unlock user account | JWT + APPROVER |
| 5 | `/tenant/:tenant_code/users/reset-2fa` | POST | Reset 2FA settings | JWT + APPROVER |
| 6 | `/tenant/:tenant_code/fail-auth-logs` | GET | Get failed auth logs (today by default) | JWT + APPROVER |

## Base URL
```
http://localhost:7001
```

## Authentication

All endpoints require **JWT Bearer Token** authentication (except where noted).

Include the token in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

## API Endpoints

### 1. List Users by Tenant
**Endpoint:** `GET /tenant/:tenant_code/users`

**Description:** Retrieve a paginated list of all users assigned to a specific tenant.

**Authentication:** ✅ Required (JwtAuthGuard)

**Request Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `tenant_code` | string | required | Tenant code (e.g., 'demo') |
| `skip` | number | 0 | Number of records to skip (pagination offset) |
| `take` | number | 10 | Number of records to retrieve (pagination limit) |
| `search` | string | optional | Search by username, email, or fullname |
| `is_active` | boolean | optional | Filter by active status (true/false) |

**Example Request:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?skip=0&take=10&is_active=true" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "userid": 4,
      "email": "john.anderson@company.com",
      "fullname": "John Anderson",
      "nickname": "john.anderson",
      "is_active": true,
      "expired_at": "2027-12-31T23:59:59.000Z",
      "fail_login": 0,
      "username": "john.anderson",
      "role": "2"
    },
    {
      "userid": 5,
      "email": "sarah.mitchell@company.com",
      "fullname": "Sarah Mitchell",
      "nickname": "sarah.mitchell",
      "is_active": true,
      "expired_at": "2027-12-31T23:59:59.000Z",
      "fail_login": 0,
      "username": "sarah.mitchell",
      "role": "1"
    }
  ],
  "total": 6,
  "skip": 0,
  "take": 10
}
```

---

### 2. Get User Detail
**Endpoint:** `GET /tenant/:tenant_code/users/:userId`

**Description:** Retrieve detailed information about a specific user in the tenant.

**Authentication:** ✅ Required (JwtAuthGuard)

**Request Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., 'demo') |
| `userId` | number | User ID |

**Example Request:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/users/4" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:** `200 OK`
```json
{
  "userid": 4,
  "email": "john.anderson@company.com",
  "fullname": "John Anderson",
  "nickname": "john.anderson",
  "is_active": true,
  "expired_at": "2027-12-31T23:59:59.000Z",
  "fail_login": 0,
  "username": "john.anderson",
  "role": "2"
}
```

---

### 3. Reset User Password
**Endpoint:** `POST /tenant/:tenant_code/users/reset-password`

**Description:** Reset a user's password and generate a new default password. This endpoint requires **APPROVER role** (role '2').

**Authentication:** ✅ Required (JwtAuthGuard + RolesGuard - APPROVER only)

**Request Body:**
```json
{
  "userId": 4
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/reset-password" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "userId": 4
  }'
```

**Response:** `200 OK`
```json
{
  "message": "Password user berhasil direset",
  "defaultPassword": "Kx9mL@2pQw8Z",
  "email": "john.anderson@company.com"
}
```

**Notes:**
- Returns a randomly generated password
- Resets `fail_login` counter to 0
- Email is sent with new password (in production)

---

### 4. Unlock User (Reset Failed Logins)
**Endpoint:** `POST /tenant/:tenant_code/users/unlock`

**Description:** Unlock a user by resetting the failed login counter. Required when user is locked after multiple failed attempts. This endpoint requires **APPROVER role** (role '2').

**Authentication:** ✅ Required (JwtAuthGuard + RolesGuard - APPROVER only)

**Request Body:**
```json
{
  "userId": 8,
  "reason": "User forgot password and locked out"
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/unlock" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "userId": 8,
    "reason": "Account locked, user requested unlock"
  }'
```

**Response:** `200 OK`
```json
{
  "message": "User berhasil di-unlock",
  "username": "robert.johnson",
  "fail_login": 0
}
```

**Notes:**
- Resets `fail_login` counter to 0
- User can login again after unlock
- Different from deactivate: unlock allows user to login, deactivate prevents login entirely

---

### 5. Reset User 2FA (Two-Factor Authentication)
**Endpoint:** `POST /tenant/:tenant_code/users/reset-2fa`

**Description:** Reset a user's 2FA settings by removing TOTP secret and backup codes. This endpoint requires **APPROVER role** (role '2').

**Authentication:** ✅ Required (JwtAuthGuard + RolesGuard - APPROVER only)

**Request Body:**
```json
{
  "userId": 4
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/reset-2fa" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "userId": 4
  }'
```

**Response:** `200 OK`
```json
{
  "message": "2FA user berhasil direset",
  "email": "john.anderson@company.com",
  "success": true
}
```

**Notes:**
- Clears TOTP secret and backup codes
- User can still login (different from deactivate)
- User must re-enable 2FA by scanning new QR code after reset
- Next login will not require 2FA

---

### 6. Get Failed Auth Logs
**Endpoint:** `GET /tenant/:tenant_code/fail-auth-logs`

**Description:** Retrieve a paginated list of failed authentication attempts for a specific tenant. Filter automatically shows today's logs by default. This endpoint requires **APPROVER role** (role '2').

**Authentication:** ✅ Required (JwtAuthGuard + RolesGuard - APPROVER only)

**Request Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `tenant_code` | string | required | Tenant code (e.g., 'demo') |
| `skip` | number | 0 | Number of records to skip (pagination offset) |
| `take` | number | 50 | Number of records to retrieve (pagination limit, max 100) |
| `date` | string | today | Filter date in YYYY-MM-DD format (e.g., '2026-05-20') |
| `search` | string | optional | Search by username or email |

**Example Request - Today's Logs:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/fail-auth-logs?skip=0&take=50" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Example Request - Specific Date with Search:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/fail-auth-logs?date=2026-05-20&search=john&skip=0&take=50" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "userid": 4,
      "email": "john.anderson@company.com",
      "password": "john.anderson:wrongpassword",
      "message": "Invalid credentials",
      "ip_address": "192.168.1.100",
      "created_at": "2026-05-20T10:30:45.000Z"
    },
    {
      "userid": 5,
      "email": "sarah.mitchell@company.com",
      "password": "sarah.mitchell:expiredpass",
      "message": "User account expired",
      "ip_address": "192.168.1.101",
      "created_at": "2026-05-20T10:25:12.000Z"
    }
  ],
  "total": 2,
  "skip": 0,
  "take": 50,
  "date": "2026-05-20"
}
```

**Error Responses:**

404 Not Found:
```json
{
  "message": "Tenant not found",
  "statusCode": 404
}
```

403 Forbidden (when user doesn't have APPROVER role):
```json
{
  "message": "Forbidden",
  "statusCode": 403
}
```

**Notes:**
- Default filter is today's date only
- Pagination: 50 records per page by default (can be adjusted with `take` parameter)
- Results are sorted by `created_at` in descending order (newest first)
- Response includes specific date that was filtered
- Search is case-insensitive
- Data includes IP address for security tracking

---

## Response DTOs

### TenantUserDto
```typescript
{
  userid: number;              // User ID
  email: string;               // Primary email (corporate or non-corporate)
  fullname: string;            // Full name from user_profile
  nickname: string;            // Username
  is_active: boolean;          // Active status
  expired_at: Date;            // Account expiration date
  fail_login: number;          // Failed login attempts counter
  username: string;            // Username
  role: string;                // User role (1=REQUESTER, 2=APPROVER, 3=ITOPS, 4=ITITSI)
}
```

### TenantUserListResponseDto
```typescript
{
  data: TenantUserDto[];       // Array of users
  total: number;               // Total count of users matching criteria
  skip: number;                // Offset used in query
  take: number;                // Limit used in query
}
```

### FailAuthLogDto
```typescript
{
  userid: number;              // User ID yang gagal login
  email: string;               // Email pengguna
  password: string;            // Username:password yang di-attempt
  message: string;             // Pesan error/alasan gagal
  ip_address: string;          // IP address dari attempt
  created_at: Date;            // Timestamp attempt
}
```

### FailAuthLogListResponseDto
```typescript
{
  data: FailAuthLogDto[];      // Array of failed auth logs
  total: number;               // Total count of failed attempts
  skip: number;                // Offset used in query
  take: number;                // Limit used in query
  date: string;                // Tanggal filter (YYYY-MM-DD)
}
```

---

## User Roles

| Role ID | Role Name | Description |
|---------|-----------|-------------|
| 1 | REQUESTER | Can request services/access |
| 2 | APPROVER | Can approve requests and manage users |
| 3 | ITOPS | IT Operations - can manage infrastructure |
| 4 | ITITSI | IT IT Systems Infrastructure |

---

## Demo Tenant Users

The following users are pre-populated in the demo tenant (`tenant_code: 'demo'`, `tenant_id: 'onx_dev'`):

| ID | Username | Name | Role | Status | Fail Login | Expired At |
|----|----------|------|------|--------|------------|-----------|
| 4 | john.anderson | John Anderson | APPROVER (2) | ✅ Active | 0 | 2027-12-31 |
| 5 | sarah.mitchell | Sarah Mitchell | REQUESTER (1) | ✅ Active | 0 | 2027-12-31 |
| 6 | michael.chen | Michael Chen | REQUESTER (1) | ✅ Active | 0 | 2027-12-31 |
| 7 | andea.wijaya | Andea Wijaya | APPROVER (2) | ✅ Active | 2 | 2027-06-30 |
| 8 | robert.johnson | Robert Johnson | ITOPS (3) | ❌ Inactive | 5 | 2026-03-15 |
| 9 | emily.davis | Emily Davis | ITITSI (4) | ✅ Active | 0 | 2027-12-31 |

---

## Test Cases

### Test 1: List All Users
```bash
curl -X GET "http://localhost:7001/tenant/demo/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected:** Returns 6 users with pagination info

### Test 2: List Active Users Only
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?is_active=true" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected:** Returns 5 active users (robert.johnson is inactive)

### Test 3: Search Users by Email
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?search=john" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected:** Returns john.anderson user

### Test 4: Get Specific User Detail
```bash
curl -X GET "http://localhost:7001/tenant/demo/users/4" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected:** Returns john.anderson user details

### Test 5: Reset User Password (APPROVER only)
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/reset-password" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"userId": 5}'
```
**Expected:** Returns new generated password for sarah.mitchell

### Test 6: Unlock User (APPROVER only)
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/unlock" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"userId": 8}'
```
**Expected:** Resets fail_login counter for robert.johnson to 0

### Test 7: Reset 2FA (APPROVER only)
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/reset-2fa" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"userId": 4}'
```
**Expected:** Clears 2FA settings for john.anderson

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "User dengan ID 999 tidak ditemukan di tenant ini",
  "error": "Bad Request",
  "statusCode": 400
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthorized",
  "statusCode": 401
}
```

### 403 Forbidden (Insufficient Role)
```json
{
  "message": "Forbidden - APPROVER role required",
  "error": "Forbidden",
  "statusCode": 403
}
```

---

## Data Fields Explanation

### userid
The unique identifier for the user in the system. Used as `userId` in API requests.

### email
The primary email address for the user. Priority: corporate email > non-corporate email.

### fullname
The user's full name from the user_profile table.

### nickname
The username - unique identifier for login.

### is_active
Boolean flag indicating if user account is active. 
- `true`: User can login
- `false`: User account is deactivated, cannot login

### expired_at
The date when the user account will expire. After this date, user cannot login.

### fail_login
Counter for failed login attempts. Increments on each failed login, resets to 0 after successful login or manual unlock.

### username
The username used for login (same as nickname).

### role
User's role/permission level:
- **1** = REQUESTER - Basic user
- **2** = APPROVER - Can approve and manage users
- **3** = ITOPS - IT Operations
- **4** = ITITSI - IT Systems Infrastructure

---

## Integration with Frontend

The API response format matches the requirements shown in your specification sheet:

| Frontend Field | API Response Field | Type | Example |
|--|--|--|--|
| userid (kolom id) | userid | number | 4 |
| email | email | string | john.anderson@company.com |
| fullname | fullname | string | John Anderson |
| nickname | nickname | string | john.anderson |
| is_active | is_active | boolean | true |
| expired_at | expired_at | date | 2027-12-31T23:59:59.000Z |
| fail_login | fail_login | number | 0 |

---

## Database Schema

### user table
```sql
CREATE TABLE user (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL UNIQUE,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('1','2','3','4') DEFAULT '1',
  is_active BOOLEAN DEFAULT true,
  tenant_id VARCHAR(255) NULL,      -- NEW
  expired_at DATETIME NULL,         -- NEW
  fail_login INT DEFAULT 0,         -- NEW
  create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  update_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX IDX_user_is_active (is_active),
  INDEX IDX_user_tenant_id (tenant_id),
  INDEX IDX_user_expired_at (expired_at)
);
```

### user_profile table
```sql
CREATE TABLE user_profile (
  id VARCHAR(36) PRIMARY KEY,
  user_id VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(255),
  email_corporate VARCHAR(255),
  email_non_corporate VARCHAR(255),
  ...
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Source Code Files

The following files were created/modified:

1. **src/tenant/dto/tenant-users.dto.ts** - Request/response DTOs
2. **src/tenant/tenant-users.service.ts** - Business logic
3. **src/tenant/tenant.controller.ts** - API endpoints (updated)
4. **src/tenant/tenant.module.ts** - Module configuration (updated)
5. **src/database/entities/user.entity.ts** - User entity (updated with new fields)
6. **src/database/migrations/AddTenantFieldsToUser1715952000000.ts** - Database migration

---

## Version Info

- **API Version:** 1.0
- **Last Updated:** May 20, 2026
- **NestJS Version:** 10.3.8
- **TypeORM Version:** 0.3+
- **Database:** MySQL 8.0

