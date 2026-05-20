# Tenant Users — Frontend Integration (OMNIX)

This document is intended for frontend developers who will consume the Tenant Users API (List, Detail, Edit flows) for the OMNIX tenant management UI.

Keep this file in the frontend repository as the single source of truth for API contract and UI mapping.

---

## Base
- Base URL: `http://localhost:7001`
- All endpoints are prefixed with `/tenant/:tenant_code`
- Authentication: send `Authorization: Bearer {JWT}` header on every request

---

## 1. List Users (primary view)

Endpoint
- `GET /tenant/:tenant_code/users`

Query parameters
- `skip` (number, default: `0`) — pagination offset
- `take` (number, default: `10`) — pagination limit
- `search` (string, optional) — search by `username`, `email`, or `fullname` (case-insensitive, substring)
- `is_active` (boolean, optional) — filter active/inactive

Behavior
- Returns paginated results, sorted by `create_at` DESC
- Default `take` = 10 (frontend can increase to show more rows)

Example request (axios)

```js
import axios from 'axios';

async function fetchUsers(tenantCode, token, { skip = 0, take = 10, search, is_active } = {}) {
  const params = { skip, take };
  if (search) params.search = search;
  if (typeof is_active !== 'undefined') params.is_active = is_active;

  const { data } = await axios.get(`http://localhost:7001/tenant/${tenantCode}/users`, {
    headers: { Authorization: `Bearer ${token}` },
    params,
  });

  return data; // { data: TenantUserDto[], total, skip, take }
}
```

Example request (fetch)

```js
async function fetchUsersFetch(tenantCode, token, query = {}) {
  const q = new URLSearchParams(query).toString();
  const res = await fetch(`http://localhost:7001/tenant/${tenantCode}/users?${q}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}
```

Response schema (JSON)

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

TypeScript interfaces (copy into frontend)

```ts
export interface TenantUserDto {
  userid: number;
  email: string;
  fullname: string;
  nickname?: string;
  is_active: boolean;
  expired_at?: string | null;
  fail_login: number;
  username: string;
  role: string; // '1'|'2'|'3'|'4'
}

export interface TenantUserListResponse {
  data: TenantUserDto[];
  total: number;
  skip: number;
  take: number;
}
```

UI mapping — table columns
- No (index)
- User ID (`userid`)
- Email (`email`) — show tooltip with full value if truncated
- Fullname (`fullname`)
- Nickname (`nickname`)
- Status (`is_active`) — show badge: Active / Inactive
- Expired At (`expired_at`) — format date
- Fail Login (`fail_login`) — numeric badge
- Actions — buttons: `Edit`, `Reset PW`, `Unlock`, `Reset 2FA`

Interactions
- Clicking `Edit` opens modal. Pre-fill modal using `GET /tenant/:tenant_code/users/:userId`.
- `Reset PW`, `Unlock`, `Reset 2FA` call their respective endpoints (APPROVER role required).
- After successful update (Edit, Reset PW, Unlock), refresh the list row or re-fetch current page.

---

## 2. User Detail (for Edit modal pre-fill)

Endpoint
- `GET /tenant/:tenant_code/users/:userId`

Response
- Returns a single `TenantUserDto` object (see interface above)

Example (axios)

```js
const { data } = await axios.get(`http://localhost:7001/tenant/${tenantCode}/users/${userId}`, { headers: { Authorization: `Bearer ${token}` } });
// data is TenantUserDto
```

---

## 3. Update User (Edit action)

Endpoint
- `PUT /tenant/:tenant_code/users/:userId`

Permissions
- Requires JWT and `APPROVER` role in server-side guard. Frontend should show/hide UI actions according to the logged-in user's role.

Allowed request fields (partial allowed — only send fields that changed):
- `email` (string) — corporate email
- `fullname` (string)
- `nickname` (string) — will update `user.username`; must be unique per tenant

Example request (axios)

```js
const payload = { email: 'john.new@company.com', fullname: 'John New', nickname: 'john.new' };
const { data } = await axios.put(`http://localhost:7001/tenant/${tenantCode}/users/${userId}`, payload, { headers: { Authorization: `Bearer ${token}` } });
// data: { message: 'User berhasil diupdate', data: TenantUserDto }
```

Frontend handling notes
- If API returns 400 with a `message` "Nickname/username sudah digunakan", show inline error under nickname field.
- On success: close modal, update row and show toast.
- On 401: redirect to login
- On 403: show permission error (insufficient role)
- On 404: show "user tidak ditemukan"

---

## 4. Actions (Reset Password, Unlock, Reset 2FA)

These actions require `APPROVER` role.

- Reset Password: `POST /tenant/:tenant_code/users/reset-password` with `{ "userId": <id> }`. Response includes generated password in non-production; in production the password should be emailed.
- Unlock: `POST /tenant/:tenant_code/users/unlock` with `{ "userId": <id>, "reason": "..." }`.
- Reset 2FA: `POST /tenant/:tenant_code/users/reset-2fa` with `{ "userId": <id> }`.

All return 200 with a message and some payload. After any action, refresh the user row.

---

## 5. Fail Auth Logs (optional integration)

Endpoint
- `GET /tenant/:tenant_code/fail-auth-logs` (requires APPROVER)

Use for security/monitoring pages. Pagination defaults to 50 items.

---

## 6. Errors & Status Codes

- `200 OK` — success
- `400 Bad Request` — validation error (body contains `message`)
- `401 Unauthorized` — invalid or expired token
- `403 Forbidden` — insufficient role
- `404 Not Found` — tenant or user not found
- `500 Internal Server Error` — unexpected

Frontend should parse JSON error body where available and present friendly messages.

---

## 7. UI/UX Recommendations

- Debounce search input (300ms) before calling API
- Use server-side pagination (do not fetch all records)
- Show loading state for table and buttons
- Disable action buttons while request is in progress
- Confirm destructive actions (e.g., Reset Password) with a modal
- Mask long emails in table with tooltip for full value

---

## 8. Example React Hook (simplified)

```tsx
import { useState, useEffect } from 'react';
import axios from 'axios';

export function useTenantUsers(tenantCode, token) {
  const [data, setData] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(0);
  const [pageSize, setPageSize] = useState(10);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    axios.get(`http://localhost:7001/tenant/${tenantCode}/users`, {
      headers: { Authorization: `Bearer ${token}` },
      params: { skip: page * pageSize, take: pageSize, search }
    })
    .then(res => {
      if (!mounted) return;
      setData(res.data.data);
      setTotal(res.data.total);
    })
    .finally(() => mounted && setLoading(false));

    return () => { mounted = false };
  }, [tenantCode, token, page, pageSize, search]);

  return { data, total, page, pageSize, setPage, setPageSize, setSearch, loading };
}
```

---

## 9. Notes for Backend/Frontend Alignment

- If you need additional fields in the list response (e.g., `last_login`, `department`), ask backend to add them to `TenantUserDto` and to the SQL query.
- Keep error message keys consistent: use `message` for human text and `code` for machine-readable errors if needed.

---

Made for the frontend team — ask if you want a React modal example (component + form) or full Storybook story.
