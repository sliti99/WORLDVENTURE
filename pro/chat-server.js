// WorldVenture Chat Server
// Implements Socket.io for real-time chat and Gemini for content filtering

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const { GoogleGenerativeAI } = require('@google/generative-ai');
const dotenv = require('dotenv');

// Load environment variables
dotenv.config();

// Initialize Express app
const app = express();
// Use CORS with credentials for our front-end origin, allowing default headers
app.use(cors({
    origin: 'http://localhost',
    credentials: true
}));

// Create HTTP server
const server = http.createServer(app);

// Configure Socket.IO with simpler CORS settings
const io = socketIo(server, {
    cors: {
        origin: 'http://localhost',
        credentials: true
    }
});

// Initialize Gemini AI with API key from environment variables
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

// Store recent messages (in-memory for simplicity)
const recentMessages = [];
const MAX_MESSAGES = 100; // Limit memory usage

// Function to filter content using Gemini
async function filterContent(message, userName) {
    try {
        // Configure the model
        // Use a supported model for content moderation
        const model = genAI.getGenerativeModel({ model: "Gemini 1.5 Flash" });
        
        // Create the prompt for content filtering
        const prompt = `
        You are a content moderation system for a family-friendly chat. 
        Analyze the following message and determine if it contains any inappropriate content:
        
        User: ${userName}
        Message: "${message}"
        
        Respond only with either "safe" or "blocked" and no other text.
        If the message contains profanity, hate speech, sexual content, threats, 
        or any other harmful content, respond with "blocked".
        Otherwise, respond with "safe".
        `;
        
        // Generate content
        const result = await model.generateContent(prompt);
        const response = result.response.text().trim().toLowerCase();
        
        console.log(`Content filter response for "${message}": ${response}`);
        
        return response === "safe";
    } catch (error) {
        console.error("Gemini API error:", error);
        // Allow message in case of API error to prevent service disruption
        return true;
    }
}

// Initialize Socket.IO connection
io.on('connection', (socket) => {
    console.log('New client connected');
    
    // Send recent message history to new connections
    socket.emit('message_history', recentMessages);
    
    // Handle new message
    socket.on('send_message', async (data) => {
        console.log('Message received:', data);
        
        // Apply content filtering using Gemini for all users
        const isSafe = await filterContent(data.message, data.user.name);
        
        if (!isSafe) {
            console.log('Message blocked due to inappropriate content');
            socket.emit('message_blocked');
            return;
        }
        
        // Add message to recent messages
        recentMessages.push(data);
        if (recentMessages.length > MAX_MESSAGES) {
            recentMessages.shift(); // Remove oldest message when limit reached
        }
        
        // Broadcast message to all clients
        io.emit('new_message', data);
    });
    
    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('Client disconnected');
    });
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.send('Chat server is running');
});

// Start server
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Chat server running on port ${PORT}`);
    console.log(`Content filtering enabled using Gemini AI`);
});

// Cleanup function to periodically remove old messages (every 30 minutes)
setInterval(() => {
    const oneHourAgo = new Date();
    oneHourAgo.setHours(oneHourAgo.getHours() - 1);
    
    // Keep only messages from the last hour
    const filteredMessages = recentMessages.filter(msg => 
        new Date(msg.timestamp) > oneHourAgo
    );
    
    // Replace the array with filtered messages
    recentMessages.length = 0;
    recentMessages.push(...filteredMessages);
    
    console.log(`Cleaned up chat history. Kept ${recentMessages.length} recent messages.`);
}, 30 * 60 * 1000); // 30 minutes in milliseconds