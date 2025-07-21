# API Usage Guide

This comprehensive guide covers all aspects of using the Library Management System API.

## Table of Contents

1. [Authentication](#authentication)
2. [API Conventions](#api-conventions)
3. [Error Handling](#error-handling)
4. [Pagination](#pagination)
5. [Filtering & Searching](#filtering--searching)
6. [Common Workflows](#common-workflows)
7. [Data Models](#data-models)
8. [SDK Examples](#sdk-examples)

## Authentication

### Overview
The API uses Laravel Sanctum for token-based authentication. All protected endpoints require a valid bearer token.

### Getting Started

1. **Register a new account**
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

2. **Login to receive token**
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "securepassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "member",
      "created_at": "2024-01-15T10:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
  }
}
```

3. **Use token for authenticated requests**
```http
GET /api/v1/borrows
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456789
```

### Token Management

- **Logout** (invalidates current token):
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

- **Get current user info**:
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

## API Conventions

### Request Format
- Use `Content-Type: application/json` for all POST/PUT requests
- Send data in JSON format in the request body
- Use query parameters for filtering and pagination

### Response Format
All API responses follow a consistent structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Specific error message"]
  }
}
```

### HTTP Methods
- `GET` - Retrieve data
- `POST` - Create new resources
- `PUT` - Update existing resources
- `DELETE` - Remove resources

## Error Handling

### Common Error Scenarios

#### Validation Errors (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

#### Authentication Required (401)
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

#### Insufficient Permissions (403)
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

#### Resource Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found."
}
```

### Error Recovery Strategies

1. **For 401 errors**: Refresh or re-authenticate
2. **For 422 errors**: Fix validation issues and retry
3. **For 403 errors**: Contact admin for permission changes
4. **For 500 errors**: Retry with exponential backoff

## Pagination

### Default Pagination
Most list endpoints support pagination with these parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

**Example:**
```http
GET /api/v1/books?page=2&per_page=20
```

**Paginated Response:**
```json
{
  "success": true,
  "data": {
    "data": [ /* array of items */ ],
    "current_page": 2,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "from": 21,
    "to": 40
  }
}
```

## Filtering & Searching

### Book Search
```http
GET /api/v1/books/search?q=javascript&category=programming
```

### Borrow Filtering
```http
GET /api/v1/borrows?status=pending&book_id=1&from_date=2024-01-01&to_date=2024-01-31
```

**Available filters:**
- `status` - pending, borrowed, overdue, returned, cancelled
- `book_id` - Filter by specific book
- `from_date` - Start date (YYYY-MM-DD)
- `to_date` - End date (YYYY-MM-DD)

### Overdue Books
```http
GET /api/v1/borrows/overdue
```

## Common Workflows

### 1. Book Borrowing Workflow

#### Step 1: Member requests to borrow a book
```http
POST /api/v1/borrows
Authorization: Bearer {member-token}
Content-Type: application/json

{
  "book_id": 1,
  "notes": "Need this for my research project"
}
```

#### Step 2: Admin approves the borrow request
```http
PUT /api/v1/borrows/1
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "due_date": "2024-02-15",
  "notes": "Approved. Please return by due date."
}
```

#### Step 3: Member requests to return the book
```http
POST /api/v1/returns/1/request
Authorization: Bearer {member-token}
Content-Type: application/json

{
  "notes": "Returning the book in good condition"
}
```

#### Step 4: Admin approves the return
```http
PUT /api/v1/returns/1/approve
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "notes": "Book returned in good condition"
}
```

### 2. Book Management Workflow (Admin)

#### Create a new book
```http
POST /api/v1/books
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "title": "Learning Laravel",
  "author": "John Smith",
  "isbn": "978-1234567890",
  "published_at": "2024-01-01",
  "description": "Comprehensive guide to Laravel framework",
  "stock": 10,
  "categories": [1, 2]
}
```

#### Update book information
```http
PUT /api/v1/books/1
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "stock": 15,
  "description": "Updated description with new edition info"
}
```

## Data Models

### User Model
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "member", // or "admin"
  "email_verified_at": "2024-01-15T10:00:00.000000Z",
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

### Book Model
```json
{
  "id": 1,
  "title": "The Great Gatsby",
  "author": "F. Scott Fitzgerald",
  "isbn": "978-0-7432-7356-5",
  "published_at": "1925-04-10",
  "description": "A classic American novel",
  "cover_image": null,
  "stock": 5,
  "available_stock": 3,
  "categories": [
    {
      "id": 1,
      "name": "Fiction",
      "description": "Fictional literature"
    }
  ],
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

### Borrow Model
```json
{
  "id": 1,
  "user_id": 2,
  "book_id": 1,
  "status": "borrowed", // pending, borrowed, overdue, returned, cancelled
  "requested_at": "2024-01-15T10:00:00.000000Z",
  "approved_at": "2024-01-15T11:00:00.000000Z",
  "due_date": "2024-02-15",
  "returned_at": null,
  "notes": "Research project",
  "admin_notes": "Approved",
  "user": { /* User model */ },
  "book": { /* Book model */ }
}
```

### Category Model
```json
{
  "id": 1,
  "name": "Fiction",
  "description": "Fictional literature and novels",
  "books_count": 15,
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

## SDK Examples

### JavaScript/Node.js Example

```javascript
class LibraryAPI {
  constructor(baseURL, token = null) {
    this.baseURL = baseURL;
    this.token = token;
  }

  async authenticate(email, password) {
    const response = await fetch(`${this.baseURL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    if (data.success) {
      this.token = data.data.token;
    }
    return data;
  }

  async getBooks(page = 1, perPage = 15) {
    const response = await fetch(
      `${this.baseURL}/books?page=${page}&per_page=${perPage}`,
      {
        headers: this.token ? {
          'Authorization': `Bearer ${this.token}`
        } : {}
      }
    );
    return response.json();
  }

  async borrowBook(bookId, notes = '') {
    const response = await fetch(`${this.baseURL}/borrows`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`
      },
      body: JSON.stringify({ book_id: bookId, notes })
    });
    return response.json();
  }
}

