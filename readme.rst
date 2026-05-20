# Reset Password Feature Documentation

## Overview
Reset Password adalah fitur keamanan yang memungkinkan pengguna untuk mereset password mereka dengan validasi NIK (Nomor Induk Karyawan) dan email, serta verifikasi reCAPTCHA.

## Endpoint Details

### URL
```
POST /auth/resetPassword
```

### Base URL
- **Development (localhost):** `http://localhost:7001`
- **Production (ngrok tunnel):** `https://subacetabular-jodee-literally.ngrok-free.dev`

## Request Body

### Required Fields
```json
{
  "username": "string (required)",
  "nik": "string (required)",
  "email": "string (required)",
  "recaptchaToken": "string (required)"
}
```

### Field Descriptions
| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `username` | string | Username karyawan yang akan mereset password | `agnes` |
| `nik` | string | Nomor Induk Karyawan (NIK) sesuai data di user_profile | `1234567890123456` |
| `email` | string | Email karyawan (corporate atau non-corporate) | `agnes@company.com` atau `agnes@gmail.com` |
| `recaptchaToken` | string | reCAPTCHA token untuk verifikasi | `test-token-dev` (development) |

## Validation Rules

### 1. reCAPTCHA Verification
- Token harus valid dan terverifikasi
- Dalam development mode, token `test-token-dev` diterima untuk testing
- Production menggunakan Google reCAPTCHA v3

### 2. User Existence
- Username harus terdaftar di dalam database
- User profile harus ada di tabel `user_profile`

### 3. NIK Validation
- NIK harus cocok dengan `user_profile.nik`
- NIK tidak boleh kosong
- Error: "NIK tidak sesuai" (HTTP 400)

### 4. Email Validation
- Email harus cocok dengan salah satu:
  - `user_profile.email_corporate` (email perusahaan)
  - `user_profile.email_non_corporate` (email non-perusahaan)
- Error: "Email tidak sesuai" (HTTP 400)

## Response Examples

### Success Response
```json
{
  "message": "Password berhasil direset",
  "success": true
}
```

**HTTP Status:** `200` atau `201`

### Error Responses

#### Invalid NIK
```json
{
  "message": "NIK tidak sesuai",
  "error": "Bad Request",
  "statusCode": 400
}
```

#### Invalid Email
```json
{
  "message": "Email tidak sesuai",
  "error": "Bad Request",
  "statusCode": 400
}
```

#### User Not Found
```json
{
  "message": "User not found",
  "error": "Bad Request",
  "statusCode": 400
}
```

#### User Profile Not Found
```json
{
  "message": "User profile not found",
  "error": "Bad Request",
  "statusCode": 400
}
```

#### reCAPTCHA Verification Failed
```json
{
  "message": "reCAPTCHA verification failed. Please try again.",
  "error": "Bad Request",
  "statusCode": 400
}
```

## Password Reset Behavior

### Default Password Strategy
The reset password feature uses a **tenant-aware password selection system**:

1. **Tenant-Specific Passwords** (if configured):
   - Each tenant can have a specific default password
   - Set via environment variables: `TENANT_PASSWORD_<TENANT_CODE>`
   - Example: `TENANT_PASSWORD_USER_MANAGEMENT=I7lBLi'7x7s`

2. **Fallback to Global Default** (if no tenant password):
   - Uses `PASSWORD_DEFAULT` environment variable
   - Applied when user has no tenant or tenant has no specific password

### Tenant-Specific Password Configuration

The system supports different default passwords for different tenancies:

**Environment Variables:**
```env
# User Management Tenancy
TENANT_PASSWORD_USER_MANAGEMENT="I7lBLi'7x7s"

# User Omnix Tenancy  
TENANT_PASSWORD_USER_OMNIX="$Hm$U16a3Z"

# Global fallback
PASSWORD_DEFAULT=abstract123.
```

**How It Works:**
1. When reset password is called, the system looks up the user's `tenant_id`
2. Queries the `tenant` table using the `tenant_id`
3. Gets the `tenant_code` (e.g., "USER_MANAGEMENT", "USER_OMNIX")
4. Looks for `TENANT_PASSWORD_<TENANT_CODE>` environment variable
5. If found, uses that password; otherwise uses `PASSWORD_DEFAULT`

### Available Tenants
Currently configured tenants in the system:

