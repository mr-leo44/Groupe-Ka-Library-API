# 📦 Installation Guide - Groupe Ka Library API

## Prerequisites

- PHP >= 8.2
- Composer
- MySQL or PostgreSQL
- Node.js & NPM (for frontend assets if needed)

## Step-by-Step Installation

### 1. Clone the Repository

```bash
git clone https://github.com/mr-leo44/Groupe-Ka-Library-API.git
cd Groupe-Ka-Library-API
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Install Additional Security Packages

```bash
# Activity logging
composer require spatie/laravel-activitylog

# If not already installed
composer require owen-it/laravel-auditing
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require laravel/socialite
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=groupeka_library
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Configure Mail (Important for Email Verification)

**For Development (Mailtrap):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

**For Production (Example: Gmail):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### 7. Configure Sanctum

```env
SANCTUM_EXPIRATION=60  # Tokens expire after 60 minutes
```

### 8. Configure Social Login (Optional)

**Google OAuth:**
- Go to [Google Console](https://console.cloud.google.com)
- Create OAuth 2.0 credentials
- Add to `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
```

**Apple Sign In:**
- Go to [Apple Developer](https://developer.apple.com)
- Configure Sign in with Apple
- Add to `.env`:

```env
APPLE_CLIENT_ID=your-client-id
APPLE_CLIENT_SECRET=your-client-secret
```

### 9. Publish Configuration Files

```bash
# Publish activity log migrations
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

# Publish activity log config
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"

# Create queue jobs table
php artisan queue:table
```

### 10. Run Migrations

```bash
php artisan migrate
```

### 11. Seed Database with Roles

```bash
php artisan db:seed --class=RoleSeeder
```

### 12. Create Storage Link (if needed)

```bash
php artisan storage:link
```

### 13. Start Queue Worker (Important for Emails)

```bash
# In a separate terminal
php artisan queue:work

# Or use supervisor in production
```

### 14. Start Development Server

```bash
php artisan serve
```

API will be available at: `http://localhost:8000`

---

## 🔧 Post-Installation Configuration

### Create Admin User

```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@groupeka.com',
    'password' => Hash::make('SecurePassword123!'),
    'email_verified_at' => now(),
]);

$user->assignRole('admin');
```

### Enable New Device Notifications (Optional)

In `.env`:

```env
ENABLE_NEW_DEVICE_NOTIFICATIONS=true
```

Uncomment in `DetectSuspiciousLogin.php`:

```php
$user->notify(new NewDeviceLoginNotification($currentIp, $userAgent));
```

### Configure Rate Limiting (Optional)

In `.env`:

```env
THROTTLE_LOGIN_MAX_ATTEMPTS=5
THROTTLE_LOGIN_DECAY_MINUTES=1
```

---

## 🧪 Testing the API

### Register a New User

```bash
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!@",
  "password_confirmation": "SecurePass123!@"
}
```

### Login

```bash
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123!@"
}
```

### Access Protected Route

```bash
GET http://localhost:8000/api/user/profile
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📝 API Documentation

All available endpoints:

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Login with email/password |
| POST | `/api/auth/social` | Social login (Google/Apple) |
| POST | `/api/auth/forgot-password` | Request password reset |
| POST | `/api/auth/reset-password` | Reset password with token |

### Protected Endpoints (Requires Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/logout` | Logout current device |
| POST | `/api/auth/logout-all` | Logout all devices |
| GET | `/api/user/profile` | Get user profile |
| PUT | `/api/user/profile` | Update profile |
| POST | `/api/user/change-password` | Change password |
| GET | `/api/user/sessions` | List active sessions |
| DELETE | `/api/user/sessions/{id}` | Revoke session |

### Admin Endpoints (Requires Admin Role)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/users` | List all users |
| GET | `/api/admin/users/{id}` | Get user details |
| PUT | `/api/admin/users/{id}/role` | Update user role |
| DELETE | `/api/admin/users/{id}` | Delete user |
| GET | `/api/admin/audits` | List all audits |
| GET | `/api/admin/activities` | List activities |
| GET | `/api/admin/statistics` | Get dashboard stats |

---

## 🚀 Deployment to Production

### 1. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 2. Set Environment to Production

```env
APP_ENV=production
APP_DEBUG=false
```

### 3. Setup Queue Worker with Supervisor

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
```

### 4. Setup Scheduled Tasks (Cron)

```bash
crontab -e
```

Add:

```cron
* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Configure Web Server (Nginx example)

```nginx
server {
    listen 80;
    server_name api.groupeka.com;
    root /path/to/your/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔒 Security Checklist

- [ ] Change `APP_KEY` in production
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure proper CORS settings
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set strong database password
- [ ] Configure firewall (UFW/iptables)
- [ ] Setup regular database backups
- [ ] Monitor logs regularly
- [ ] Keep dependencies updated (`composer update`)
- [ ] Configure rate limiting properly
- [ ] Review and test all authentication flows

---

## 🐛 Troubleshooting

### Email Not Sending

```bash
# Check queue
php artisan queue:work --verbose

# Check logs
tail -f storage/logs/laravel.log
```

### Token Expiration Issues

Check `config/sanctum.php`:

```php
'expiration' => env('SANCTUM_EXPIRATION', 60),
```

### Permission Denied Errors

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 📞 Support

For issues or questions:
- GitHub Issues: [Repository Issues](https://github.com/mr-leo44/Groupe-Ka-Library-API/issues)
- Email: support@groupeka.com

---

## 📄 License

[Your License Here]