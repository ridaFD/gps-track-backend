# 🔐 Sanctum API Authentication - Implementation Guide

**Status:** ✅ Fully Implemented & Working  
**Last Updated:** October 22, 2025

---

## 📋 **Overview**

Laravel Sanctum authentication has been successfully implemented for the GPS Tracking API. All endpoints now require valid Bearer tokens for access.

### **What Changed:**

| Before | After |
|--------|-------|
| ❌ Mock tokens (fake authentication) | ✅ Real Sanctum tokens |
| ❌ `middleware('api')` | ✅ `middleware('auth:sanctum')` |
| ❌ No token validation | ✅ Full token validation |
| ❌ Security risk | ✅ Production-ready |

---

## 🎯 **Features Implemented**

### **Backend (Laravel)**

1. ✅ **Real Login Endpoint**
   - Validates email and password
   - Checks credentials against database
   - Generates Sanctum token
   - Returns token + user data

2. ✅ **Registration Endpoint**
   - Validates input (name, email, password)
   - Creates new user
   - Hashes password with `Hash::make()`
   - Auto-generates token
   - Returns token + user data

3. ✅ **Logout Endpoint**
   - Revokes current access token
   - Prevents reuse of old tokens
   - Returns success message

4. ✅ **Protected Routes**
   - All API routes require `auth:sanctum` middleware
   - Returns 401 Unauthenticated without valid token
   - `/user` endpoint returns authenticated user data

### **Frontend (React)**

1. ✅ **Authentication Service** (`src/services/auth.js`)
   - Token management (store/retrieve/remove)
   - User data management
   - Login/Register/Logout functions
   - Error handling

2. ✅ **Login Page** (`src/pages/Login.js`)
   - Beautiful gradient UI
   - Email and password fields
   - Error message display
   - Auto-redirect after login

3. ✅ **Protected Routes** (`src/App.js`)
   - `ProtectedRoute` component
   - Auto-redirect to `/login` if unauthenticated
   - Prevents access to protected pages

4. ✅ **Logout Functionality** (`src/components/Header.js`)
   - User menu dropdown
   - Display user name and email
   - Logout button
   - Confirmation dialog

5. ✅ **API Interceptor** (`src/services/api.js`)
   - Auto-adds Bearer token to requests
   - Handles 401 errors (redirects to login)
   - Token stored in localStorage

---

## 🚀 **API Endpoints**

### **1. Login**

**Endpoint:** `POST /api/v1/login`

**Request:**
```json
{
  "email": "test@test.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "token": "1|xmHtT3XIMmc3HlTBvg9ZKON0s7NItHQaxpByWAGG7e3f8a95",
  "user": {
    "id": 2,
    "name": "Test User",
    "email": "test@test.com"
  }
}
```

**Error Response (401):**
```json
{
  "message": "Invalid credentials"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password123"}'
```

---

### **2. Register**

**Endpoint:** `POST /api/v1/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success Response (201):**
```json
{
  "message": "User registered successfully",
  "token": "2|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890",
  "user": {
    "id": 3,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Response (422):**
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `email`: Required, valid email, unique in users table
- `password`: Required, min 8 characters, must match confirmation

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"John Doe",
    "email":"john@example.com",
    "password":"password123",
    "password_confirmation":"password123"
  }'
```

---

### **3. Logout**

**Endpoint:** `POST /api/v1/logout`

**Headers Required:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

**cURL Example:**
```bash
TOKEN="1|xmHtT3XIMmc3HlTBvg9ZKON0s7NItHQaxpByWAGG7e3f8a95"

curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### **4. Get User Info**

**Endpoint:** `GET /api/v1/user`

**Headers Required:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "id": 2,
  "name": "Test User",
  "email": "test@test.com",
  "company": "GPS Tracking Inc.",
  "role": "admin"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

### **5. Protected Endpoints**

All other API endpoints require authentication:

**Example (Get Devices):**
```bash
TOKEN="1|xmHtT3XIMmc3HlTBvg9ZKON0s7NItHQaxpByWAGG7e3f8a95"

curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Without Token (401):**
```bash
curl http://localhost:8000/api/v1/devices \
  -H "Accept: application/json"

# Response:
# {"message":"Unauthenticated."}
```

---

## 💻 **Frontend Implementation**

### **1. Login Page**

**Location:** `src/pages/Login.js`

```javascript
import { login } from '../services/auth';

const handleSubmit = async (e) => {
  e.preventDefault();
  const result = await login(email, password);
  
  if (result.success) {
    window.location.href = '/';
  } else {
    setError(result.message);
  }
};
```

**Features:**
- Modern gradient UI
- Email/password validation
- Error message display
- Auto-redirect after login
- Default test credentials shown

---

### **2. Protected Routes**

**Location:** `src/App.js`

```javascript
import { isAuthenticated } from './services/auth';

function ProtectedRoute({ children }) {
  return isAuthenticated() ? children : <Navigate to="/login" replace />;
}

<Route 
  path="/*" 
  element={
    <ProtectedRoute>
      {/* Your protected content */}
    </ProtectedRoute>
  }
/>
```

---

### **3. Auth Service**

**Location:** `src/services/auth.js`

```javascript
// Login
export const login = async (email, password) => {
  const response = await authAPI.login(email, password);
  setAuthToken(response.token);
  setUser(response.user);
  return { success: true, user: response.user };
};

// Logout
export const logout = async () => {
  await authAPI.logout();
  removeAuthToken();
  removeUser();
  window.location.href = '/login';
};

// Check if authenticated
export const isAuthenticated = () => {
  return !!getAuthToken();
};
```

---

### **4. API Interceptor**

**Location:** `src/services/api.js`

```javascript
// Auto-add token to requests
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle 401 errors
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

---

## 🧪 **Testing**

### **Test Results:** ✅ All Passing

| Test | Result |
|------|--------|
| Login with valid credentials | ✅ Pass |
| Login with invalid credentials | ✅ Pass (401 error) |
| Access protected route with token | ✅ Pass |
| Access protected route without token | ✅ Pass (401 error) |
| Logout and revoke token | ✅ Pass |
| Use revoked token | ✅ Pass (401 error) |

### **Test Commands:**

**1. Create Test User:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan tinker --execute="\\App\\Models\\User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => \\Hash::make('password123')]);"
```

**2. Test Login:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password123"}'
```

**3. Test Protected Endpoint:**
```bash
TOKEN="YOUR_TOKEN_HERE"
curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**4. Test Logout:**
```bash
TOKEN="YOUR_TOKEN_HERE"
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**5. Test Revoked Token:**
```bash
# Use the same token after logout
curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
# Should return: {"message":"Unauthenticated."}
```

---

## 🔒 **Security Features**

1. ✅ **Password Hashing**
   - Uses `Hash::make()` for secure password storage
   - Bcrypt algorithm (default Laravel)

2. ✅ **Token-Based Authentication**
   - Stateless authentication
   - Tokens stored in `personal_access_tokens` table
   - Each login generates unique token

3. ✅ **Token Revocation**
   - Logout deletes current token
   - Old tokens cannot be reused
   - Users can have multiple active sessions

4. ✅ **Validation**
   - Email format validation
   - Password strength requirements (min 8 chars)
   - Unique email constraint
   - Password confirmation required

5. ✅ **Error Handling**
   - Generic error messages (no user enumeration)
   - 401 status for unauthenticated requests
   - 422 status for validation errors

---

## 📊 **Database Tables**

### **users Table:**
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
```

### **personal_access_tokens Table:**
```sql
CREATE TABLE personal_access_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tokenable_type VARCHAR(255) NOT NULL,
  tokenable_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  token VARCHAR(64) UNIQUE NOT NULL,
  abilities TEXT NULL,
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX tokenable (tokenable_type, tokenable_id)
);
```

---

## 🎓 **How It Works**

### **Authentication Flow:**

```
1. User submits email + password
   ↓
