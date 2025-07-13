# Library Management System API

This is a Laravel-based API for a Library Management System. This application is designed to manage books, users, and borrowing processes within a library. It provides a complete set of endpoints for all required functionalities, ensuring a smooth and efficient library management experience.

## Key Features

- **User Authentication**: Secure registration and login for users.
- **Role-Based Access Control**: Differentiates between `admin` and `member` roles, each with specific permissions.
- **Book Management**: Comprehensive CRUD (Create, Read, Update, Delete) operations for books.
- **Category Management**: Organize books into categories with full CRUD functionalities.
- **Borrowing System**: A complete workflow for borrowing books, including:
  - Requesting a book borrow.
  - Admin approval or rejection of borrow requests.
  - Tracking book status (e.g., `borrowed`, `overdue`, `returned`).
  - Handling return requests and approvals.
- **Search Functionality**: Easily search for books in the library's collection.
- **API Documentation**: Detailed API documentation powered by OpenAPI (Swagger) for clear and easy-to-understand endpoint references.

## Getting Started

To get the application up and running, follow these steps:

### Prerequisites

- PHP >= 8.2
- Composer
- Laravel

### Installation

1.  **Clone the repository**:
    ```bash
    git clone <repository-url>
    cd <repository-directory>
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    ```

3.  **Set up the environment**:
    - Copy the example environment file:
      ```bash
      cp .env.example .env
      ```
    - Generate an application key:
      ```bash
      php artisan key:generate
      ```

4.  **Set up the database**:
    - Run migrations and seed the database with initial data (including an admin user):
      ```bash
      php artisan migrate --seed
      ```

5.  **Run the application**:
    ```bash
    php artisan serve
    ```
    The API will be accessible at `http://127.0.0.1:8000`.

## API Documentation

The API is documented using OpenAPI (Swagger). To view the interactive documentation, start the development server and navigate to `/api/documentation`.

To regenerate the documentation after making changes to the annotations, run:
```bash
php artisan l5-swagger:generate
```

## License

This Library Management System API is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
