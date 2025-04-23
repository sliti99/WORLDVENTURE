# WorldVenture Blog System

A Facebook-like blog system built with PHP using an MVC-like architecture, PDO for database interaction, and JavaScript for dynamic features and validation.

## Features

-   **MVC Architecture:** Organized structure with Controllers, Models, and Views.
-   **Role-Based Access Control:**
    -   **Visitor:** Can view posts and comments.
    -   **User:** Can view, create posts, comment on posts, and react to posts.
    -   **Admin:** Full CRUD permissions (Create, Read, Delete posts), can comment and react. (Edit functionality can be added).
-   **Dynamic Post Feed (`blog_frontend.php`):**
    -   AJAX loading of posts.
    -   Real-time post creation (for Users/Admins) - post appears instantly without page reload.
    -   AJAX-based reactions (like button) for posts (Users/Admins).
    -   AJAX-based post deletion (Admins).
-   **Post Details Page (`post_details.php`):**
    -   Displays full post content and comments.
    -   Allows users/admins to add comments (via standard form submission).
    -   AJAX-based reactions for the main post.
-   **Admin Backend (`blog_backend.php`):**
    -   View all posts.
    -   Create new posts (via standard form submission).
    -   Delete posts.
-   **Database:** Uses PDO for secure database operations (MySQL/MariaDB). Includes tables for `posts`, `comments`, `users`, `reactions_log`, etc.
-   **Security:**
    -   PDO prepared statements to prevent SQL injection.
    -   `htmlspecialchars()` used for output escaping (XSS prevention).
    -   Basic server-side permission checks based on user roles.
-   **Validation:** Client-side JavaScript validation for post and comment forms. Server-side validation in the controller.
-   **UI/UX:** Frontend aims for a dynamic, Facebook-like experience for post creation and interaction.

## Installation

1.  **Environment:** Set up a local web server environment (e.g., XAMPP, MAMP, WAMP) with PHP and MySQL/MariaDB.
2.  **Database:** Import the `worldventure (2).sql` file into your MySQL/MariaDB database. This will create the necessary tables (`posts`, `comments`, `users`, `reactions_log`, etc.) and add some sample data.
3.  **Configuration:**
    -   Verify the database connection details (database name, username, password) in `pro/blog part/models/model.php`. Currently set to:
        ```php
        $this->pdo = new PDO("mysql:host=localhost;dbname=worldventure", "root", "", [ ... ]);
        ```
    -   Adjust if your database credentials differ.
4.  **Files:** Place the project files (`pro/` directory and `README.md`, `worldventure (2).sql`) in your web server's document root (e.g., `htdocs` for XAMPP).
5.  **Permissions:** Ensure your web server has write permissions if file uploads were to be implemented later.

## Usage

1.  **Frontend Blog:** Access `pro/blog part/views/blog_frontend.php` in your browser.
    -   You can switch between 'Visitor', 'User', and 'Admin' roles using the buttons at the top for testing purposes.
    -   Visitors see posts.
    -   Users/Admins see posts and a creation form. They can post, react, and comment.
    -   Admins see a delete button on posts.
2.  **Post Details:** Click "Read More" on a post to navigate to `pro/blog part/views/post_details.php?id=...`.
    -   View full post and comments.
    -   Users/Admins can add comments using the form.
3.  **Backend Management:** Access `pro/blog part/views/blog_backend.php`.
    -   This interface is currently hardcoded for the 'admin' role.
    -   View all posts, create new posts, and delete posts.

## Project Structure (Simplified)

```
pro/
├── blog part/
│   ├── controllers/
│   │   └── controller.php   # Handles logic, interacts with model & view
│   ├── models/
│   │   └── model.php        # Handles database interactions (PDO)
│   └── views/
│       ├── blog_backend.php # Admin interface view
│       ├── blog_frontend.php# Public blog feed view (dynamic)
│       └── post_details.php # Single post and comments view
├── main_backoffice/         # Styles/assets for backend
│   ├── css/style.css
│   └── images/
├── main_front/              # Styles/assets for frontend
│   ├── style.css
│   └── logo.png
└── ... (other files/folders)

worldventure (2).sql         # Database dump
README.md                    # This file
```

## Future Improvements

-   Implement User Authentication (Login/Registration).
-   Add Edit functionality for posts (for authors/admins).
-   Implement reactions for comments.
-   Add image uploads for posts.
-   Refine UI/UX further.
-   Implement pagination for posts and comments.
-   Enhance security measures (CSRF protection, more robust validation).
-   Improve slug generation.
-   Replace testing role switcher with actual login system.
