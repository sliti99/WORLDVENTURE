// WorldVenture Chat Server
// Implements Socket.io for real-time chat and Gemini for content filtering

const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const cors = require('cors');
const { GoogleGenerativeAI } = require("@google/generative-ai");
require('dotenv').config();

// Initialize Express
const app = express();
app.use(cors({
    origin: ["http://localhost", "http://127.0.0.1", "http://localhost:80", "http://localhost:3000"],
    credentials: true
}));

const server = http.createServer(app);

// Initialize Socket.io
const io = new Server(server, {
    cors: {
        origin: ["http://localhost", "http://127.0.0.1", "http://localhost:80", "http://localhost:3000"],
        credentials: true,
        methods: ["GET", "POST"]
    }
});

// Initialize Gemini AI
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY || "YOUR_API_KEY_HERE");
const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

// Chat storage (in memory)
const discussions = {
    general: {
        messages: [],
        settings: {
            maxMessages: 100,
            ephemeral: true,
            inactivityTimeout: 8 * 60 * 60 * 1000 // 8 hours
        }
    }
};

// Initialize timeout for ephemeral chats
let inactivityTimer;
function resetInactivityTimer() {
    const timeout = discussions.general.settings.inactivityTimeout;
    
    // Clear existing timer
    if (inactivityTimer) {
        clearTimeout(inactivityTimer);
    }
    
    // Set new timer
    inactivityTimer = setTimeout(() => {
        if (discussions.general.settings.ephemeral) {
            discussions.general.messages = [];
            io.to('general').emit('chat_cleared', {
                message: 'Chat has been cleared due to inactivity.'
            });
        }
    }, timeout);
}

// Check for inappropriate content using Gemini
async function checkBadWords(text) {
    try {
        const prompt = "Vérifie si ce texte contient des insultes en tunisien, français ou anglais. Réponds uniquement par 'true' ou 'false': " + text;
        const result = await model.generateContent(prompt);
        const response = await result.response;
        const responseText = response.text().toLowerCase();
        return responseText.includes("true");
    } catch (error) {
        console.error("Gemini API error:", error);
        // Fallback to safe mode if API fails
        return text.length > 50; // Temporary fallback to block longer messages if API fails
    }
}

// Socket.io connection handling
io.on('connection', (socket) => {
    console.log('User connected:', socket.id);
    
    // Join the general room
    socket.join('general');
    
    // Send the last 50 messages to the newly connected user
    socket.emit('chat_history', discussions.general.messages.slice(-50));
    
    // Handle new messages
    socket.on('send_message', async (data) => {
        console.log('Message received:', data.message);
        
        // Reset inactivity timer
        resetInactivityTimer();
        
        // Check if the user is an admin (no content filtering for admins)
        const isAdmin = data.user && data.user.role === 'admin';
        
        if (isAdmin) {
            // Admin messages bypass filtering
            const messageData = {
                message: data.message,
                user: data.user,
                timestamp: new Date().toISOString()
            };
            
            discussions.general.messages.push(messageData);
            
            // Trim messages array if it exceeds max length
            if (discussions.general.messages.length > discussions.general.settings.maxMessages) {
                discussions.general.messages = discussions.general.messages.slice(-discussions.general.settings.maxMessages);
            }
            
            // Broadcast to all clients in the room
            io.to('general').emit('new_message', messageData);
        } else {
            // Regular user messages need to be filtered
            const containsBadWords = await checkBadWords(data.message);
            
            if (containsBadWords) {
                // Send blocked message notification only to the sender
                socket.emit('message_blocked');
                console.log('Message blocked due to inappropriate content');
            } else {
                const messageData = {
                    message: data.message,
                    user: data.user,
                    timestamp: new Date().toISOString()
                };
                
                discussions.general.messages.push(messageData);
                
                // Trim messages array if it exceeds max length
                if (discussions.general.messages.length > discussions.general.settings.maxMessages) {
                    discussions.general.messages = discussions.general.messages.slice(-discussions.general.settings.maxMessages);
                }
                
                // Broadcast to all clients in the room
                io.to('general').emit('new_message', messageData);
            }
        }
    });
    
    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
    });
});

// API endpoint to check server status
app.get('/status', (req, res) => {
    res.json({ status: 'online' });
});

// Start the server
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`WorldVenture Chat Server running on port ${PORT}`);
    console.log(`Gemini API is ${process.env.GEMINI_API_KEY ? 'configured' : 'NOT configured - please set GEMINI_API_KEY'}`);
});

// Handle process shutdown
process.on('SIGINT', () => {
    console.log('Shutting down chat server...');
    server.close(() => {
        console.log('Server closed.');
        process.exit(0);
    });
});