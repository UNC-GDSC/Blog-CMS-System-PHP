# PHP Blog CMS

A simple Blog Content Management System (CMS) built using PHP and MySQL. This application provides a basic web interface for creating, reading, updating, and deleting (CRUD) blog posts. It is designed for learning and development purposes, and can be extended with features like user authentication, comments, and an administrative dashboard.

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Architecture & Design](#architecture--design)
4. [Tech Stack](#tech-stack)
5. [Installation & Setup](#installation--setup)
    - [Prerequisites](#prerequisites)
    - [Local Setup](#local-setup)
    - [Environment Configuration](#environment-configuration)
6. [Database Schema](#database-schema)
7. [File Structure](#file-structure)
8. [Usage](#usage)
9. [Testing](#testing)
10. [Deployment](#deployment)
    - [Apache/Nginx Setup](#apachenignx-setup)
    - [Docker Deployment](#docker-deployment)
11. [Security Considerations](#security-considerations)
12. [Future Enhancements](#future-enhancements)
13. [Troubleshooting & FAQ](#troubleshooting--faq)
14. [Contributing](#contributing)
15. [License](#license)

---

## Overview

The PHP Blog CMS is a lightweight content management system that allows users to manage blog posts through a simple web interface. It demonstrates the basics of PHP with SQL persistence using PDO and MySQL. The application covers core CRUD operations and provides a foundation to expand with advanced features like user authentication, search, and SEO-friendly URLs.

---

## Features

- **CRUD Operations:** Create, read, update, and delete blog posts.
- **Responsive UI:** Built with Bootstrap for a mobile-friendly experience.
- **Simple Routing:** Easy-to-understand PHP scripts for each operation.
- **Database Integration:** Uses MySQL with PDO for secure database interactions.
- **Extensible:** Serves as a foundation for adding features like comments and user management.

---

## Architecture & Design

### System Overview

- **Frontend:**  
  HTML, CSS, JavaScript (with Bootstrap) for a responsive and clean user interface.
  
- **Backend:**  
  PHP scripts that handle business logic and routing, using PDO for database interactions.

- **Database Layer:**  
  A MySQL database stores blog posts and (optionally) user and comment data.

### Component Breakdown

- **User Module:**  
  (Future Enhancement) Manage user authentication and roles.

- **Blog Module:**  
  Manages blog posts with operations to create, edit, view, and delete content.

- **Admin Dashboard:**  
  (Optional) Interface for site administrators to manage posts and view statistics.

---

## Tech Stack

- **Programming Language:** PHP (7.4+)
- **Database:** MySQL or MariaDB
- **Web Server:** Apache or Nginx (or PHP’s built-in server for development)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 4
- **ORM/Database Interaction:** PDO
- **Dependency Management:** None required for core functionality (Composer optional for further extensions)

---

## Installation & Setup

### Prerequisites

- PHP 7.4 or above installed.
- MySQL or MariaDB database server.
- Web server (Apache, Nginx, or use PHP built-in server).
- Git (optional, for cloning the repository).

### Local Setup

1. **Clone the Repository**

   ```bash
   git clone https://github.com/your-username/php-blog-cms.git
   cd php-blog-cms
   ```

2. **Configure Environment**

   Create a file named `config.php` in the project root with your database and application settings. Example:

   ```php
   <?php
   // config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'blog_cms');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   define('BASE_URL', 'http://localhost/blog-cms/');
   date_default_timezone_set('UTC');

   try {
       $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
       die("Could not connect to the database " . DB_NAME . ": " . $e->getMessage());
   }
   ?>
   ```

3. **Set Up the Database**

  - Create a new database called `blog_cms` (or update `DB_NAME` accordingly):

    ```sql
    CREATE DATABASE blog_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

  - Import the provided schema located in `database/schema.sql`:

    ```bash
    mysql -u your_db_username -p blog_cms < database/schema.sql
    ```

---

## Database Schema

The SQL schema in `database/schema.sql` sets up the following tables:

- **users** (optional for future authentication):

  ```sql
  CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(50) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      email VARCHAR(100),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```

- **posts** (for blog posts):

  ```sql
  CREATE TABLE IF NOT EXISTS posts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(150) NOT NULL,
      content TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
  ```

- **comments** (optional for future use):

  ```sql
  CREATE TABLE IF NOT EXISTS comments (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT NOT NULL,
      author VARCHAR(100),
      content TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (post_id) REFERENCES posts(id)
  );
  ```

---

## File Structure

```
php-blog-cms/
├── config.php
├── index.php
├── create_post.php
├── view_post.php
├── edit_post.php
├── delete_post.php
├── inc/
│   ├── header.php
│   └── footer.php
└── database/
    └── schema.sql
```

- **config.php:** Contains configuration settings and creates a PDO connection.
- **index.php:** Lists all blog posts.
- **create_post.php:** Provides a form to create new blog posts.
- **view_post.php:** Displays a single post.
- **edit_post.php:** Provides a form to edit an existing post.
- **delete_post.php:** Deletes a post.
- **inc/header.php & inc/footer.php:** Common header and footer for the pages.
- **database/schema.sql:** SQL script to set up the database schema.

---

## Usage

### Web Interface

- **Homepage:**  
  Visit `http://localhost/blog-cms/` to see a list of blog posts.

- **Create Post:**  
  Navigate to `http://localhost/blog-cms/create_post.php` to create a new post.

- **View Post:**  
  Click on "Read More" from any post card on the homepage to view full content.

- **Edit/Delete Post:**  
  Use the corresponding buttons on each post to edit or delete posts.

### Running the Application with PHP’s Built-In Server

From the project directory, run:

```bash
php -S localhost:8000
```

Then, open your browser at [http://localhost:8000](http://localhost:8000).

---

## Testing

### Manual Testing

- Use your browser to navigate through the application.
- Verify that posts can be created, viewed, updated, and deleted.
- Ensure that the database updates accordingly.

### Automated Testing

- (Optional) Write PHPUnit tests to cover key functionalities.
- Use Postman to test API endpoints if you extend the application with a REST API.

---

## Deployment

### Apache/Nginx Setup

- **Apache:**  
  Configure a virtual host and ensure `mod_rewrite` is enabled for clean URLs.

- **Nginx:**  
  Set up a server block similar to the following:

  ```nginx
  server {
      listen 80;
      server_name your-domain.com;
      root /path/to/php-blog-cms;
      index index.php index.html;
  
      location / {
          try_files \$uri \$uri/ /index.php?\$query_string;
      }
  
      location ~ \.php$ {
          include snippets/fastcgi-php.conf;
          fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
      }
  }
  ```

### Docker Deployment

A sample `Dockerfile` and `docker-compose.yml` are provided (see documentation below for details):

- **Dockerfile:**

  ```dockerfile
  FROM php:7.4-apache
  RUN a2enmod rewrite
  COPY . /var/www/html/
  RUN docker-php-ext-install pdo pdo_mysql
  EXPOSE 80
  CMD ["apache2-foreground"]
  ```

- **docker-compose.yml:**

  ```yaml
  version: '3.7'
  services:
    web:
      build: .
      ports:
        - "80:80"
      volumes:
        - .:/var/www/html
      depends_on:
        - db
    db:
      image: mysql:5.7
      restart: always
      environment:
        MYSQL_ROOT_PASSWORD: your_root_password
        MYSQL_DATABASE: blog_cms
        MYSQL_USER: your_db_username
        MYSQL_PASSWORD: your_db_password
      volumes:
        - db_data:/var/lib/mysql
  volumes:
    db_data:
  ```

---

## Security Considerations

- **Input Sanitization:**  
  Use functions like `htmlspecialchars()` and prepared statements via PDO.
- **Password Hashing:**  
  (Future Enhancement) Use `password_hash()` for storing user passwords securely.
- **Session Security:**  
  Ensure proper session management and consider HTTPS for production.
- **Error Handling:**  
  Log errors instead of displaying them in a production environment.

---

## Future Enhancements

- **User Authentication & Roles:**  
  Implement login functionality to restrict access to create/edit/delete operations.
- **Rich Text Editing:**  
  Integrate a WYSIWYG editor for creating posts.
- **Comments Module:**  
  Allow readers to leave comments on posts.
- **SEO & Analytics:**  
  Enhance post URLs, metadata, and add tracking for page views.
- **RESTful API:**  
  Develop API endpoints for integrating with external applications or a modern JavaScript frontend.

---

## Troubleshooting & FAQ

- **Database Connection Issues:**  
  Ensure that `config.php` has the correct database credentials and that the MySQL server is running.
- **404 Errors:**  
  Verify the file paths and URL rewriting configuration in your web server.
- **Permission Issues:**  
  Ensure your web server has the necessary permissions to read the project files.

---

## Contributing

Contributions are welcome! Follow these steps:
1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`.
3. Commit your changes with clear messages.
4. Push your branch and open a pull request detailing your changes.

Please adhere to the coding standards and include tests where applicable.

---

## License

This project is licensed under the [MIT License](LICENSE).

---

## Authors

The UNC-CH Google Developer Student Club (GDSC) team.
