# 📚 Groupe Ka – Digital Library Platform  

This project is a **digital library and book marketplace** for the **Groupe Ka association**.  
It allows **members, managers, and admins** to manage and purchase books, with features for authentication, role-based access, promotions, and secure book distribution (PDFs accessible only within the app).  

The ecosystem consists of:  

- **API (Backend)** – built with **Laravel 12.2*** using Spatie Roles & Permissions, Socialite for authentication, Sanctum for token handling, and advanced security features (rate limiting, audit logs).  
- **Mobile App (Frontend)** – built with **Flutter**, structured with **Clean Architecture**, **Riverpod** for state management, and **SOLID principles**.  

---

## 📑 Table of Contents  

1. [Features](#features)  
2. [Architecture](#architecture)  
3. [API (Laravel)](#api-laravel)  
   - [Authentication & Roles](#authentication--roles)  
   - [Rate Limiting & Security](#rate-limiting--security)  
   - [Audit Logging](#audit-logging)  
   - [Endpoints](#endpoints)  
4. [Mobile App (Flutter)](#mobile-app-flutter)  
   - [Architecture & State Management](#architecture--state-management)  
   - [Authentication Flow](#authentication-flow)  
   - [Book Purchase & Access Control](#book-purchase--access-control)  
5. [Installation & Setup](#installation--setup)  
6. [Testing](#testing)  
7. [Security Guidelines](#security-guidelines)  
8. [Future Improvements](#future-improvements)  

---

## 🚀 Features  

- ✅ **Authentication** via email/password + **Social Media login** (Google, Facebook, etc.)  
- ✅ **Role-based Access Control** (Admin, Manager, Member) using [Spatie Roles & Permissions](https://spatie.be/docs/laravel-permission).  
- ✅ **Secure book marketplace** (PDFs only readable within the app, linked to the buyer’s account).  
- ✅ **Promotion system** (discounts on selected works).  
- ✅ **Book proposals** (members can propose a work → requires admin validation).  
- ✅ **Audit & monitoring** with activity logs and rate limiting.  
- ✅ **Cross-platform Mobile App** built with **Flutter + Riverpod + Clean Architecture**.  

---

## 🏗 Architecture  

The platform follows **Clean Architecture** and **SOLID principles**:  

- **Backend (Laravel)**  
  - Controllers → thin, delegate logic  
  - Services → contain business logic  
  - Repositories → handle database queries  
  - Middleware → enforce security (rate limits, audit)  

- **Frontend (Flutter)**  
  - Feature-driven folders (auth, books, profile)  
  - Separation of concerns (UI → State → Domain → Data layers)  
  - Riverpod for state management  
  - Secure local storage for tokens & purchased works  

---

## 🔐 API (Laravel)  

### Authentication & Roles  

- **JWT / Sanctum tokens** for API security.  
- **Socialite** for OAuth login (Google, Facebook, etc.).  
- **Spatie Roles** for `admin`, `manager`, `member`.  

📌 See implementation:  
- [Auth Controller](./api/app/Http/Controllers/AuthController.php)  
- [Role Middleware](./api/app/Http/Middleware/RoleMiddleware.php)  

---

### Rate Limiting & Security  

- Per-user rate limiting to prevent brute force attacks.  
- Request validation & input sanitization.  
- Secure storage of sensitive data.  

📌 See:  
- [Rate Limiter](./api/app/Providers/RouteServiceProvider.php)  

---

### Audit Logging  

- Every critical action (login, book purchase, role change) is logged.  
- Admins can review activity history.  

📌 See:  
- [Audit Logs](./api/app/Models/AuditLog.php)  

---

### Endpoints  

| Method | Endpoint | Description | Role Required |  
|--------|----------|-------------|---------------|  
| `POST` | `/api/auth/login` | Login with email/password | All |  
| `POST` | `/api/auth/social` | Social login via Google/Facebook | All |  
| `GET` | `/api/books` | List all available works | Member+ |  
| `POST` | `/api/books/propose` | Propose a new book | Member |  
| `POST` | `/api/books/{id}/purchase` | Purchase a book | Member |  
| `GET` | `/api/admin/books` | List all proposed books | Admin |  
| `POST` | `/api/admin/books/{id}/validate` | Validate a proposed book | Admin |  

---

## 📱 Mobile App (Flutter)  

### Architecture & State Management  

- **Riverpod** for global state handling.  
- **Feature-based folder structure**:  
