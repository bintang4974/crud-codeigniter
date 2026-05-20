# Activity Logging System Documentation

## Overview

The Activity Logging System tracks all important user actions and changes within the application. All logged activities are stored in the `log_tenant` table in the master_tenant database with comprehensive before/after audit trails.

## Features

- ✅ Automatic logging of user actions
- ✅ Before/after state tracking (JSON format)
- ✅ User context capture (username, userid, tenant_id)
- ✅ Pagination support
- ✅ Advanced filtering and search
- ✅ Non-blocking logging (doesn't break main operations)

## API Endpoints

### 1. Get Tenant Activity Logs
**Endpoint:** `GET /activity-log/tenant`

**Query Parameters:**
- `limit` (optional): Number of records to return (default: 50, max: 500)
- `offset` (optional): Pagination offset (default: 0)

**Example:**
```bash
curl -X GET "http://localhost:7001/activity-log/tenant?limit=50&offset=0" \
  -H "Authorization: Bearer <TOKEN>"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "action": "LOGIN",
      "userid": 123,
      "username": "john.doe",
      "tenant_id": "onx_dev",
      "date_create": "2025-01-15T10:30:45.000Z",
      "before": null,
      "after": "{\"loginTime\":\"2025-01-15T10:30:45.123Z\"}",
      "create_at": "2025-01-15T10:30:45.123Z",
      "update_at": "2025-01-15T10:30:45.123Z"
    }
  ],
  "pagination": {
    "total": 150,
    "limit": 50,
    "offset": 0
  }
}
```

### 2. Get User Activity Logs
**Endpoint:** `GET /activity-log/user`

**Query Parameters:**
- `limit` (optional): Number of records to return (default: 50, max: 500)
- `offset` (optional): Pagination offset (default: 0)

**Example:**
```bash
curl -X GET "http://localhost:7001/activity-log/user?limit=50" \
  -H "Authorization: Bearer <TOKEN>"
```

**Response:** Same format as tenant logs

### 3. Get Logs by Action Type
**Endpoint:** `GET /activity-log/action`

**Query Parameters:**
- `action` (required): Action type to filter by
- `limit` (optional): Number of records to return (default: 50, max: 500)
- `offset` (optional): Pagination offset (default: 0)

**Example:**
```bash
curl -X GET "http://localhost:7001/activity-log/action?action=UPDATE_PROFILE&limit=50" \
  -H "Authorization: Bearer <TOKEN>"
```

### 4. Advanced Search/Filter Logs
**Endpoint:** `GET /activity-log/search`

**Query Parameters:**
- `userId` (optional): Filter by user ID
- `action` (optional): Filter by action type
- `username` (optional): Filter by username
- `startDate` (optional): Filter logs from this date (ISO 8601 format: 2025-01-01)
- `endDate` (optional): Filter logs until this date (ISO 8601 format: 2025-12-31)
- `limit` (optional): Number of records to return (default: 50, max: 500)
- `offset` (optional): Pagination offset (default: 0)

**Example:**
```bash
curl -X GET "http://localhost:7001/activity-log/search?userId=123&action=UPDATE_PROFILE&startDate=2025-01-01&endDate=2025-12-31&limit=100" \
  -H "Authorization: Bearer <TOKEN>"
```

## Logged Actions

### Authentication Actions
- **LOGIN** - User login event
- **RESET_PASSWORD** - Password reset by user
- **CHANGE_PASSWORD** - User changes own password
- **SETUP_2FA** - Two-factor authentication setup

### Profile Actions
- **UPDATE_PROFILE** - User profile information updated
- **UPDATE_USER** - User account information updated

### File Operations
- **UPLOAD_ATTACHMENT** - File/attachment upload

### Other Actions
- **UPDATE_REMARK** - Remark or comment updated
- **ACTIVATE_ACCOUNT** - Account activated
- **DEACTIVATE_ACCOUNT** - Account deactivated

## Before/After Data Structure

Each log entry can contain `before` and `after` fields with JSON-formatted state snapshots:

### Profile Update Example
```json
{
  "action": "UPDATE_PROFILE",
  "before": {
    "name": "John Doe",
    "nik": "1234567890123456",
    "direktorat": "IT",
    "divisi": "Development",
    "email_corporate": "john@company.com"
  },
  "after": {
    "name": "John Smith",
    "nik": "1234567890123456",
    "direktorat": "IT",
    "divisi": "Operations",
    "email_corporate": "john.smith@company.com"
  }
}
```

### Login Example
```json
{
  "action": "LOGIN",
  "before": null,
  "after": {
    "loginTime": "2025-01-15T10:30:45.123Z"
  }
}
```

### File Upload Example
```json
{
  "action": "UPLOAD_ATTACHMENT",
  "before": null,
  "after": {
    "filename": "document.pdf",
    "fileType": "application/pdf",
    "fileSize": 1048576
  }
}
```

## Integration Points

### Where Actions Are Logged

1. **Authentication (`src/auth/auth.service.ts`)**
   - `validateUserWithRecaptcha()` → triggers login logging
   - `resetPassword()` → logs password reset
   - `changePassword()` → logs password change

2. **Profile Management (`src/profile/profile.service.ts`)**
   - `updateProfile()` → logs profile updates with before/after

3. **Activity Log Retrieval (`src/activity-log/activity-log.controller.ts`)**
   - Provides endpoints to query logged activities

## Usage Patterns

### Pattern 1: Audit Trail for User
Track all actions performed by a specific user:
```bash
GET /activity-log/search?userId=123&limit=100
```

### Pattern 2: Review Updates to Specific Entity
Track all updates to a user's profile:
```bash
GET /activity-log/search?userId=123&action=UPDATE_PROFILE&limit=50
```

### Pattern 3: Date Range Audit
Review all activities within a specific date range:
```bash
GET /activity-log/search?startDate=2025-01-01&endDate=2025-01-31&limit=100
```

### Pattern 4: Compliance Report
Get all password resets for compliance:
```bash
GET /activity-log/action?action=RESET_PASSWORD&limit=500
```

## Database Schema

**Table:** `log_tenant`

```sql
CREATE TABLE log_tenant (
  id INT PRIMARY KEY AUTO_INCREMENT,
  action VARCHAR(255) NOT NULL,
  userid INT NOT NULL,
  username VARCHAR(255) NOT NULL,
  tenant_id VARCHAR(255) NOT NULL,
  date_create DATETIME NOT NULL,
  before TEXT NULL,
  after TEXT NULL,
  create_at DATETIME(6) DEFAULT CURRENT_TIMESTAMP(6),
  update_at DATETIME(6) DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  KEY idx_tenant (tenant_id),
  KEY idx_user (userid),
  KEY idx_action (action),
  KEY idx_date (date_create)
);
```

## Error Handling

- Logging failures are non-critical and don't block main operations
- If logging fails, error is logged to console but operation succeeds
- All endpoints include proper error handling and validation
- Maximum pagination limit of 500 records per request

## Service Injection

To add logging to any service:

```typescript
import { TenantActivityLogService } from 'src/libs/tenant-activity-log.service';

@Injectable()
export class MyService {
  constructor(
    private readonly activityLogService: TenantActivityLogService,
  ) {}

  async doSomething(userId: string, username: string, tenantId: string) {
    // Do work...
    
    // Log the action
    await this.activityLogService.log({
      action: 'CUSTOM_ACTION',
      username,
      userid: userId,
      tenant_id: tenantId,
      before: oldData,
      after: newData,
    });
  }
}
```

## Frontend Integration

### React Example
```javascript
const fetchActivityLogs = async (tenantId, token) => {
  const response = await fetch(
    'http://localhost:7001/activity-log/tenant?limit=50&offset=0',
    {
      headers: {
        'Authorization': `Bearer ${token}`,
      }
    }
  );
  return await response.json();
};

// Usage in component
useEffect(() => {
  const logs = await fetchActivityLogs(tenant, accessToken);
  setActivityLogs(logs.data);
}, [tenant, accessToken]);
```

## Performance Considerations

- Indexed on: tenant_id, userid, action, date_create
- Max 500 records per request to prevent large payloads
- Pagination required for large datasets
- Consider archiving old logs after retention period

## Security Notes

- Activity logs are only accessible with JWT authentication
- Logs capture user actions but not sensitive data like passwords
- Tenant isolation enforced (users can only see logs from their tenant)
- User can see their own logs or tenant admin can see all tenant logs
