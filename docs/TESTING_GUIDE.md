# Testing Guide

This guide covers comprehensive testing strategies for the Library Management System API.

## Table of Contents

1. [Overview](#overview)
2. [Test Environment Setup](#test-environment-setup)
3. [Manual Testing](#manual-testing)
4. [Automated Testing](#automated-testing)
5. [Performance Testing](#performance-testing)
6. [Security Testing](#security-testing)
7. [Postman Collection](#postman-collection)

## Overview

The API includes multiple testing approaches:
- **Unit Tests**: Individual component testing
- **Feature Tests**: End-to-end workflow testing
- **Integration Tests**: Database and external service testing
- **Performance Tests**: Load and stress testing
- **Security Tests**: Authentication and authorization testing

## Test Environment Setup

### Prerequisites
```bash
# Install testing dependencies
composer install --dev

# Copy test environment
cp .env.example .env.testing

# Configure test database
php artisan migrate --env=testing
php artisan db:seed --env=testing
```

### Database Configuration
Update `.env.testing`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
# OR use separate test database
DB_DATABASE=library_test
```

## Manual Testing

### 1. Authentication Flow Testing

#### Test User Registration
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { /* user object */ },
    "token": "1|token_string"
  }
}
```

#### Test Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### Test Invalid Credentials
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "wrongpassword"
  }'
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### 2. Book Management Testing

#### Test Book Creation (Admin Required)
```bash
# First login as admin
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@library.com","password":"password123"}' \
  | jq -r '.data.token')

# Create book
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "title": "Test Book",
    "author": "Test Author",
    "isbn": "978-1234567890",
    "published_at": "2024-01-01",
    "description": "Test description",
    "stock": 5,
    "categories": [1]
  }'
```

#### Test Book Search
```bash
curl -X GET "http://localhost:8000/api/v1/books/search?q=test"
```

#### Test Pagination
```bash
curl -X GET "http://localhost:8000/api/v1/books?page=1&per_page=5"
```

### 3. Borrowing Workflow Testing

#### Test Borrow Request
```bash
# Login as regular user
USER_TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}' \
  | jq -r '.data.token')

# Request borrow
curl -X POST http://localhost:8000/api/v1/borrows \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "book_id": 1,
    "notes": "Test borrow request"
  }'
```

#### Test Admin Approval
```bash
# Approve borrow (as admin)
curl -X PUT http://localhost:8000/api/v1/borrows/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "due_date": "2024-02-15",
    "notes": "Approved for testing"
  }'
```

### 4. Error Handling Testing

#### Test Validation Errors
```bash
# Missing required fields
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "title": ""
  }'
```

#### Test Unauthorized Access
```bash
# Try admin endpoint without token
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Book"
  }'
```

#### Test Forbidden Access
```bash
# Try admin endpoint with user token
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "title": "Test Book"
  }'
```

## Automated Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run specific test method
php artisan test --filter=test_user_can_login
```

### Example Test Cases

#### Authentication Tests
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'email'],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token'
                    ]
                ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }
}
```

#### Book Management Tests
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_books()
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => ['id', 'title', 'author', 'isbn']
                        ]
                    ]
                ]);
    }

    public function test_admin_can_create_book()
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
                        ->postJson('/api/v1/books', [
                            'title' => 'Test Book',
                            'author' => 'Test Author',
                            'isbn' => '978-1234567890',
                            'published_at' => '2024-01-01',
                            'stock' => 5,
                            'categories' => [$category->id]
                        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Test Book'
                    ]
                ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Test Book'
        ]);
    }

    public function test_member_cannot_create_book()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/v1/books', [
                            'title' => 'Test Book',
                            'author' => 'Test Author'
                        ]);

        $response->assertStatus(403);
    }
}
```

### Test Data Factories

```php
<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->isbn13(),
            'published_at' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'stock' => $this->faker->numberBetween(1, 20),
        ];
    }
}
```

## Performance Testing

### Load Testing with Apache Bench

```bash
# Test concurrent users
ab -n 1000 -c 10 http://localhost:8000/api/v1/books

