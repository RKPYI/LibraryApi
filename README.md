# Library Management System API

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0.0-6BA539?logo=openapi-initiative)](https://swagger.io)

A comprehensive RESTful API for library management system built with Laravel. This application provides secure and efficient management of books, users, categories, and borrowing workflows with role-based access control and complete audit trails.

## 📚 Table of Contents

- [Features](#-features)
- [Architecture](#-architecture)
- [Quick Start](#-quick-start)
- [API Documentation](#-api-documentation)
- [Authentication](#-authentication)
- [Endpoints Overview](#-endpoints-overview)
- [Error Handling](#-error-handling)
- [Rate Limiting](#-rate-limiting)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

## 🚀 Features

### Core Functionality
- **🔐 Secure Authentication**: JWT-based authentication using Laravel Sanctum
- **👥 Role-Based Access Control**: Granular permissions for `admin` and `member` roles
- **📖 Book Management**: Full CRUD operations with advanced search and categorization
- **📂 Category Management**: Hierarchical organization of books
- **🔄 Borrowing Workflow**: Complete request/approval cycle with status tracking
- **📅 Return Management**: Streamlined return process with admin oversight
- **🔍 Advanced Search**: Multi-field search across books and metadata

### Technical Features
- **🎯 RESTful Design**: Consistent API patterns following REST principles
- **📋 OpenAPI Documentation**: Interactive Swagger UI with comprehensive endpoint documentation
- **✅ Input Validation**: Robust request validation with detailed error messages
- **📊 Structured Responses**: Consistent JSON response format across all endpoints
- **🚀 Performance Optimized**: Efficient database queries with eager loading
- **🔒 Security Best Practices**: CSRF protection, SQL injection prevention, and secure headers

## 🏗 Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Controllers   │────│    Services     │────│  Repositories   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Middleware    │    │     Models      │    │    Database     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Key Components
- **Controllers**: Handle HTTP requests and responses
- **Services**: Business logic and complex operations
- **Repositories**: Data access layer with query optimization
- **Models**: Eloquent models with relationships and scopes
- **Middleware**: Authentication, authorization, and request filtering

## 🚀 Quick Start

### Prerequisites

- **PHP**: >= 8.2
- **Composer**: Latest version
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache/Nginx (optional for development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/RKPYI/LibraryApi.git
   cd LibraryApi
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate --seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

   🎉 **Your API is now running at** `http://localhost:8000`

### Default Admin Account
```
Email: admin@library.com
Password: password123
```

## 📖 API Documentation

### Interactive Documentation
Visit the **Swagger UI** at: `http://localhost:8000/api/documentation`

### Comprehensive Documentation
For detailed guides, examples, and deployment instructions, see our comprehensive documentation:

- **📖 [Complete Documentation Index](docs/README.md)** - Navigation hub for all documentation
- **🔧 [API Usage Guide](docs/API_GUIDE.md)** - Detailed developer guide with examples
- **🧪 [Testing Guide](docs/TESTING_GUIDE.md)** - Comprehensive testing strategies
- **🚀 [Deployment Guide](docs/DEPLOYMENT_GUIDE.md)** - Production deployment instructions
- **📬 [Postman Collection](docs/postman/LibraryAPI.postman_collection.json)** - Ready-to-use API collection

### Generate Documentation
```bash
php artisan l5-swagger:generate
```

### Base URL
```
Development: http://localhost:8000/api/v1
Production: https://your-domain.com/api/v1
```

## 🔐 Authentication

The API uses **Laravel Sanctum** for authentication with bearer tokens.

### Authentication Flow

1. **Register/Login** to receive an authentication token
2. **Include the token** in the `Authorization` header for protected endpoints
3. **Token format**: `Bearer {your-token-here}`

### Example Authentication

```bash
# 1. Login to get token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@library.com",
    "password": "password123"
  }'

# Response
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|abc123xyz..."
  }
}

# 2. Use token for protected endpoints
curl -X GET http://localhost:8000/api/v1/books \
  -H "Authorization: Bearer 1|abc123xyz..."
```

## 🛠 Endpoints Overview

### Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/register` | Register new user | No |
| POST | `/auth/login` | User login | No |
| POST | `/auth/logout` | User logout | Yes |
| GET | `/auth/me` | Get current user | Yes |

### Books
| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/books` | List all books | No | - |
| GET | `/books/{id}` | Get book details | No | - |
| GET | `/books/search?q={query}` | Search books | No | - |
| POST | `/books` | Create new book | Yes | Admin |
| PUT | `/books/{id}` | Update book | Yes | Admin |
| DELETE | `/books/{id}` | Delete book | Yes | Admin |

### Categories
| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/categories` | List categories | No | - |
| GET | `/categories/{id}` | Get category details | No | - |
| POST | `/categories` | Create category | Yes | Admin |
| PUT | `/categories/{id}` | Update category | Yes | Admin |
| DELETE | `/categories/{id}` | Delete category | Yes | Admin |

### Borrowing & Returns
| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/borrows` | List borrows | Yes | Any |
| POST | `/borrows` | Request borrow | Yes | Any |
| PUT | `/borrows/{id}` | Approve borrow | Yes | Admin |
| PUT | `/borrows/{id}/reject` | Reject borrow | Yes | Admin |
| POST | `/returns/{id}/request` | Request return | Yes | Any |
| PUT | `/returns/{id}/approve` | Approve return | Yes | Admin |
| PUT | `/returns/{id}/reject` | Reject return | Yes | Admin |

### Users (Admin Only)
| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/users` | List all users | Yes | Admin |
| GET | `/users/{id}` | Get user details | Yes | Any |
| PUT | `/users/{id}` | Update user | Yes | Any |
| DELETE | `/users/{id}` | Delete user | Yes | Any |

## 📋 Request/Response Examples

### Creating a Book
```bash
POST /api/v1/books
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "The Great Gatsby",
  "author": "F. Scott Fitzgerald",
  "isbn": "978-0-7432-7356-5",
  "published_at": "1925-04-10",
  "description": "A classic American novel",
  "stock": 5,
  "categories": [1, 2]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Book created successfully",
  "data": {
    "id": 1,
    "title": "The Great Gatsby",
    "author": "F. Scott Fitzgerald",
    "isbn": "978-0-7432-7356-5",
    "published_at": "1925-04-10",
    "description": "A classic American novel",
    "stock": 5,
    "available_stock": 5,
    "categories": [
      {
        "id": 1,
        "name": "Fiction"
      },
      {
        "id": 2,
        "name": "Classic Literature"
      }
    ],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### Requesting a Borrow
```bash
POST /api/v1/borrows
Content-Type: application/json
Authorization: Bearer {token}

{
  "book_id": 1,
  "notes": "I need this book for my research project"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Borrow request submitted successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "book_id": 1,
    "status": "pending",
    "notes": "I need this book for my research project",
    "requested_at": "2024-01-15T14:30:00.000000Z",
    "book": {
      "title": "The Great Gatsby",
      "author": "F. Scott Fitzgerald"
    }
  }
}
```

## ⚠️ Error Handling

The API uses consistent error response format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

### HTTP Status Codes
- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `204 No Content` - Request successful, no content returned
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

## 🚦 Rate Limiting

The API implements rate limiting to ensure fair usage:

- **Authentication endpoints**: 5 requests per minute
- **General endpoints**: 60 requests per minute
- **Search endpoints**: 30 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## 🧪 Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### API Testing with Postman
Import the Postman collection: [Download Collection](postman/LibraryAPI.postman_collection.json)

### Manual Testing Examples

```bash
# Test book search
curl -X GET "http://localhost:8000/api/v1/books/search?q=gatsby"

# Test authentication
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@library.com","password":"password123"}'
```

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Configure database credentials
- [ ] Set up SSL certificates
- [ ] Configure caching (Redis/Memcached)
- [ ] Set up queue workers
- [ ] Configure logging
- [ ] Set up monitoring

### Deployment Commands
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Update documentation
php artisan l5-swagger:generate
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation for new features
- Use semantic commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📞 Support

- **Documentation**: [API Documentation](http://localhost:8000/api/documentation)
- **Issues**: [GitHub Issues](https://github.com/RKPYI/LibraryApi/issues)
- **Email**: support@libraryapi.com

---

**Made with ❤️ by the Library API Team**
