# 🧪 Sanctum Authentication - Quick Test Guide

## ✅ **Test Default User (Orchid Admin)**

The Orchid admin user is already set up and can be used for testing.

### **Default Credentials:**
- **Email:** `admin@admin.com`
- **Password:** `password`

---

## 🚀 **Quick Test Commands**

### **1. Test Login:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.com","password":"password"}' | jq
```

**Expected Response:**
```json
{
  "token": "1|...",
  "user": {
    "id": 1,
    "name": "admin",
    "email": "admin@admin.com"
  }
}
```

---

### **2. Test Protected Endpoint (with token):**
```bash
# Copy the token from step 1
TOKEN="PASTE_TOKEN_HERE"

curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
```

**Expected:** List of devices (200 OK)

---

### **3. Test Protected Endpoint (without token):**
```bash
curl http://localhost:8000/api/v1/devices \
  -H "Accept: application/json" | jq
```

**Expected Response:**
```json
{
  "message": "Unauthenticated."
}
```

---

### **4. Test Logout:**
```bash
TOKEN="PASTE_TOKEN_HERE"

curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
```

**Expected Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

### **5. Test Revoked Token:**
```bash
# Use the same token from step 4 (after logout)
curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
```

**Expected Response:**
```json
{
  "message": "Unauthenticated."
}
```

---

## 🌐 **Test in Frontend**

### **1. Start Backend:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan serve
```

### **2. Start Frontend:**
```bash
cd /Users/ridafakherlden/www/gps-track
npm start
```

### **3. Open Browser:**
```
http://localhost:3000
```

**Expected:** Redirects to `/login`

### **4. Login:**
- **Email:** `admin@admin.com`
- **Password:** `password`
- Click "Sign In"

**Expected:** Redirects to `/dashboard`

### **5. Check User Menu:**
- Click on user name in header (top right)
- Should see user menu with email
- Click "Logout"

**Expected:** Redirects to `/login`

---

## 🎯 **All Endpoints**

| Endpoint | Method | Auth Required | Description |
|----------|--------|---------------|-------------|
| `/api/v1/health` | GET | ❌ No | Health check |
| `/api/v1/login` | POST | ❌ No | Login |
| `/api/v1/register` | POST | ❌ No | Register |
| `/api/v1/logout` | POST | ✅ Yes | Logout |
| `/api/v1/user` | GET | ✅ Yes | Get user info |
| `/api/v1/devices` | GET | ✅ Yes | List devices |
| `/api/v1/devices` | POST | ✅ Yes | Create device |
| `/api/v1/positions/last` | GET | ✅ Yes | Last positions |
| `/api/v1/geofences` | GET | ✅ Yes | List geofences |
| `/api/v1/alerts` | GET | ✅ Yes | List alerts |
| `/api/v1/reports/generate` | POST | ✅ Yes | Generate report |
| *...and more* | * | ✅ Yes | All other endpoints |

---

## 📝 **Create New Test User**

```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan tinker --execute="\\App\\Models\\User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => \\Hash::make('password123')]);"
```

Then test with:
- **Email:** `test@test.com`
- **Password:** `password123`

---

## ✅ **Expected Results Summary**

| Test | Expected Result |
|------|-----------------|
| Login with valid credentials | ✅ Returns token + user |
| Login with invalid credentials | ❌ 401 "Invalid credentials" |
| Access endpoint with token | ✅ Returns data |
| Access endpoint without token | ❌ 401 "Unauthenticated." |
| Logout | ✅ "Logged out successfully" |
| Use revoked token | ❌ 401 "Unauthenticated." |
| Frontend login page | ✅ Shows form |
| Frontend auto-redirect | ✅ Goes to dashboard |
| Frontend logout | ✅ Back to login |

---

**Status:** ✅ All Tests Passing  
**Last Tested:** October 22, 2025

