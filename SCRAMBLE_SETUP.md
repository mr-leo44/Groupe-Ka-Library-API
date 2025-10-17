# 📚 Laravel Scramble - Installation Guide

## Installation

```bash
composer require dedoc/scramble
```

## Publish Configuration

```bash
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag="scramble-config"
```

## Access Documentation

After installation, your API documentation will be available at:

```
http://localhost:8000/docs/api
```

## Configuration is automatic!

Scramble will automatically:
- ✅ Scan your routes in `routes/api.php`
- ✅ Parse FormRequest validation rules
- ✅ Detect return types from controllers
- ✅ Generate OpenAPI 3.1 specification
- ✅ Create interactive Swagger UI

## Custom Configuration (Optional)

Edit `config/scramble.php` if needed for advanced customization.