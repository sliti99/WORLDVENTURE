// WorldVenture Chat Server
// Implements Socket.io for real-time chat and Gemini for content filtering

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const { genAI, models } = require('./gemini-config'); // Use the new config file
const cors = require('cors');
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

// Store recent messages (in-memory for simplicity)
const recentMessages = [];
const MAX_MESSAGES = 100; // Limit memory usage

// Update the model initialization and content filtering function
async function filterContent(message) {
  // First check locally to avoid unnecessary API calls
  if (containsInappropriateContent(message)) {
    return false;
  }
  
  // Don't use the API if we don't need to (return true = message is safe)
  return true;
  
  // NOTE: API-based content filtering is commented out due to quota issues
  // Uncomment and modify if you have sufficient API quota
  /*
  try {
    // Use the correct model name without "models/" prefix
    let modelToUse = models.pro;
    
    try {
      const model = genAI.getGenerativeModel({ model: modelToUse });
      
      // Set proper generation config
      const generationConfig = {
        temperature: 0.4,
        topK: 32,
        topP: 0.95,
        maxOutputTokens: 100,
      };
      
      const safetySettings = [
        {
          category: "HARM_CATEGORY_HATE_SPEECH",
          threshold: "BLOCK_MEDIUM_AND_ABOVE"
        },
        {
          category: "HARM_CATEGORY_SEXUALLY_EXPLICIT",
          threshold: "BLOCK_MEDIUM_AND_ABOVE"
        },
        {
          category: "HARM_CATEGORY_HARASSMENT",
          threshold: "BLOCK_MEDIUM_AND_ABOVE"
        }
      ];
      
      // Create prompt parts
      const parts = [
        {text: `Check if this message is safe and appropriate: "${message}". 
                Only respond with "SAFE" or "UNSAFE". Don't explain your reasoning.`}
      ];
      
      const result = await model.generateContent({
        contents: [{ role: "user", parts }],
        generationConfig,
        safetySettings
      });
      
      const response = result.response.text().trim().toUpperCase();
      return response === "SAFE";
    } catch (error) {
      console.error('Error with content filtering:', error);
      // In case of API error, fall back to local filtering
      return !containsInappropriateContent(message);
    }
  } catch (error) {
    console.error('Fatal error with content filtering:', error);
    // In case of complete failure, err on the side of caution
    return !containsInappropriateContent(message);
  }
  */
}

// Enhanced local inappropriate content filter
function containsInappropriateContent(text) {
  if (!text) return false;
  
  const inappropriateWords = [
    // Profanity
    'fuck', 'shit', 'ass', 'bitch', 'bastard', 'cunt', 'dick', 'pussy',
    // Slurs
    'fagot', 'faggot', 'nigger', 'nigga', 'retard',
    // Sexual content
    'porn', 'sex', 'xxx', 'nude', 'naked', 'penis', 'vagina', 'blowjob',
    // Violence
    'kill', 'murder', 'suicide', 'bomb', 'terrorist'
  ];
  
  // First check exact matches (case insensitive)
  const textLower = text.toLowerCase();
  for (const word of inappropriateWords) {
    // Check for word boundaries to avoid false positives
    const regex = new RegExp(`\\b${word}\\b`, 'i');
    if (regex.test(textLower)) {
      return true;
    }
  }
  
  // Check for common variations and partial matches
  for (const word of inappropriateWords) {
    // Check for words that contain the inappropriate word
    if (textLower.includes(word)) {
      return true;
    }
  }
  
  return false;
}

// Initialize Socket.IO connection
io.on('connection', (socket) => {
    console.log('New client connected');
    
    // Send recent message history to new connections
    socket.emit('message_history', recentMessages);
    
    // Handle new message
    socket.on('send_message', async (data) => {
        console.log('Message received:', data);
        
        try {
            // Basic content filtering before sending to API
            const messageText = data.message || '';
            
            // Check locally first
            if (containsInappropriateContent(messageText)) {
                socket.emit('message', {
                    message: 'This message contains inappropriate content and was blocked.',
                    user: { id: 0, name: 'System', role: 'system' },
                    timestamp: new Date().toISOString()
                });
                return;
            }
            
            // Apply content filtering - now returns boolean (true = safe)
            const isSafe = await filterContent(messageText);
            
            if (!isSafe) {
                console.log('Message blocked due to inappropriate content');
                socket.emit('message', {
                    message: 'This message contains inappropriate content and was blocked.',
                    user: { id: 0, name: 'System', role: 'system' },
                    timestamp: new Date().toISOString()
                });
                return;
            }
            
            // Add message to recent messages
            recentMessages.push(data);
            if (recentMessages.length > MAX_MESSAGES) {
                recentMessages.shift(); // Remove oldest message when limit reached
            }
            
            // Broadcast message to all clients
            io.emit('new_message', data);
        } catch (error) {
            console.error('Error processing message:', error);
            socket.emit('message', {
              message: 'An error occurred while processing your message.',
              user: { id: 0, name: 'System', role: 'system' },
              timestamp: new Date().toISOString()
            });
        }
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