# Test with authentication
ab -n 100 -c 5 -H "Authorization: Bearer $TOKEN" \
   http://localhost:8000/api/v1/borrows
```

### Database Query Optimization

```bash
# Enable query logging
php artisan tinker
> DB::enableQueryLog();
> // Perform operations
> DB::getQueryLog();
```

### Performance Benchmarks

```php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_listing_performance()
    {
        // Create large dataset
        Book::factory()->count(1000)->create();

        $start = microtime(true);
        
        $response = $this->getJson('/api/v1/books?per_page=50');
        
        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(200);
        
        // Assert response time is under 500ms
        $this->assertLessThan(0.5, $duration);
    }
}
```

## Security Testing

### Authentication Security

```bash
# Test token expiration
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer expired_token"

# Test invalid token format
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer invalid_format"

# Test missing authorization header
curl -X GET http://localhost:8000/api/v1/borrows
```

### Authorization Testing

```bash
# Test role-based access
curl -X POST http://localhost:8000/api/v1/books \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test"}'
```

### Input Validation Testing

```bash
# SQL Injection attempts
curl -X GET "http://localhost:8000/api/v1/books/search?q='; DROP TABLE books; --"

# XSS attempts
curl -X POST http://localhost:8000/api/v1/books \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "<script>alert(\"xss\")</script>",
    "author": "Test"
  }'
```

## Postman Collection

### Setting Up Postman

1. **Create Environment Variables**
   - `base_url`: `http://localhost:8000/api/v1`
   - `admin_token`: (will be set by login script)
   - `user_token`: (will be set by login script)

2. **Pre-request Scripts**
```javascript
// Auto-login script
if (!pm.environment.get("admin_token")) {
    pm.sendRequest({
        url: pm.environment.get("base_url") + "/auth/login",
        method: "POST",
        header: {
            "Content-Type": "application/json"
        },
        body: {
            mode: "raw",
            raw: JSON.stringify({
                email: "admin@library.com",
                password: "password123"
            })
        }
    }, function(err, response) {
        if (!err && response.json().success) {
            pm.environment.set("admin_token", response.json().data.token);
        }
    });
}
```

3. **Test Scripts**
```javascript
// Response validation
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    const response = pm.response.json();
    pm.expect(response).to.have.property("success", true);
});

pm.test("Response structure is valid", function () {
    const response = pm.response.json();
    pm.expect(response).to.have.all.keys("success", "message", "data");
});
```

### Collection Structure

```
Library API
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Logout
│   └── Get Current User
├── Books
│   ├── List Books
│   ├── Search Books
│   ├── Get Book Details
│   ├── Create Book (Admin)
│   ├── Update Book (Admin)
│   └── Delete Book (Admin)
├── Categories
│   ├── List Categories
│   ├── Create Category (Admin)
│   ├── Update Category (Admin)
│   └── Delete Category (Admin)
├── Borrowing
│   ├── List Borrows
│   ├── Request Borrow
│   ├── Approve Borrow (Admin)
│   ├── Reject Borrow
│   └── Get Overdue Books
└── Returns
    ├── Request Return
    ├── Approve Return (Admin)
    └── Reject Return (Admin)
```

## Continuous Integration

### GitHub Actions Test Workflow

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: library_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, pdo, pdo_mysql
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run tests
      run: php artisan test --coverage
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: library_test
        DB_USERNAME: root
        DB_PASSWORD: password
```

## Test Coverage Goals

- **Unit Tests**: 90%+ coverage
- **Feature Tests**: 85%+ coverage
- **Integration Tests**: 80%+ coverage
- **Critical Paths**: 100% coverage (auth, borrowing workflow)

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Data Setup**: Use factories and seeders
3. **Meaningful Names**: Test names should describe behavior
4. **AAA Pattern**: Arrange, Act, Assert
5. **Mock External Services**: Don't rely on external APIs
6. **Test Edge Cases**: Boundary conditions and error scenarios
7. **Performance Considerations**: Test response times
8. **Security Testing**: Validate authentication and authorization