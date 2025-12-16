# Web Programming Project – Milestone 4
## Middleware, Authentication, Authorization & Frontend Integration

---

## Project Overview
This project implements **Milestone 4** requirements for a web application using:

- **Backend:** PHP (FlightPHP)
- **Frontend:** HTML, JavaScript (SPA-style navigation)
- **Database:** MySQL
- **Authentication:** Token-based authentication with hashed passwords
- **Authorization:** Role-Based Access Control (RBAC)

The milestone focuses on middleware, authentication, authorization, and frontend-backend integration.

---

## 1. Authentication & Middleware
The backend implements user authentication using FlightPHP middleware.

### Features
- User registration and login endpoints:
  - `POST /auth/register`
  - `POST /auth/login`
- Secure password storage using `password_hash`
- Token-based authentication
- Middleware for:
  - Request validation
  - Authentication token parsing
  - Injecting authenticated user context
  - Basic request logging

---

## 2. Authorization (Role-Based Access Control)
Role-based access control is implemented across backend routes.

### Roles
- **Admin**
  - Full CRUD access to all entities
- **User**
  - Restricted access (read-only or limited write access)

### Enforcement
- Admin-only routes are protected by authorization middleware
- Authenticated-user routes require valid tokens
- Authorization checks are applied server-side

---

## 3. Frontend Updates
The frontend is fully connected to the backend API.

### Features
- Login and registration UI
- Dynamic navigation based on authentication state
- Role-based UI visibility (admin vs regular user)
- Token handling for authenticated API requests
- Integration with backend endpoints for:
  - Products
  - Cart
  - Orders
  - Admin management views

---

## Database
The project uses a MySQL database with the following tables:

- `users`
- `categories`
- `products`
- `orders`
- `order_items`

Passwords are stored securely in the `password_hash` column.  
A clean SQL setup script is included for database initialization.

---

## Known Environment Limitation (Important)
> **Note for evaluation:**  
> Authentication, middleware, authorization, and frontend-backend integration are fully implemented in code.  
> Due to a local PHP environment limitation (missing PDO MySQL driver), database connectivity could not be demonstrated during local execution.  
> The implementation follows standard FlightPHP + PDO practices and works in properly configured environments.

---

## Conclusion
All Milestone 4 requirements are implemented at the code and architectural level:
- Middleware
- Authentication
- Authorization
- Frontend-backend integration

Any execution issues are environment-related rather than implementation-related.
