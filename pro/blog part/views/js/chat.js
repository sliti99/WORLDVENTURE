/**
 * Enhanced Chat System
 * Provides real-time chat functionality with session validation and content moderation
 */

// Chat state
const chatState = {
    lastCheck: new Date().toISOString(),
    unreadCount: 0,
    isMinimized: false,
    lastMessageId: 0,
    userActivity: {}
};

// DOM Elements
let chatContainer;
let chatMessages;
let chatInput;
let sendButton;
let chatHeader;
let toggleButton;
let chatNotification;

// Initialize chat functionality
function initChat() {
    // Get DOM elements
    chatContainer = document.querySelector('.chat-container');
    chatMessages = document.getElementById('chatMessages');
    chatInput = document.getElementById('chatInput');
    sendButton = document.getElementById('sendButton');
    chatHeader = document.querySelector('.chat-header');
    toggleButton = document.querySelector('.toggle-chat');
    chatNotification = document.querySelector('.chat-notification');
    
    if (!chatContainer) {
        console.warn('Chat container not found. Chat functionality disabled.');
        return;
    }
    
    // Setup event listeners
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleChat);
    }
    
    if (chatHeader) {
        chatHeader.addEventListener('click', function(e) {
            if (e.target !== sendButton) {
                toggleChat();
            }
        });
    }
    
    if (chatInput) {
        chatInput.addEventListener('input', validateChatInput);
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !sendButton.disabled) {
                sendChatMessage();
            }
        });
    }
    
    if (sendButton) {
        sendButton.addEventListener('click', sendChatMessage);
    }
    
    // Check for new messages periodically
    loadChatMessages();
    pingActivity();
    
    // Set timers for periodic updates
    setInterval(checkNewMessages, 10000); // Check for new messages every 10 seconds
    setInterval(pingActivity, 30000); // Ping activity every 30 seconds
    setInterval(loadChatMessages, 15000); // Refresh messages every 15 seconds
}

// Toggle chat minimization
function toggleChat() {
    if (!chatContainer) return;
    
    chatState.isMinimized = !chatState.isMinimized;
    chatContainer.classList.toggle('minimized', chatState.isMinimized);
    
    if (!chatState.isMinimized) {
        // Reset notification count when opening chat
        chatState.unreadCount = 0;
        updateNotificationBadge();
        
        // Load latest messages
        loadChatMessages();
        
        // Focus input
        if (chatInput) {
            chatInput.focus();
        }
    }
}

// Load chat messages
function loadChatMessages() {
    fetch('../controllers/chat_api.php?action=get_messages')
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.messages && chatMessages) {
                // If chat is minimized, don't update DOM but track unread messages
                if (chatState.isMinimized && data.messages.length > 0) {
                    const newMessages = data.messages.filter(msg => 
                        !document.getElementById(`chat-msg-${msg.id}`) && 
                        msg.id > chatState.lastMessageId);
                        
                    if (newMessages.length > 0) {
                        chatState.unreadCount += newMessages.length;
                        updateNotificationBadge();
                        
                        // Update last message ID
                        chatState.lastMessageId = Math.max(...data.messages.map(m => m.id));
                    }
                    return;
                }
                
                // Update chat messages
                updateChatMessages(data.messages);
                
                // Reset notification count since messages are now visible
                chatState.unreadCount = 0;
                updateNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error loading chat messages:', error);
        });
}

