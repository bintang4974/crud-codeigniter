# 👥 Tenant Users API Documentation

> Comprehensive API documentation for managing tenant users in the OMNIX system.

## 📋 Overview

This document describes the **Tenant Users Management API** endpoints that allow you to list, manage, and control users within a specific tenant in the OMNIX application.

- **Total Endpoints:** 6
- **Authentication:** JWT Bearer Token (required)
- **Base URL:** `http://localhost:7001`
- **API Version:** 1.0

---

## 🎯 Quick Reference

| # | Endpoint | Method | Description | Role |
|---|----------|--------|-------------|------|
| **1** | `/tenant/:tenant_code/users` | `GET` | List tenant users | JWT |
| **2** | `/tenant/:tenant_code/users/:userId` | `GET` | Get user detail | JWT |
| **3** | `/tenant/:tenant_code/users/reset-password` | `POST` | Reset password | APPROVER |
| **4** | `/tenant/:tenant_code/users/unlock` | `POST` | Unlock account | APPROVER |
| **5** | `/tenant/:tenant_code/users/reset-2fa` | `POST` | Reset 2FA | APPROVER |
| **6** | `/tenant/:tenant_code/fail-auth-logs` | `GET` | Failed auth logs | APPROVER |

---

## 🔐 Authentication

All endpoints require **JWT Bearer Token** authentication in the request header.

```bash
Authorization: Bearer {jwt_token}
```

> **Note:** Management endpoints (reset-password, unlock, reset-2fa, fail-auth-logs) require **APPROVER role** (role ID: 2)

---

## 📚 API Endpoints

### 1️⃣ List Users by Tenant

**`GET /tenant/:tenant_code/users`**

Retrieve a paginated list of all users assigned to a specific tenant.

**Authentication:** ✅ JWT Required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `skip` | number | `0` | Records to skip (pagination offset) |
| `take` | number | `10` | Records to retrieve (pagination limit) |
| `search` | string | - | Search by username, email, or fullname |
| `is_active` | boolean | - | Filter by active status (true/false) |

**Example Request:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?skip=0&take=10&is_active=true" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
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
    }
  ],
  "total": 6,
  "skip": 0,
  "take": 10
}
```

---

### 2️⃣ Get User Detail

**`GET /tenant/:tenant_code/users/:userId`**

Retrieve detailed information about a specific user in the tenant.

**Authentication:** ✅ JWT Required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |
| `userId` | number | User ID |

**Example Request:**
```bash
curl -X GET "http://localhost:7001/tenant/demo/users/4" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
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

### 3️⃣ Reset User Password

**`POST /tenant/:tenant_code/users/reset-password`**

Reset a user's password and generate a new default password.

**Authentication:** ✅ JWT + **APPROVER** role required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |

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
  -d '{"userId": 4}'
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
- Returns a randomly generated strong password
- Resets `fail_login` counter to 0
- Password should be sent to user via email

---

### 4️⃣ Unlock User Account

**`POST /tenant/:tenant_code/users/unlock`**

Unlock a user by resetting the failed login counter. Use when account is locked after multiple failed attempts.

**Authentication:** ✅ JWT + **APPROVER** role required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |

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
  -d '{"userId": 8, "reason": "Account locked"}'
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
- User can login immediately after unlock
- Optional `reason` field for audit logging

---

### 5️⃣ Reset User 2FA

**`POST /tenant/:tenant_code/users/reset-2fa`**

Reset a user's 2FA settings by clearing TOTP secret and backup codes.

**Authentication:** ✅ JWT + **APPROVER** role required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |

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
  -d '{"userId": 4}'
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
- User must re-enable 2FA on next login
- Next login will NOT require 2FA

---

### 6️⃣ Get Failed Auth Logs

**`GET /tenant/:tenant_code/fail-auth-logs`**

Retrieve failed authentication attempts for a tenant. Shows today's logs by default.

**Authentication:** ✅ JWT + **APPROVER** role required

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `tenant_code` | string | Tenant code (e.g., `demo`) |

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `skip` | number | `0` | Records to skip (pagination offset) |
| `take` | number | `50` | Records to retrieve (max 100) |
| `date` | string | today | Filter date (YYYY-MM-DD format) |
| `search` | string | - | Search by username or email |

**Example Requests:**
```bash
# Today's failed logins
curl -X GET "http://localhost:7001/tenant/demo/fail-auth-logs?skip=0&take=50" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Specific date with search
curl -X GET "http://localhost:7001/tenant/demo/fail-auth-logs?date=2026-05-20&search=john" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
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
    }
  ],
  "total": 5,
  "skip": 0,
  "take": 50,
  "date": "2026-05-20"
}
```

**Notes:**
- Default filter shows today only
- Results sorted by latest first (DESC)
- Includes IP address for security tracking
- Search is case-insensitive

---

## 📦 Response Models

### TenantUserDto
User information response object.

```typescript
{
  userid: number;              // User ID
  email: string;               // Primary email address
  fullname: string;            // Full name
  nickname: string;            // Username/nickname
  is_active: boolean;          // Active status
  expired_at: Date;            // Account expiration date
  fail_login: number;          // Failed login counter
  username: string;            // Username
  role: string;                // Role ID (1, 2, 3, 4)
}
```

### TenantUserListResponseDto
Paginated user list response.

```typescript
{
  data: TenantUserDto[];       // Array of users
  total: number;               // Total count
  skip: number;                // Offset used
  take: number;                // Limit used
}
```

### FailAuthLogDto
Failed authentication attempt record.

```typescript
{
  userid: number;              // User ID
  email: string;               // User email
  password: string;            // Username:password attempt
  message: string;             // Error message
  ip_address: string;          // IP address
  created_at: Date;            // Attempt timestamp
}
```

