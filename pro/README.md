# WorldVenture Blog System

A modern, responsive blog system for the WorldVenture travel platform with Facebook-like interaction features.

## Features

- **Complete CRUD Functionality**:
  - Create, read, update, and delete blog posts
  - Comment system with real-time updates
  - Reaction system for posts and comments

- **Role-Based Access Control**:
  - Admin: Full access to all features, including post management
  - User: Can create posts, comment, and react
  - Visitor: Read-only access to content

- **Facebook-like UI/UX**:
  - Responsive, modern interface
  - Real-time post updates
  - Interactive reactions and comments

- **Security**:
  - Input validation on all forms
  - PDO with prepared statements for database queries
  - Role-based permissions enforcement

## Technical Implementation

### MVC Architecture

The project follows the MVC (Model-View-Controller) pattern:

- **Models**: Handle data, business logic, and database interactions
- **Views**: Present data and handle user interface
- **Controllers**: Process user requests and coordinate between models and views

### Directory Structure

```
/blog part/
    /config/         # Configuration files and authentication
    /controllers/    # Controller logic
    /models/         # Data models and database interactions
    /views/          # Frontend templates

/main_backoffice/   # Admin panel assets
/main_front/        # Frontend assets
```

### Post and Comment System

- Posts include title, content, and reactions
- Comments can be added to posts with reactions
- User feedback via notifications and UI animations

### Authentication

- Email and password login
- Role-based session management
- Secure logout functionality

## Installation

1. Import the SQL file `worldventure.sql` to your MySQL database
2. Configure database credentials in `blog part/config/config.php`
3. Ensure your web server can access the project directory
4. Navigate to the main page via your web browser

## User Credentials

- **Admin**: admin@example.com / password
- **User**: user@example.com / password

## Technologies Used

- PHP 8.2+
- MySQL/MariaDB
- HTML5, CSS3, JavaScript
- FontAwesome for icons
- Modern CSS features (Grid, Flexbox, Animations)

## JavaScript Form Validation

All forms implement client-side validation for improved user experience:
- Post creation form validates title and content length
- Comment form validates content length
- Login form validates email format and password requirements

## Developer Notes

- The system uses PDO for database connectivity
- Singleton pattern for database connection management
- Prepared statements for SQL injection prevention
- HTML escaping for XSS prevention

# Real-time Features

The system includes a real-time chat component powered by Socket.io:

- Facebook-like chat widget in the bottom-right corner
- Message filtering with Google's Gemini 2.5 Flash API to block inappropriate content
- Support for multiple languages (English, French, and Tunisian dialect)
- Role-based message styling and permissions
- Ephemeral messages that clear after 8 hours of inactivity

## Setting Up the Chat Server

1. Install Node.js dependencies:
   ```bash
   cd /path/to/worldventure
   npm install
   ```

2. Configure your Gemini API key:
   - Rename `.env.example` to `.env`
   - Add your Google Gemini API key to the `.env` file

3. Start the chat server:
   ```bash
   npm start
   ```

4. For development with auto-restart:
   ```bash
   npm run dev
   ```

## Chat Server Features

- **Content Filtering**: Uses Google's Gemini 2.5 Flash to detect and block inappropriate content
- **Admin Bypass**: Admin messages skip content filtering
- **Room-Based Chat**: Current implementation uses a single 'general' room
- **Message Persistence**: Messages are stored in memory with a configurable limit
- **Ephemeral Messages**: Chat clears after 8 hours of inactivity