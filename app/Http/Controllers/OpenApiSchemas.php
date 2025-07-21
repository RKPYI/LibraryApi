<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin", "member"}, example="member"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     title="Book",
 *     description="Book model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *     @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
 *     @OA\Property(property="isbn", type="string", example="978-0-7432-7356-5"),
 *     @OA\Property(property="published_at", type="string", format="date", example="1925-04-10"),
 *     @OA\Property(property="description", type="string", nullable=true, example="A classic American novel"),
 *     @OA\Property(property="cover_image", type="string", nullable=true, example="https://example.com/covers/book1.jpg"),
 *     @OA\Property(property="stock", type="integer", example=5),
 *     @OA\Property(property="available_stock", type="integer", example=3),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Category model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Fiction"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Fictional literature and novels"),
 *     @OA\Property(property="books_count", type="integer", example=15),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Borrow",
 *     type="object",
 *     title="Borrow",
 *     description="Borrow model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="book_id", type="integer", example=1),
 *     @OA\Property(
 *         property="status", 
 *         type="string", 
 *         enum={"pending", "borrowed", "overdue", "returned", "cancelled", "return_requested", "return_rejected"},
 *         example="borrowed"
 *     ),
 *     @OA\Property(property="requested_at", type="string", format="date-time"),
 *     @OA\Property(property="approved_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2024-02-15"),
 *     @OA\Property(property="returned_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Research project"),
 *     @OA\Property(property="admin_notes", type="string", nullable=true, example="Approved"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="book", ref="#/components/schemas/Book"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="API Response",
 *     description="Standard API response format",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object", description="Response data")
 * )
 * 
 * @OA\Schema(
 *     schema="ApiErrorResponse",
 *     type="object",
 *     title="API Error Response",
 *     description="Standard API error response format",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error description"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"field_name": {"Specific validation error"}},
 *         description="Validation errors"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     title="Paginated Response",
 *     description="Paginated response format",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="to", type="integer", example=15)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     title="Authentication Response",
 *     description="Authentication response with user and token",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Login successful"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz123456789")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="BookCreateRequest",
 *     type="object",
 *     title="Book Create Request",
 *     description="Request body for creating a book",
 *     required={"title", "author", "published_at", "isbn"},
 *     @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *     @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
 *     @OA\Property(property="isbn", type="string", example="978-0-7432-7356-5"),
 *     @OA\Property(property="published_at", type="string", format="date", example="1925-04-10"),
 *     @OA\Property(property="description", type="string", example="A classic American novel"),
 *     @OA\Property(property="cover_image", type="string", format="binary", description="Book cover image file"),
 *     @OA\Property(property="stock", type="integer", example=5),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         example={1, 2},
 *         description="Array of category IDs"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="BookUpdateRequest",
 *     type="object",
 *     title="Book Update Request",
 *     description="Request body for updating a book",
 *     @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *     @OA\Property(property="author", type="string", example="F. Scott Fitzgerald"),
 *     @OA\Property(property="isbn", type="string", example="978-0-7432-7356-5"),
 *     @OA\Property(property="published_at", type="string", format="date", example="1925-04-10"),
 *     @OA\Property(property="description", type="string", example="A classic American novel"),
 *     @OA\Property(property="cover_image", type="string", format="binary", description="Book cover image file"),
 *     @OA\Property(property="stock", type="integer", example=5),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         example={1, 2},
 *         description="Array of category IDs"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="CategoryRequest",
 *     type="object",
 *     title="Category Request",
 *     description="Request body for creating/updating a category",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Fiction"),
 *     @OA\Property(property="description", type="string", example="Fictional literature and novels")
 * )
 * 
 * @OA\Schema(
 *     schema="BorrowRequest",
 *     type="object",
 *     title="Borrow Request",
 *     description="Request body for borrowing a book",
 *     required={"book_id"},
 *     @OA\Property(property="book_id", type="integer", example=1),
 *     @OA\Property(property="notes", type="string", example="I need this book for my research project")
 * )
 * 
 * @OA\Schema(
 *     schema="BorrowApprovalRequest",
 *     type="object",
 *     title="Borrow Approval Request",
 *     description="Request body for approving a borrow",
 *     @OA\Property(property="due_date", type="string", format="date", example="2024-02-15"),
 *     @OA\Property(property="notes", type="string", example="Approved. Please return by due date.")
 * )
 * 
 * @OA\Schema(
 *     schema="ReturnRequest",
 *     type="object",
 *     title="Return Request",
 *     description="Request body for return operations",
 *     @OA\Property(property="notes", type="string", example="Returning the book in good condition")
 * )
 * 
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     type="object",
 *     title="User Update Request",
 *     description="Request body for updating user information",
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"member", "admin"}, example="member")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     title="Login Request",
 *     description="Request body for user login",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123")
 * )
 * 
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     title="Register Request",
 *     description="Request body for user registration",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
 * )
 */
class OpenApiSchemas
{
    // This class exists solely for OpenAPI schema definitions
}