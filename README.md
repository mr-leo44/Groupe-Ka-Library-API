# 📚 Groupe Ka Library API

A comprehensive RESTful API for managing a digital library with books, user authentication, role-based access control, and audit logging.

## ✨ Features

- ✅ **Authentication**: Email/password + Social login (Google, Apple)
- ✅ **Email Verification**: Mandatory email confirmation
- ✅ **Strong Password Policy**: Mixed case, numbers, symbols, breach detection
- ✅ **Token-based Auth**: Laravel Sanctum with configurable expiration
- ✅ **Role-Based Access**: Admin, Manager, Member roles via Spatie
- ✅ **Session Management**: View and revoke active tokens per device
- ✅ **Audit Logging**: Track all model changes (Owen-It Auditing)
- ✅ **Activity Logging**: Track user actions (login, logout, etc.)
- ✅ **Suspicious Login Detection**: Alert on new IP/device
- ✅ **Password Reset**: Secure email-based password recovery
- ✅ **Rate Limiting**: Brute-force protection on authentication
- ✅ **Soft Deletes**: Recover deleted accounts
- ✅ **API Documentation**: Auto-generated with Laravel Scramble

## 🚀 Quick Start

### Installation

```bash
# Clone repository
git clone https://github.com/mr-leo44/Groupe-Ka-Library-API.git
cd Groupe-Ka-Library-API

# Manual installation
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Start Server

```bash
# Start API server
php artisan serve

# Start queue worker (separate terminal)
php artisan queue:work

# API available at: http://localhost:8000
# Documentation at: http://localhost:8000/docs/api
```

### Test API

```bash
# use PHPUnit
php artisan test
```

## 📋 Test Credentials

After running `php artisan db:seed`, you can use:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@groupeka.com | Admin@123! |
| Manager | manager@groupeka.com | Manager@123! |
| Member | john.doe@example.com | Member@123! |

## 📚 API Documentation

Interactive API documentation is available at:

```
http://localhost:8000/docs/api
```

Powered by **Laravel Scramble** - automatically generated from your routes and controllers.

### Key Endpoints

#### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/social` - Social login (Google/Apple)
- `POST /api/auth/logout` - Logout current device
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password

#### User Profile
- `GET /api/user/profile` - Get profile
- `PUT /api/user/profile` - Update profile
- `POST /api/user/change-password` - Change password
- `GET /api/user/sessions` - List active sessions
- `DELETE /api/user/sessions/{id}` - Revoke session

#### Admin
- `GET /api/admin/users` - List all users
- `PUT /api/admin/users/{id}/role` - Update user role
- `DELETE /api/admin/users/{id}` - Delete user
- `GET /api/admin/audits` - View audit logs
- `GET /api/admin/activities` - View activity logs
- `GET /api/admin/statistics` - Dashboard statistics

## 🏗️ Architecture

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── PasswordController.php
│   │   ├── EmailVerificationController.php
│   │   └── AuditController.php
│   ├── Requests/
│   │   ├── RegisterRequest.php
│   │   ├── LoginRequest.php
│   │   └── SocialLoginRequest.php
│   └── Middleware/
│       ├── EnsureEmailIsVerified.php
│       └── DetectSuspiciousLogin.php
├── Models/
│   ├── User.php (Auditable, MustVerifyEmail)
│   ├── Book.php (Auditable)
│   └── ...
├── Services/Auth/
│   ├── AuthService.php
│   ├── SocialAuthService.php
│   └── TokenService.php
├── Repositories/
│   └── AuthRepository.php
└── Contracts/
    └── AuthRepositoryInterface.php
```

## 🔐 Security Features

### Password Requirements
- Minimum 8 characters
- Mixed case (uppercase + lowercase)
- At least one number
- At least one special symbol
- Not in breached password database

### Rate Limiting
- Login: 5 attempts per minute
- Registration: 5 attempts per minute
- Password reset: 3 attempts per minute
- Email verification resend: 3 attempts per minute

### Token Security
- Configurable expiration (default: 60 minutes)
- Revoked on password change
- Per-device management
- Bearer token authentication

### Audit & Monitoring
- All model changes tracked
- User actions logged (login, logout, etc.)
- New IP/device detection
- Failed login attempt logging

## 🛠️ Configuration

### Environment Variables

```env
# Token expiration (minutes)
SANCTUM_EXPIRATION=60

# Email configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525

# Social Auth
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-secret
APPLE_CLIENT_ID=your-client-id
APPLE_CLIENT_SECRET=your-secret

# Enable auditing
AUDITING_ENABLED=true

# Queue for async operations
QUEUE_CONNECTION=database
```

### Enable New Device Notifications

1. Set in `.env`:
```env
ENABLE_NEW_DEVICE_NOTIFICATIONS=true
```

2. Uncomment in `DetectSuspiciousLogin.php`:
```php
$user->notify(new NewDeviceLoginNotification($currentIp, $userAgent));
```

3. Configure mail settings

## 📦 Dependencies

- **laravel/sanctum** - API authentication
- **spatie/laravel-permission** - Role & permission management
- **spatie/laravel-activitylog** - Activity logging
- **owen-it/laravel-auditing** - Model auditing
- **laravel/socialite** - Social authentication