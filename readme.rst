# Fail Auth Logs Endpoint Fix - 500 Error Resolution

## Problem
When accessing the fail-auth-logs endpoint via Postman/ngrok:
```
GET https://subacetabular-jodee-literally.ngrok-free.dev/tenant/demo/fail-auth-logs
Response: HTTP 500
{
    "statusCode": 500,
    "message": "Internal server error"
}
```

## Root Cause
**The `log_fail_auth` table did not exist in the database**, causing a database query error when the endpoint tried to retrieve failed authentication logs.

The endpoint implementation in [src/tenant/tenant-users.service.ts](src/tenant/tenant-users.service.ts#L327-L368) calls:
```typescript
const [logs, total] = await this.logFailAuthRepository.findAndCount({
  where: whereConditions,
  skip,
  take,
  order: { created_at: 'DESC' },
});
```

But the table was never created, resulting in a database error being thrown as HTTP 500.

## Solution
Created the missing `log_fail_auth` table in MySQL using the following SQL:

```sql
CREATE TABLE IF NOT EXISTS log_fail_auth (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userid INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  username VARCHAR(255),
  password TEXT,
  message TEXT,
  tenant_id VARCHAR(255),
  ip_address VARCHAR(50),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX IDX_LOG_FAIL_AUTH_TENANT_DATE (tenant_id, created_at),
  INDEX IDX_LOG_FAIL_AUTH_USERID (userid),
  INDEX IDX_LOG_FAIL_AUTH_EMAIL (email)
);
```

### Why The Table Wasn't Created
- A migration file exists at [src/database/migrations/CreateLogFailAuthTable1715952001000.ts](src/database/migrations/CreateLogFailAuthTable1715952001000.ts)
- The migration was not executed in the development database
- Running `npm run migration:run` doesn't work when connecting from the host machine (DNS resolution fails for "mysql" hostname)
- Solution: Manually created the table using docker exec command

## Verification

### ✅ Local Testing (HTTP 200 OK)
```bash
# Generate JWT token
curl http://localhost:7001/auth/debug/token/4/2

# Test endpoint with token
curl -H "Authorization: Bearer <token>" http://localhost:7001/tenant/demo/fail-auth-logs

# Response
{
  "data": [
    {
      "userid": 4,
      "email": "john.new@company.com",
      "password": "john.new:wrongpass",
      "message": "Invalid credentials",
      "ip_address": "192.168.1.100",
      "created_at": "2026-05-20T09:32:34.000Z"
    }
  ],
  "total": 1,
  "skip": 0,
  "take": 50,
  "date": "2026-05-20"
}
```

### ✅ ngrok Tunnel Testing (HTTP 200 OK)
```bash
curl -H "Authorization: Bearer <token>" \
  https://subacetabular-jodee-literally.ngrok-free.dev/tenant/demo/fail-auth-logs

# Same successful response as above
```

## Files Modified
- **Database**: Created `log_fail_auth` table in `master_tenant` database

## Endpoint Details

**Route**: `GET /tenant/:tenant_code/fail-auth-logs`

**Authentication**: Required (Bearer token with APPROVER role)

**Query Parameters**:
- `skip` (default: 0) - Pagination offset
- `take` (default: 50) - Items per page (max 50)
- `date` (optional) - Filter by date in YYYY-MM-DD format (default: today)
- `search` (optional) - Search by username

**Response Format**:
```typescript
{
  data: FailAuthLogDto[];  // Array of failed auth logs
  total: number;            // Total count of records
  skip: number;             // Current pagination offset
  take: number;             // Items per page
  date: string;             // Filtered date (YYYY-MM-DD)
}
```

## Related Files
- Service Implementation: [src/tenant/tenant-users.service.ts](src/tenant/tenant-users.service.ts#L327-L368)
- Controller Endpoint: [src/tenant/tenant.controller.ts](src/tenant/tenant.controller.ts#L698-L708)
- Entity Definition: [src/database/entities/log_fail_auth.entity.ts](src/database/entities/log_fail_auth.entity.ts)
- Migration File: [src/database/migrations/CreateLogFailAuthTable1715952001000.ts](src/database/migrations/CreateLogFailAuthTable1715952001000.ts)
- DTO: [src/tenant/dto/tenant-users.dto.ts](src/tenant/dto/tenant-users.dto.ts)

## Status
✅ **FIXED** - Endpoint now returns 200 OK with failed auth logs data