### FailAuthLogListResponseDto
Paginated failed auth logs response.

```typescript
{
  data: FailAuthLogDto[];      // Failed auth attempts
  total: number;               // Total count
  skip: number;                // Offset used
  take: number;                // Limit used
  date: string;                // Filtered date (YYYY-MM-DD)
}
```

---

## 👤 User Roles

| ID | Role | Description |
|----|------|-------------|
| **1** | REQUESTER | Can request services/access |
| **2** | APPROVER | Can approve requests and manage users ⭐ |
| **3** | ITOPS | IT Operations - infrastructure management |
| **4** | ITITSI | IT Systems Infrastructure |

> ⭐ Only APPROVER (role 2) can access management endpoints

---

## 👥 Demo Tenant Users

Pre-populated users in demo tenant (`demo` / `onx_dev`):

| ID | Username | Name | Role | Status | Failed | Expires |
|----|----------|------|------|--------|--------|---------|
| 4 | john.anderson | John Anderson | APPROVER | ✅ | 0 | 2027-12-31 |
| 5 | sarah.mitchell | Sarah Mitchell | REQUESTER | ✅ | 0 | 2027-12-31 |
| 6 | michael.chen | Michael Chen | REQUESTER | ✅ | 0 | 2027-12-31 |
| 7 | andea.wijaya | Andea Wijaya | APPROVER | ✅ | 2 | 2027-06-30 |
| 8 | robert.johnson | Robert Johnson | ITOPS | ❌ | 5 | 2026-03-15 |
| 9 | emily.davis | Emily Davis | ITITSI | ✅ | 0 | 2027-12-31 |

---

## 🧪 Test Cases

### Test 1: List All Users
```bash
curl -X GET "http://localhost:7001/tenant/demo/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
**Expected:** 6 users with pagination

### Test 2: Active Users Only
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?is_active=true" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
**Expected:** 5 active users

### Test 3: Search by Username
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?search=john" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
**Expected:** john.anderson user

### Test 4: Pagination
```bash
curl -X GET "http://localhost:7001/tenant/demo/users?skip=0&take=5" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
**Expected:** First 5 users

### Test 5: Reset Password (APPROVER)
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/reset-password" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"userId": 5}'
```
**Expected:** New password for sarah.mitchell

### Test 6: Unlock User (APPROVER)
```bash
curl -X POST "http://localhost:7001/tenant/demo/users/unlock" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"userId": 8}'
```
**Expected:** fail_login reset to 0

---

## ⚠️ Error Responses

### 400 Bad Request
```json
{
  "message": "User dengan ID 999 tidak ditemukan di tenant ini",
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

### 403 Forbidden
```json
{
  "message": "Insufficient permission - APPROVER role required",
  "statusCode": 403
}
```

---

## 🔧 Field Explanations

### userid
Unique user identifier. Used in API requests as `userId`.

### email
Primary email address. Uses corporate email if available, falls back to non-corporate.

### fullname
User's full name from profile.

### is_active
- `true` = User can login
- `false` = User account deactivated

### expired_at
Account expiration date. User cannot login after this date.

### fail_login
Counter for failed login attempts. Increments on each failure, resets on successful login or manual unlock.

### role
User's permission level (1, 2, 3, or 4)

---

## 🔗 Frontend Integration

Response fields map directly to UI specification:

| UI Field | API Field | Type |
|----------|-----------|------|
| Kolom ID | userid | number |
| Email | email | string |
| Fullname | fullname | string |
| Nickname | nickname | string |
| Is Active | is_active | boolean |
| Expired At | expired_at | date |
| Fail Login | fail_login | number |

---

## 💾 Database Schema

### user table
```sql
CREATE TABLE user (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL UNIQUE,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('1','2','3','4') DEFAULT '1',
  is_active BOOLEAN DEFAULT true,
  tenant_id VARCHAR(255) NULL,
  expired_at DATETIME NULL,
  fail_login INT DEFAULT 0,
  create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  update_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX IDX_user_is_active (is_active),
  INDEX IDX_user_tenant_id (tenant_id),
  INDEX IDX_user_expired_at (expired_at)
);
```

### log_fail_auth table
```sql
CREATE TABLE log_fail_auth (
  id INT PRIMARY KEY AUTO_INCREMENT,
  userid INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  username VARCHAR(255),
  password TEXT,
  message TEXT,
  tenant_id VARCHAR(255),
  ip_address VARCHAR(50),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX IDX_LOG_FAIL_AUTH_TENANT_DATE (tenant_id, created_at),
  INDEX IDX_LOG_FAIL_AUTH_USERID (userid)
);
```

---

## 📂 Source Files

Core implementation files:

- `src/tenant/dto/tenant-users.dto.ts` - DTOs
- `src/tenant/tenant-users.service.ts` - Business logic
- `src/tenant/tenant.controller.ts` - API endpoints
- `src/tenant/tenant.module.ts` - Module setup
- `src/database/entities/user.entity.ts` - User entity
- `src/database/entities/log_fail_auth.entity.ts` - Log entity
- `src/database/migrations/` - Database migrations

---

## ✨ Version Info

| Item | Value |
|------|-------|
| **API Version** | 1.0 |
| **NestJS** | 10.3.8 |
| **TypeORM** | 0.3+ |
| **MySQL** | 8.0+ |
| **Last Updated** | May 20, 2026 |

---

## 📞 Support

For issues or questions:

1. Check error responses above
2. Verify JWT token is valid
3. Ensure user has required role
4. Check tenant_code exists

---

**Made with ❤️ for OMNIX Tenant Management**

