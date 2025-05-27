<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Upload Documents System

## Models

### Core Models
1. **User**
   - Handles user authentication and profile
   - Fillable: name, email, password
   - Has notifications and documents relationship

2. **Document**
   - Manages uploaded documents
   - Stores document metadata and file locations
   - Has audit logs relationship

3. **AuditLog**
   - Tracks all activities on documents
   - Records user actions and timestamps
   - Belongs to users and documents

### Notification Models
1. **DatabaseNotification**
   - Handles database notifications
   - Extends Laravel's base notification
   - Polymorphic relationship with notifiable entities

### Queue Related Models
1. **Job**
   - Represents queued jobs
   - Manages job payload and attempts
   - No timestamps

2. **JobBatch**
   - Handles batch job processing
   - Tracks job completion and failures
   - Custom timestamp handling

3. **FailedJob**
   - Stores failed job information
   - Includes detailed error information
   - Helps in debugging and retry

### Authentication Related Models
1. **PasswordResetToken**
   - Manages password reset functionality
   - Email as primary key
   - No auto-incrementing

2. **Session**
   - Handles user sessions
   - Stores session data and activity
   - Belongs to User model

## Database Schema

### users
- id (bigint, auto-increment)
- name (string)
- email (string, unique)
- password (string, hashed)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)

### documents
- id (bigint, auto-increment)
- filename (string)
- original_name (string)
- mime_type (string)
- size (bigint)
- created_at, updated_at (timestamps)

### audit_logs
- id (bigint, auto-increment)
- user_id (bigint, foreign key)
- document_id (bigint, foreign key)
- action (string)
- description (text)
- created_at, updated_at (timestamps)

### notifications
- id (uuid)
- type (string)
- notifiable_type (string)
- notifiable_id (bigint)
- data (text, json)
- read_at (timestamp, nullable)
- created_at, updated_at (timestamps)

### jobs
- id (bigint, auto-increment)
- queue (string)
- payload (longtext)
- attempts (tinyint)
- reserved_at (int, nullable)
- available_at (int)
- created_at (int)

### job_batches
- id (string, primary)
- name (string)
- total_jobs (int)
- pending_jobs (int)
- failed_jobs (int)
- failed_job_ids (longtext)
- options (mediumtext, nullable)
- cancelled_at (int, nullable)
- created_at (int)
- finished_at (int, nullable)

### failed_jobs
- id (bigint, auto-increment)
- uuid (string, unique)
- connection (text)
- queue (text)
- payload (longtext)
- exception (longtext)
- failed_at (timestamp)

### password_reset_tokens
- email (string, primary)
- token (string)
- created_at (timestamp)

### sessions
- id (string, primary)
- user_id (bigint, nullable)
- ip_address (string, nullable)
- user_agent (text, nullable)
- payload (text)
- last_activity (int)