| Tenant Code | Tenant Name | Tenant ID | Default Password |
|-------------|-------------|-----------|------------------|
| USER_MANAGEMENT | User Management | tenant_user_management | I7lBLi'7x7s |
| USER_OMNIX | User Omnix | tenant_user_omnix | $Hm$U16a3Z |
| (others) | Custom | - | PASSWORD_DEFAULT |

### Password Hash Update
- Password lashed using bcrypt (salt rounds: 8)
- Password stored in `user` table, `password` column

## Testing Guide

### Prerequisites
- Application running di `http://localhost:7001`
- Test user tersedia di database dengan profile lengkap
- Environment variable `ENVIRONMENT=development` untuk testing

### Test User Data
```
Username: agnes
NIK: 1234567890123456
Email (Corporate): agnes@company.com
Email (Non-Corporate): agnes@gmail.com
```

### Using Node.js

```javascript
const http = require('http');

const data = JSON.stringify({
  username: 'agnes',
  nik: '1234567890123456',
  email: 'agnes@company.com',
  recaptchaToken: 'test-token-dev'
});

const options = {
  hostname: 'localhost',
  port: 7001,
  path: '/auth/resetPassword',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': data.length
  }
};

const req = http.request(options, (res) => {
  let body = '';
  res.on('data', (chunk) => body += chunk);
  res.on('end', () => {
    console.log('Status:', res.statusCode);
    console.log('Response:', body);
  });
});

req.write(data);
req.end();
```

### Using Postman

1. **Create New Request**
   - Method: `POST`
   - URL: `http://localhost:7001/auth/resetPassword`

2. **Headers**
   - `Content-Type: application/json`

3. **Body (JSON)**
   ```json
   {
     "username": "agnes",
     "nik": "1234567890123456",
     "email": "agnes@company.com",
     "recaptchaToken": "test-token-dev"
   }
   ```

4. **Send Request**
   - Expected Response: `200` atau `201` dengan `success: true`

### Test Scenarios

#### Test 1: Valid Credentials
```bash
POST /auth/resetPassword
{
  "username": "agnes",
  "nik": "1234567890123456",
  "email": "agnes@company.com",
  "recaptchaToken": "test-token-dev"
}
```
**Expected:** ✅ HTTP 200, Password reset successfully

#### Test 2: Invalid NIK
```bash
POST /auth/resetPassword
{
  "username": "agnes",
  "nik": "wrong-nik",
  "email": "agnes@company.com",
  "recaptchaToken": "test-token-dev"
}
```
**Expected:** ❌ HTTP 400, "NIK tidak sesuai"

#### Test 3: Invalid Email
```bash
POST /auth/resetPassword
{
  "username": "agnes",
  "nik": "1234567890123456",
  "email": "wrong@email.com",
  "recaptchaToken": "test-token-dev"
}
```
**Expected:** ❌ HTTP 400, "Email tidak sesuai"

#### Test 4: Non-existent User
```bash
POST /auth/resetPassword
{
  "username": "nonexistent",
  "nik": "1234567890123456",
  "email": "agnes@company.com",
  "recaptchaToken": "test-token-dev"
}
```
**Expected:** ❌ HTTP 400, "User not found"

#### Test 5: Using Non-Corporate Email
```bash
POST /auth/resetPassword
{
  "username": "agnes",
  "nik": "1234567890123456",
  "email": "agnes@gmail.com",
  "recaptchaToken": "test-token-dev"
}
```
**Expected:** ✅ HTTP 200, Password reset successfully

## Database Tables & Fields

### user_profile (Required Fields)
```sql
SELECT id, user_id, nik, email_corporate, email_non_corporate FROM user_profile WHERE user_id = ?;
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | int | Yes | Primary key |
| `user_id` | varchar | Yes | User ID reference |
| `nik` | varchar | Yes | Nomor Induk Karyawan |
| `email_corporate` | varchar | No | Email perusahaan |
| `email_non_corporate` | varchar | No | Email personal/non-perusahaan |

### user (Updated Fields)
```sql
SELECT id, username, password FROM user WHERE username = ?;
```

| Field | Type | Changes | Description |
|-------|------|---------|-------------|
| `password` | varchar | ✏️ Updated | Bcrypt hash password baru |

## Environment Configuration

### Required Environment Variables
```env
# Global default password (fallback if no tenant-specific password)
PASSWORD_DEFAULT=your_default_password