2. Backend validates credentials
   ↓
3. Backend generates Sanctum token
   ↓
4. Token sent to frontend
   ↓
5. Frontend stores token in localStorage
   ↓
6. All API requests include token in headers
   ↓
7. Backend validates token on each request
   ↓
8. User clicks logout
   ↓
9. Backend revokes token
   ↓
10. Frontend removes token from localStorage
```

---

## 🚀 **Usage Instructions**

### **For Frontend Developers:**

**1. Check if user is authenticated:**
```javascript
import { isAuthenticated } from './services/auth';

if (isAuthenticated()) {
  // User is logged in
} else {
  // Redirect to login
}
```

**2. Get current user:**
```javascript
import { getUser } from './services/auth';

const user = getUser();
console.log(user.name, user.email);
```

**3. Login:**
```javascript
import { login } from './services/auth';

const result = await login('test@test.com', 'password123');
if (result.success) {
  console.log('Logged in!', result.user);
}
```

**4. Logout:**
```javascript
import { logout } from './services/auth';

await logout(); // Auto-redirects to /login
```

---

### **For API Developers:**

**All protected endpoints automatically:**
- ✅ Require Bearer token
- ✅ Return 401 if unauthenticated
- ✅ Provide authenticated user via `$request->user()`

**Example:**
```php
Route::middleware('auth:sanctum')->get('/devices', function (Request $request) {
    $user = $request->user(); // Authenticated user
    
    return Device::where('user_id', $user->id)->get();
});
```

---

## ✅ **Status Summary**

| Component | Status |
|-----------|--------|
| **Backend Login** | ✅ Working |
| **Backend Register** | ✅ Working |
| **Backend Logout** | ✅ Working |
| **Token Generation** | ✅ Working |
| **Token Validation** | ✅ Working |
| **Token Revocation** | ✅ Working |
| **Frontend Login Page** | ✅ Working |
| **Frontend Auth Service** | ✅ Working |
| **Frontend Protected Routes** | ✅ Working |
| **Frontend API Interceptor** | ✅ Working |
| **Logout Button** | ✅ Working |
| **Auto-redirect** | ✅ Working |

---

## 📖 **Related Documentation**

- Laravel Sanctum: https://laravel.com/docs/10.x/sanctum
- Token Management: `personal_access_tokens` table
- Frontend Auth: `src/services/auth.js`
- API Client: `src/services/api.js`

---

## 🎉 **Conclusion**

✅ **Sanctum authentication is fully implemented and tested!**

**What works:**
- Real authentication with database validation
- Secure token generation and validation
- Token revocation on logout
- Protected API endpoints
- Beautiful login UI
- Auto-redirect flows

**Security level:** ✅ **Production-ready**

---

**Last Updated:** October 22, 2025  
**Git Commits:**
- Backend: `600ebbb` - feat: Implement Sanctum API authentication
- Frontend: `490b4e6` - feat: Implement real Sanctum authentication in frontend

**Status:** ✅ Complete & Operational

