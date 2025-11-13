# Blog CMS System - PHP Edition

A modern, secure, and well-architected Blog Content Management System built with PHP 8+, featuring user authentication, CSRF protection, input validation, and a clean Bootstrap 5 interface. This project demonstrates professional PHP development practices with proper separation of concerns, repository pattern, and comprehensive security features.

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Tech Stack](#tech-stack)
5. [Installation](#installation)
6. [Configuration](#configuration)
7. [Usage](#usage)
8. [Project Structure](#project-structure)
9. [Security Features](#security-features)
10. [Docker Deployment](#docker-deployment)
11. [Testing](#testing)
12. [Future Enhancements](#future-enhancements)
13. [Contributing](#contributing)
14. [License](#license)

---

## Overview

This Blog CMS System is a production-ready content management platform that showcases modern PHP development practices. It features a clean MVC-inspired architecture, robust security measures, and an intuitive user interface built with Bootstrap 5.

### Key Highlights

- **Modern Architecture**: Repository pattern, dependency injection, and proper separation of concerns
- **Security First**: CSRF protection, input validation, password hashing, and SQL injection prevention
- **User Authentication**: Complete login/registration system with session management
- **Developer Friendly**: Clean code, comprehensive logging, and environment-based configuration
- **Production Ready**: Docker support, error handling, and scalable architecture

---

## Features

### Core Functionality
- ✅ **User Authentication**: Secure registration, login, and session management
- ✅ **Blog Post Management**: Full CRUD operations (Create, Read, Update, Delete)
- ✅ **Search Functionality**: Search posts by title or content
- ✅ **Pagination**: Efficient pagination for large post collections
- ✅ **Responsive Design**: Mobile-first Bootstrap 5 interface
- ✅ **Flash Messages**: User-friendly success/error notifications

### Security Features
- ✅ **CSRF Protection**: Token-based protection on all forms
- ✅ **Input Validation**: Comprehensive server-side validation
- ✅ **Password Hashing**: Bcrypt password encryption
- ✅ **SQL Injection Prevention**: Prepared statements with PDO
- ✅ **XSS Protection**: Proper output escaping
- ✅ **Session Security**: Secure session configuration and regeneration

### Developer Features
- ✅ **Environment Configuration**: `.env` file support
- ✅ **Logging System**: Comprehensive error and activity logging
- ✅ **Repository Pattern**: Clean database abstraction layer
- ✅ **Autoloading**: PSR-4 compliant autoloader
- ✅ **Error Handling**: Custom error and exception handlers
- ✅ **Docker Support**: One-command deployment

---

## Architecture

### Directory Structure

```
Blog-CMS-System-PHP/
├── bootstrap.php              # Application initialization
├── .env                       # Environment configuration (not in repo)
├── .env.example              # Environment template
├── docker-compose.yml        # Docker configuration
├── Dockerfile                # Docker image definition
│
├── database/
│   └── schema.sql           # Database schema with indexes
│
├── public/                   # Public web root
│   ├── index.php            # Homepage (list posts)
│   ├── view_post.php        # Single post view
│   ├── create_post.php      # Create new post
│   ├── edit_post.php        # Edit existing post
│   ├── delete_post.php      # Delete post handler
│   ├── login.php            # User login
│   ├── register.php         # User registration
│   └── logout.php           # Logout handler
│
├── src/
│   ├── Config/
│   │   └── Database.php     # Database connection (Singleton)
│   │
│   ├── Controllers/
│   │   ├── PostController.php    # Post management logic
│   │   └── AuthController.php    # Authentication logic
│   │
│   ├── Models/
│   │   ├── BaseRepository.php    # Base CRUD operations
│   │   ├── PostRepository.php    # Post-specific queries
│   │   └── UserRepository.php    # User-specific queries
│   │
│   ├── Middleware/
│   │   └── Auth.php         # Authentication middleware
│   │
│   ├── Helpers/
│   │   ├── Env.php          # Environment variable loader
│   │   ├── Logger.php       # File-based logger
│   │   ├── Session.php      # Session management
│   │   ├── CSRF.php         # CSRF token handler
│   │   └── Validator.php    # Input validation
│   │
│   └── Views/
│       ├── header.php       # HTML header template
│       └── footer.php       # HTML footer template
│
├── logs/                     # Application logs
└── tests/                    # Unit tests (future)
```

### Design Patterns

- **Repository Pattern**: Clean separation between data access and business logic
- **Singleton Pattern**: Database connection management
- **MVC Architecture**: Separation of views, controllers, and models
- **Dependency Injection**: Controllers receive dependencies
- **Factory Pattern**: Used in database connection creation

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Language** | PHP | 8.0+ |
| **Database** | MySQL / MariaDB | 8.0+ / 10.5+ |
| **Web Server** | Apache / Nginx | Latest |
| **Frontend** | Bootstrap | 5.3.2 |
| **Icons** | Bootstrap Icons | 1.11.1 |
| **Container** | Docker | Latest |
| **PHP Extensions** | PDO, pdo_mysql, mbstring | - |

---

## Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or MariaDB 10.5+
- Apache/Nginx (or PHP built-in server for development)
- Docker & Docker Compose (optional, for containerized deployment)

### Quick Start (Manual Setup)

1. **Clone the Repository**

```bash
git clone https://github.com/UNC-GDSC/Blog-CMS-System-PHP.git
cd Blog-CMS-System-PHP
```

2. **Configure Environment**

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=blog_cms
DB_USER=your_username
DB_PASS=your_password
```

3. **Create Database**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE blog_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. **Import Schema**

```bash
mysql -u your_username -p blog_cms < database/schema.sql
```

5. **Set Permissions**

```bash
chmod -R 755 .
chmod -R 777 logs/
```

6. **Start PHP Server**

```bash
cd public
php -S localhost:8000
```

7. **Access Application**

Open your browser and navigate to:
- **Application**: http://localhost:8000
- **First**: Register a new user account

---

## Configuration

### Environment Variables

All configuration is managed through the `.env` file:

```env
# Application
APP_NAME="Blog CMS System"
APP_ENV=development          # development | production
APP_DEBUG=true               # true | false
APP_URL=http://localhost:8000

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=blog_cms
DB_USER=root
DB_PASS=

# Session
SESSION_LIFETIME=7200        # 2 hours in seconds
SESSION_NAME=blog_cms_session

# Security
SECRET_KEY=your-secret-key-change-in-production
CSRF_TOKEN_EXPIRY=3600      # 1 hour

# Timezone
APP_TIMEZONE=UTC

# Logging
LOG_LEVEL=debug             # debug | info | warning | error
LOG_PATH=logs/app.log

# Pagination
POSTS_PER_PAGE=10
```

### Production Considerations

For production deployment, ensure:

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Use a strong, random `SECRET_KEY`
3. Configure HTTPS for secure cookies
4. Set appropriate file permissions
5. Enable log rotation
6. Use a dedicated database user with limited privileges

---

## Usage

### User Registration

1. Navigate to `/public/register.php`
2. Fill in username, email, and password (min 6 characters)
3. Submit to create account

### Login

1. Navigate to `/public/login.php`
2. Enter credentials
3. Access protected features after successful login

### Managing Posts

**Create Post:**
- Click "New Post" in navigation (requires login)
- Enter title (3-200 chars) and content (min 10 chars)
- Submit to publish

**Edit Post:**
- Click "Edit" button on any post
- Modify content
- Submit to save changes

**Delete Post:**
- Click "Delete" button on any post
- Confirm deletion in popup
- Post is permanently removed

**Search Posts:**
- Use search bar on homepage
- Search by title or content
- Results are paginated

---

## Security Features

### CSRF Protection

All forms include CSRF tokens that are validated on submission:

```php
<?= CSRF::field() ?>  // Generates hidden input
CSRF::verify();        // Validates token
```

### Input Validation

Comprehensive validation with custom rules:

```php
$validator = new Validator($data);
$validator->rule('title', 'required|min:3|max:200', 'Title')
          ->rule('email', 'required|email', 'Email');

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### Password Security

- Passwords are hashed using `password_hash()` with bcrypt
- Password verification uses timing-safe comparison
- Minimum length requirement enforced

### SQL Injection Prevention

All database queries use prepared statements:

```php
$stmt = $this->db->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute(['id' => $id]);
```

### Session Security

- Secure cookie configuration
- Session ID regeneration on authentication
- HTTP-only and SameSite cookies
- Periodic session regeneration

---

## Docker Deployment

### Using Docker Compose

1. **Build and Start Containers**

```bash
docker-compose up -d
```

This starts three services:
- **app**: PHP 8.2 with Apache (port 8000)
- **db**: MySQL 8.0 (port 3306)
- **phpmyadmin**: Database management (port 8080)

2. **Access Application**

- **Application**: http://localhost:8000
- **PHPMyAdmin**: http://localhost:8080

3. **Stop Containers**

```bash
docker-compose down
```

4. **View Logs**

```bash
docker-compose logs -f app
```

### Environment Variables in Docker

The `.env` file is automatically loaded. Ensure these match your `docker-compose.yml`:

```env
DB_HOST=db
DB_NAME=blog_cms
DB_USER=blog_user
DB_PASS=blog_password
```

---

## Testing

### Manual Testing

1. **Authentication Flow**
   - Register new user
   - Login with credentials
   - Verify session persistence
   - Logout and verify redirect

2. **CRUD Operations**
   - Create post with valid data
   - Edit post content
   - Delete post with confirmation
   - Verify database changes

3. **Validation**
   - Submit forms with invalid data
   - Verify error messages
   - Check CSRF token validation

4. **Search & Pagination**
   - Create multiple posts
   - Test search functionality
   - Navigate pagination

### Automated Testing (Future)

PHPUnit tests will be added in the `tests/` directory:

```bash
./vendor/bin/phpunit tests/
```

---

## Future Enhancements

### Planned Features

- [ ] **Rich Text Editor**: Integrate CKEditor or TinyMCE
- [ ] **Image Uploads**: File upload support with validation
- [ ] **Categories & Tags**: Post organization system
- [ ] **Comments System**: Allow user comments on posts
- [ ] **User Profiles**: Extended user information and avatars
- [ ] **Admin Dashboard**: Statistics and management interface
- [ ] **API Endpoints**: RESTful API for mobile apps
- [ ] **Email Notifications**: Password reset and notifications
- [ ] **Social Sharing**: Share posts on social media
- [ ] **SEO Optimization**: Meta tags and sitemap generation
- [ ] **Post Drafts**: Save posts without publishing
- [ ] **Role-Based Access**: Admin, Editor, and Author roles

### Technical Improvements

- [ ] Unit and integration tests
- [ ] Composer for dependency management
- [ ] Migration system for database changes
- [ ] Caching layer (Redis/Memcached)
- [ ] Rate limiting for API endpoints
- [ ] CLI commands for admin tasks
- [ ] Continuous Integration/Deployment

---

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Verify credentials in `.env`
- Ensure MySQL server is running
- Check database exists and user has permissions

**CSRF Token Mismatch**
- Clear browser cookies
- Ensure `.env` has `SECRET_KEY` set
- Check session is properly started

**Permission Denied Errors**
- Run `chmod -R 777 logs/` for log directory
- Ensure web server can read all files

**Bootstrap/CSS Not Loading**
- Check internet connection (CDN resources)
- Verify paths in header.php
- Check browser console for errors

---

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork the Repository**
2. **Create Feature Branch**: `git checkout -b feature/amazing-feature`
3. **Commit Changes**: `git commit -m 'Add amazing feature'`
4. **Push to Branch**: `git push origin feature/amazing-feature`
5. **Open Pull Request**

### Coding Standards

- Follow PSR-12 coding standards
- Add PHPDoc comments to all classes and methods
- Write descriptive commit messages
- Include tests for new features
- Update documentation as needed

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 UNC-CH Google Developer Student Club

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

## Authors & Acknowledgments

**UNC-CH Google Developer Student Club (GDSC)**

Special thanks to all contributors who helped make this project better!

### Contact

- **GitHub**: [UNC-GDSC](https://github.com/UNC-GDSC)
- **Issues**: [Report Bug](https://github.com/UNC-GDSC/Blog-CMS-System-PHP/issues)
- **Pull Requests**: [Contribute](https://github.com/UNC-GDSC/Blog-CMS-System-PHP/pulls)

---

## Project Status

**Current Version**: 2.0.0
**Status**: Active Development
**Last Updated**: January 2025

### Changelog

**v2.0.0** - Complete Reorganization & Enhancement
- Restructured to modern MVC architecture
- Added user authentication system
- Implemented CSRF protection
- Added input validation
- Upgraded to Bootstrap 5
- Added Docker support
- Implemented logging system
- Added pagination and search
- Enhanced security features
- Comprehensive documentation

**v1.0.0** - Initial Release
- Basic CRUD operations
- Simple UI with Bootstrap 4
- MySQL database integration

---

**Built with ❤️ by the UNC-CH GDSC Team**
