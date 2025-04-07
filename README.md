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
- **Database**:
  - Secure interactions using PDO with prepared statements.

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Server (e.g., XAMPP)

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

## Usage
- Access the FrontOffice at `http://localhost/web.pro/web V2/pro/main_front/index.html`.
- Access the BackOffice at `http://localhost/web.pro/web V2/pro/main_backoffice/index.html`.

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
WorldVenture Development Team