// Update chat messages in the DOM
function updateChatMessages(messages) {
    if (!chatMessages) return;
    
    // Get IDs of existing messages to prevent duplicates
    const existingMessageIds = new Set(
        Array.from(chatMessages.querySelectorAll('.chat-message'))
            .map(el => el.id?.replace('chat-msg-', ''))
            .filter(Boolean)
            .map(Number)
    );
    
    // Track if we've added any new messages
    let hasNewMessages = false;
    
    // Add each message to the chat (if not already present)
    messages.forEach(message => {
        const messageId = parseInt(message.id);
        
        // Skip if message already exists in the DOM
        if (existingMessageIds.has(messageId)) {
            return;
        }
        
        // Create and append new message
        const messageElement = createMessageElement(message);
        chatMessages.appendChild(messageElement);
        hasNewMessages = true;
        
        // Update last message ID if needed
        if (messageId > chatState.lastMessageId) {
            chatState.lastMessageId = messageId;
        }
    });
    
    // Scroll to bottom only if we added new messages or chat is empty
    if (hasNewMessages || chatMessages.childElementCount === messages.length) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Create a message element
function createMessageElement(message) {
    const messageElement = document.createElement('div');
    messageElement.className = 'chat-message';
    messageElement.id = `chat-msg-${message.id}`;
    
    // Check if message is from current user
    const currentUserId = getCurrentUserId();
    if (parseInt(message.user_id) === currentUserId) {
        messageElement.classList.add('mine');
    } else {
        messageElement.classList.add('other');
    }
    
    // Add admin styling if applicable
    if (message.user_role === 'admin') {
        messageElement.classList.add('admin');
    }
    
    const timestamp = new Date(message.created_at);
    const time = timestamp.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageElement.innerHTML = `
        <div class="message-header">
            <span class="message-author">${escapeHtml(message.user_name)}</span>
            ${message.user_role === 'admin' ? '<span class="message-badge admin">Admin</span>' : ''}
            <span class="message-time">${time}</span>
        </div>
        <div class="message-content">${escapeHtml(message.content)}</div>
    `;
    
    return messageElement;
}

// Send a chat message
function sendChatMessage() {
    if (!chatInput || !sendButton) return;
    
    const message = chatInput.value.trim();
    if (message === '') return;
    
    // Disable send button during sending
    sendButton.disabled = true;
    
    // Check if user is logged in
    checkSession().then(session => {
        if (!session.isLoggedIn) {
            showToast('Please log in to participate in the chat', true);
            sendButton.disabled = false;
            return;
        }
        
        // Validate content client-side first
        if (!validateChatMessage(message)) {
            showToast('Your message contains inappropriate content', true);
            sendButton.disabled = false;
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('message', message);
        
        // Send message to server
        fetch('../controllers/chat_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                chatInput.value = '';
                
                // Add the new message to the chat
                if (data.message && chatMessages) {
                    const messageElement = createMessageElement(data.message);
                    chatMessages.appendChild(messageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
                
                // Update last check time
                chatState.lastCheck = new Date().toISOString();
            } else {
                showToast(data.message || 'Failed to send message', true);
            }
        })
        .catch(error => {
            console.error('Error sending chat message:', error);
            showToast('Error sending message', true);
        })
        .finally(() => {
            // Re-enable send button
            sendButton.disabled = false;
        });
    });
}

// Validate chat input
function validateChatInput() {
    if (!chatInput || !sendButton) return;
    
    const message = chatInput.value.trim();
    sendButton.disabled = !validateChatMessage(message);
}

// Validate chat message content
function validateChatMessage(message) {
    // Basic validation
    if (!message || message.trim() === '') {
        return false;
    }
    
    if (message.length > 500) {
        return false;
    }
    
    // Check for profanity (client-side)
    if (containsProfanity(message)) {
        return false;
    }
    
    return true;
}

// Check for new messages
function checkNewMessages() {
    if (chatState.isMinimized) {
        fetch(`../api/chat_activity.php?action=check_new&since=${encodeURIComponent(chatState.lastCheck)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.new_messages > 0) {
                    chatState.unreadCount += data.new_messages;
                    updateNotificationBadge();
                    
                    // Update last check time
                    chatState.lastCheck = data.timestamp || new Date().toISOString();
                }
            })
            .catch(error => {
                console.error('Error checking for new messages:', error);
            });
    } else {
        // Update last check time if chat is open
        chatState.lastCheck = new Date().toISOString();
    }
}

// Update notification badge
function updateNotificationBadge() {
    if (!chatNotification) return;
    
    if (chatState.unreadCount > 0) {
        chatNotification.textContent = chatState.unreadCount > 99 ? '99+' : chatState.unreadCount;
        chatNotification.style.display = 'flex';
    } else {
        chatNotification.style.display = 'none';
    }
}

// Ping user activity
function pingActivity() {
    fetch('../api/chat_activity.php?action=ping')
        .then(response => response.json())
        .then(data => {
            // Update user activity if needed
            if (data.success && data.user) {
                chatState.userActivity = data.user;
            }
        })
        .catch(error => {
            console.error('Error pinging activity:', error);
        });
}

// Check user session status
function checkSession() {
    return fetch('../api/check_session.php')
        .then(response => response.json())
        .then(data => {
            return data;
        })
        .catch(error => {
            console.error('Error checking session:', error);
            return { isLoggedIn: false };
        });
}

// Get current user ID
function getCurrentUserId() {
    // Try to get from DOM data attribute
    const userIdAttr = document.body.dataset.userId;
    if (userIdAttr) {
        return parseInt(userIdAttr);
    }
    
    // Fall back to session check
    return 0;
}

// Utility: Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Utility: Show toast notification
function showToast(message, isError = false) {
    // Check if toast function exists in global scope
    if (typeof window.showToast === 'function') {
        window.showToast(message, isError);
    } else {
        // Create simple alert if no toast function is available
        if (isError) {
            console.error(message);
        } else {
            console.log(message);
        }
    }
}

// Initialize on DOM loaded
document.addEventListener('DOMContentLoaded', initChat);