# Tenant-Specific Default Passwords for Reset Password Feature
TENANT_PASSWORD_USER_MANAGEMENT="I7lBLi'7x7s"
TENANT_PASSWORD_USER_OMNIX="$Hm$U16a3Z"

# reCAPTCHA Configuration
RECAPTCHA_SECRET_KEY=your_google_recaptcha_secret_key
RECAPTCHA_SCORE_THRESHOLD=0.5

# Application Mode
ENVIRONMENT=development
RECAPTCHA_DEV_TOKEN=test-token-dev
```

### Tenant Password Naming Convention
Environment variable format: `TENANT_PASSWORD_<TENANT_CODE>`

- Tenant code is derived from the `tenant` table's `tenant_code` column
- Convert tenant code to UPPERCASE: `user_management` → `USER_MANAGEMENT`
- Replace spaces/special chars with underscores if needed
- Example: `TENANT_PASSWORD_USER_MANAGEMENT`

### Configuration Examples

**Example 1: New Tenant**
If you create a new tenant with code `CUSTOM_TENANT`, add:
```env
TENANT_PASSWORD_CUSTOM_TENANT="YourCustomPassword123!"
```

**Example 2: Without Tenant-Specific Password**
If a user has no tenant assigned, the system uses:
```env
PASSWORD_DEFAULT=your_default_password
```

## Security Considerations

### ✅ Implemented Security Features
1. **reCAPTCHA Verification** - Mencegah automated attacks
2. **NIK Validation** - Memastikan identitas karyawan
3. **Email Validation** - Konfirmasi email yang terdaftar
4. **Bcrypt Hashing** - Password di-hash dengan salt rounds 8
5. **Error Messages** - Tidak mengungkap informasi sensitif
6. **HTTP Status Codes** - Proper error responses (400 untuk validation)

### ⚠️ Security Notes
- Password default harus kuat dan diubah oleh pengguna saat login pertama
- reCAPTCHA token harus valid dari Google
- jangan expose sensitive data di error messages
- Gunakan HTTPS di production
- Implement rate limiting untuk mencegah brute force

## Implementation Details

### Code Location
- **Controller:** `src/auth/auth.controller.ts`
- **Service:** `src/auth/auth.service.ts`
- **DTO:** `src/auth/dto/auth.dto.ts`
- **Module:** `src/auth/auth.module.ts`

### Dependencies
- `@nestjs/common` - NestJS framework
- `@nestjs/typeorm` - TypeORM integration
- `bcryptjs` - Password hashing
- `class-validator` - DTO validation
- `axios` - HTTP requests (untuk reCAPTCHA)

## Troubleshooting

### Error: "Unexpected token u in JSON at position 1"
**Cause:** curl command di Windows PowerShell mengalami issue dengan quote escaping
**Solution:** Gunakan Node.js atau Postman untuk testing

### Error: "User not found"
**Solution:** Pastikan username terdaftar di database `user` table

### Error: "User profile not found"
**Solution:** Pastikan user memiliki record di `user_profile` table

### Error: "NIK tidak sesuai"
**Solution:** Pastikan NIK di request cocok dengan `user_profile.nik`

### Error: "Email tidak sesuai"
**Solution:** Gunakan email yang cocok dengan `email_corporate` atau `email_non_corporate`

### Error: "reCAPTCHA verification failed"
**Solution:** 
- Pastikan token valid
- Di development, gunakan `test-token-dev`
- Di production, pastikan `RECAPTCHA_SECRET_KEY` sudah dikonfigurasi

## API Versioning & Deprecation

Current Version: **v1.0**
- Status: ✅ Stable & Production Ready
- Last Updated: May 20, 2026
- Breaking Changes: None

## Support & Maintenance

### Test Results
```
✅ Valid credentials - Password reset successfully
✅ Invalid NIK validation - Proper error response
✅ Invalid email validation - Proper error response
✅ User existence check - Proper error response
✅ reCAPTCHA verification - Development token accepted
✅ External access (ngrok) - Tunnel accessible
✅ Database persistence - Password hash updated correctly
```

### Contact & Issues
For bug reports or feature requests, please create an issue in the project repository.

---

**Documentation Version:** 1.0  
**Last Updated:** May 20, 2026  
**Status:** ✅ Production Ready