// Usage
const api = new LibraryAPI('http://localhost:8000/api/v1');
await api.authenticate('user@example.com', 'password');
const books = await api.getBooks();
```

### Python Example

```python
import requests

class LibraryAPI:
    def __init__(self, base_url, token=None):
        self.base_url = base_url
        self.token = token
        self.session = requests.Session()
    
    def authenticate(self, email, password):
        response = self.session.post(
            f"{self.base_url}/auth/login",
            json={"email": email, "password": password}
        )
        data = response.json()
        if data.get("success"):
            self.token = data["data"]["token"]
            self.session.headers.update({
                "Authorization": f"Bearer {self.token}"
            })
        return data
    
    def get_books(self, page=1, per_page=15):
        response = self.session.get(
            f"{self.base_url}/books",
            params={"page": page, "per_page": per_page}
        )
        return response.json()
    
    def borrow_book(self, book_id, notes=""):
        response = self.session.post(
            f"{self.base_url}/borrows",
            json={"book_id": book_id, "notes": notes}
        )
        return response.json()

# Usage
api = LibraryAPI("http://localhost:8000/api/v1")
api.authenticate("user@example.com", "password")
books = api.get_books()
```

### cURL Examples

```bash
# Login and save token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@library.com","password":"password123"}' \
  | jq -r '.data.token')

# Get all books
curl -X GET http://localhost:8000/api/v1/books \
  -H "Authorization: Bearer $TOKEN"

# Search books
curl -X GET "http://localhost:8000/api/v1/books/search?q=programming" \
  -H "Authorization: Bearer $TOKEN"

# Create a book (admin only)
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "title": "Advanced Laravel",
    "author": "Jane Doe",
    "isbn": "978-1234567891",
    "published_at": "2024-01-01",
    "stock": 5,
    "categories": [1]
  }'

# Request to borrow a book
curl -X POST http://localhost:8000/api/v1/borrows \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "book_id": 1,
    "notes": "Need for learning"
  }'
```

## Best Practices

1. **Always handle errors gracefully**
2. **Implement retry logic for network failures**
3. **Cache frequently accessed data (books, categories)**
4. **Use pagination for large datasets**
5. **Validate data before sending requests**
6. **Keep tokens secure and refresh when needed**
7. **Follow rate limiting guidelines**
8. **Use HTTPS in production**

## Rate Limiting

The API implements the following rate limits:

- **Authentication endpoints**: 5 requests per minute
- **General endpoints**: 60 requests per minute
- **Search endpoints**: 30 requests per minute

Rate limit headers are included in all responses:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Requests remaining
- `X-RateLimit-Reset`: Timestamp when limit resets

## Support

For additional help:
- **API Documentation**: Visit `/api/documentation` for interactive docs
- **GitHub Issues**: Report bugs and feature requests
- **Email Support**: support@libraryapi.com