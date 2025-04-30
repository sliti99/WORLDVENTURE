# WorldVenture Travel Blog Platform

A complete travel blog platform with real-time chat, user authentication, and content management.

## Features

- Blog system with posts and comments
- User authentication with role-based permissions
- Admin dashboard for content management
- Real-time chat with content filtering powered by Gemini AI
- Responsive design for all devices

## Setup Instructions

### Database Setup
1. Import the `worldventure.sql` file into your MySQL database
2. Update database connection settings in `blog part/config/config.php`

### Chat Server Setup
1. Make sure Node.js is installed (v14+ recommended)
2. Create a `.env` file with your Gemini API key:
   ```
   GEMINI_API_KEY=your_actual_api_key_here
   PORT=3000
   ```
3. Install dependencies: `npm install`
4. Start the chat server: `npm start` or simply run `start-chat-server.bat`
   - For development with auto-restart: `npm run dev`

### Web Server Setup
1. Place the project folder in your web server directory (e.g., xampp/htdocs/)
2. Access the site at: http://localhost/web.pro/inst/pro/pro/main_front/

## Chat System

The real-time chat feature uses:
- Socket.io for real-time communication
- Google's Gemini AI for content filtering
- Automatic message cleanup after periods of inactivity

### Content Filtering

The chat server uses Gemini AI to filter inappropriate content:
- Messages from regular users are checked for inappropriate content
- Admin messages bypass the filter
- The system consistently filters content by creating a new Gemini model instance for each request
- If the Gemini API fails, messages are allowed through to prevent service disruption

## User Roles

- **Visitor**: Can view posts and chat but cannot create content
- **User**: Can create posts, comments, and participate in chat
- **Admin**: Full access to create, edit, and delete all content

## Troubleshooting

If the chat server isn't working properly:
1. Check that Node.js is installed correctly
2. Verify your Gemini API key in the `.env` file
3. Make sure all dependencies are installed with `npm install`
4. Check the console for any error messages
5. Ensure port 3000 is available on your system

For more assistance, contact the WorldVenture development team.