# WORLDVENTURE
# WorldVenture Project

## Project Overview
WorldVenture is a web application designed to provide users with an engaging platform for exploring travel destinations, managing blog posts, and accessing administrative tools. The project adheres to the MVC (Model-View-Controller) architecture and integrates both FrontOffice and BackOffice functionalities.

## Features
- **FrontOffice**:
  - Dynamic blog section displaying the latest posts.
  - User-friendly navigation and responsive design.
- **BackOffice**:
  - Blog management tools for creating and deleting posts.
  - Dashboard with statistics and recent activities.
- **Database**

# WorldVenture Project - Blog Module

This project implements a blog feature for the WorldVenture website.

## Features

*   **Posts:** Create, Read, Update, Delete (CRUD) blog posts (Admin).
*   **Comments:** Add comments to posts (Logged-in Users), Delete comments (Admin), Read comments (All).
*   **Reactions:** Users can react to posts and comments. The system tracks reactions to prevent duplicate reactions from the same user.
*   **Role-Based Access:** Different capabilities for visitors (read-only), users (read, comment, react), and admins (full access).
*   **MVC Architecture:** Follows the Model-View-Controller pattern.
*   **Database:** Uses PDO for MySQL database interaction.
*   **Frontend/Backend:** Separate views for public blog reading and admin management.
*   **Input Validation:** Client-side JavaScript validation on forms with server-side validation backup.

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Server (e.g., XAMPP)

## Setup

1.  **Database:**
    *   Ensure you have a MySQL database named `worldventure`.
    *   Import the table structures from `worldventure.sql` including the reactions_log table needed for tracking user reactions.
    *   Update database credentials (`localhost`, `worldventure`, `root`, `""`) in `blog part/models/model.php` if they differ.
2.  **Web Server:**
    *   Place the project files in your web server's document root (e.g., `htdocs` for XAMPP).
    *   Access the project through your browser (e.g., `http://localhost/web.pro/inst/pro/main_front/index.html` for the front page, `http://localhost/web.pro/inst/pro/main_backoffice/index.html` for the admin dashboard).
3.  **User Roles:**
    *   The system supports three user roles: visitor, user, and admin
    *   For testing, you can change the default role in `blog part/controllers/controller.php`

## Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```
2. Import the `worldventure.sql` file into your MySQL database.
3. Update the database credentials in `pro/blog part/models/model.php`:
   ```php
   $this->pdo = new PDO("mysql:host=localhost;dbname=worldventure", "root", "");
   ```
4. Start the Apache server and navigate to the project directory in your browser.

## Usage local !
- Access the FrontOffice at `http://localhost/web.pro/web V2/pro/main_front/index.html`.
- Access the BackOffice at `http://localhost/web.pro/web V2/pro/main_backoffice/index.html`.

## Structure

*   `main_front/`: Public facing website files.
*   `main_backoffice/`: Admin dashboard files.
*   `blog part/`: Contains the MVC components for the blog feature.
    *   `controllers/controller.php`: Handles requests, interacts with the model, selects views.
    *   `models/model.php`: Handles database interactions (using PDO).
    *   `views/`: Contains the PHP/HTML templates (frontend, backend, post details, edit form).
*   `worldventure.sql`: Database schema definitions.
*   `README.md`: This file.

## TODO / Improvements

*   Implement a secure user authentication (login/logout) system.
*   Refine role permissions (e.g., allow users to edit/delete their *own* comments).
*   Enhance input validation (server-side validation is crucial).
*   Improve slug generation for posts.
*   Add pagination for posts and comments.
*   Consider using a templating engine for views.
*   Write unit/integration tests.

## Contribution Guidelines
1. Fork the repository.
2. Create a new branch for your feature or bug fix:
   ```bash
   git checkout -b feature-name
   ```
3. Commit your changes with clear messages:
   ```bash
   git commit -m "Add feature-name"
   ```
4. Push to your branch and create a pull request.

## License
This project is licensed under the MIT License.

## Author
WorldVenture Development Team (aladin sliti,sourour sethom ,mariem zayeni,amal gharsa and yoser larbch)
