<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Library Management System API",
 *     version="1.0.0",
 *     description="A comprehensive REST API for managing library operations including books, users, categories, and borrowing workflows. This API provides secure authentication, role-based access control, and complete CRUD operations for all library entities.",
 *     termsOfService="https://example.com/terms",
 *     @OA\Contact(
 *         email="support@libraryapi.com",
 *         name="Library API Support"
 *     ),
 *     @OA\License(
 *         name="MIT License",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.librarymanagement.com",
 *     description="Production Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum authentication. Use the token received from login endpoint."
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Books",
 *     description="Book management operations including search and categorization"
 * )
 * 
 * @OA\Tag(
 *     name="Categories",
 *     description="Book category management operations"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="User management operations (Admin only)"
 * )
 * 
 * @OA\Tag(
 *     name="Borrows",
 *     description="Book borrowing workflow management including requests and approvals"
 * )
 * 
 * @OA\Tag(
 *     name="Returns",
 *     description="Book return workflow management including requests and approvals"
 * )
 */
abstract class Controller
{
    //
}
