/**
 * Form Validation Library
 * Provides simple, consistent validation for all forms in the application
 * Enhanced with server-side FilterService integration for content moderation
 */

/**
 * Simple JavaScript form validation for WorldVenture blog
 * Provides client-side validation for forms before submission
 */

// General validation functions
function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') {
        return `${fieldName} cannot be empty.`;
    }
    return null;
}

function validateMinLength(value, minLength, fieldName) {
    if (value.trim().length < minLength) {
        return `${fieldName} must be at least ${minLength} characters long.`;
    }
    return null;
}

function validateMaxLength(value, maxLength, fieldName) {
    if (value.trim().length > maxLength) {
        return `${fieldName} must be no more than ${maxLength} characters long.`;
    }
    return null;
}

// Enhanced profanity check - now with improved detection and server integration
function containsProfanity(text) {
    // Basic client-side check for immediate feedback
    const basicBadWords = [
        'fuck', 'shit', 'ass', 'bitch', 'dick', 'pussy', 'cunt', 'whore', 'bastard'
    ];
    
    // Check if any profane word appears in the text
    const lowerText = text.toLowerCase();
    for (const word of basicBadWords) {
        // Use word boundary to match whole words only
        const regex = new RegExp('\\b' + word + '\\b', 'i');
        if (regex.test(lowerText)) {
            return true;
        }
    }
    
    // Additional pattern checks for obfuscated profanity
    const patterns = [
        /f+\s*u+\s*c+\s*k+/i,
        /s+\s*h+\s*i+\s*t+/i,
        /b+\s*i+\s*t+\s*c+\s*h+/i
    ];
    
    for (const pattern of patterns) {
        if (pattern.test(lowerText)) {
            return true;
        }
    }
    
    return false;
}

// Server-side profanity check using the FilterService API
async function checkProfanityWithServer(text) {
    if (!text || text.trim() === '') {
        return { clean: true };
    }
    
    try {
        const response = await fetch('../api/check_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: text }),
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error checking content with server:', error);
        // Fall back to client-side check if server check fails
        return { 
            clean: !containsProfanity(text),
            error: error.message 
        };
    }
}

// Post form validation - enhanced with visual feedback
function validatePostForm() {
    const title = document.getElementById('postTitle')?.value || '';
    const content = document.getElementById('postContent')?.value || '';
    const titleError = document.getElementById('titleError');
    const contentError = document.getElementById('contentError');
    const postButton = document.getElementById('postButton');
    
    // Reset errors
    if (titleError) {
        titleError.textContent = '';
        titleError.style.display = 'none';
    }
    if (contentError) {
        contentError.textContent = '';
        contentError.style.display = 'none';
    }
    
    // Validate title
    let isValid = true;
    const titleValidation = validateRequired(title, 'Title') || 
                           validateMinLength(title, 3, 'Title') || 
                           validateMaxLength(title, 100, 'Title');
    if (titleValidation && titleError) {
        titleError.textContent = titleValidation;
        titleError.style.display = 'block';
        document.getElementById('postTitle').classList.add('error');
        isValid = false;
    } else if (document.getElementById('postTitle')) {
        document.getElementById('postTitle').classList.remove('error');
    }
    
    // Validate content
    const contentValidation = validateRequired(content, 'Content') || 
                             validateMinLength(content, 10, 'Content');
    if (contentValidation && contentError) {
        contentError.textContent = contentValidation;
        contentError.style.display = 'block';
        document.getElementById('postContent').classList.add('error');
        isValid = false;
    } else if (document.getElementById('postContent')) {
        document.getElementById('postContent').classList.remove('error');
    }
    
    // Check for profanity
    if (containsProfanity(title) && titleError) {
        titleError.textContent = 'Title contains inappropriate language.';
        titleError.style.display = 'block';
        document.getElementById('postTitle').classList.add('error');
        isValid = false;
    }
    
    if (containsProfanity(content) && contentError) {
        contentError.textContent = 'Content contains inappropriate language.';
        contentError.style.display = 'block';
        document.getElementById('postContent').classList.add('error');
        isValid = false;
    }
    
    // Enable/disable submit button
    if (postButton) {
        postButton.disabled = !isValid;
        if (isValid && title.trim() && content.trim()) {
            postButton.classList.add('ready');
        } else {
            postButton.classList.remove('ready');
        }
    }
    
    return isValid;
}

// Enhanced async post form validation with server-side profanity check
async function validatePostFormAsync() {
    const title = document.getElementById('postTitle')?.value || '';
    const content = document.getElementById('postContent')?.value || '';
    const titleError = document.getElementById('titleError');
    const contentError = document.getElementById('contentError');
    const postButton = document.getElementById('postButton');
    
    // Basic validation first
    const isBasicValid = validatePostForm();
    if (!isBasicValid) {
        return false;
    }
    
    // If basic validation passes, check with server
    try {
        // Show loading state
        if (postButton) {
            postButton.disabled = true;
            postButton.textContent = 'Checking content...';
        }
        
        // Check title and content with server
        const titleCheck = await checkProfanityWithServer(title);
        if (!titleCheck.clean && titleError) {
            titleError.textContent = 'Title contains inappropriate language.';
            titleError.style.display = 'block';
            document.getElementById('postTitle').classList.add('error');
            if (postButton) {
                postButton.disabled = false;
                postButton.textContent = 'Post';
            }
            return false;
        }
        
        const contentCheck = await checkProfanityWithServer(content);
        if (!contentCheck.clean && contentError) {
            contentError.textContent = 'Content contains inappropriate language.';
            contentError.style.display = 'block';
            document.getElementById('postContent').classList.add('error');
            if (postButton) {
                postButton.disabled = false;
                postButton.textContent = 'Post';
            }
            return false;
        }
        
        // All checks passed
        if (postButton) {
            postButton.disabled = false;
            postButton.textContent = 'Post';
            postButton.classList.add('ready');
        }
        return true;
        
    } catch (error) {
        console.error('Error during content validation:', error);
        // Reset button state
        if (postButton) {
            postButton.disabled = false;
            postButton.textContent = 'Post';
        }
        return validatePostForm(); // Fall back to client-side validation
    }
}

// Comment form validation - enhanced with visual feedback
function validateCommentForm() {
    const commentInput = document.querySelector('.comment-input');
    if (!commentInput) return false;
    
    const comment = commentInput.value;
    const submitButton = document.querySelector('.submit-comment');
    const errorElement = document.querySelector('.error-message');
    
    // Reset error state
    if (errorElement) {
        errorElement.style.display = 'none';
    }
    commentInput.classList.remove('error');
    
    // Validate comment
    const commentValidation = validateRequired(comment, 'Comment') || 
                             validateMinLength(comment, 3, 'Comment');
    
    if (commentValidation) {
        if (errorElement) {
            errorElement.textContent = commentValidation;
            errorElement.style.display = 'block';
        }
        commentInput.classList.add('error');
        if (submitButton) submitButton.disabled = true;
        return false;
    }
    
    // Check for profanity
    if (containsProfanity(comment)) {
        if (errorElement) {
            errorElement.textContent = 'Comment contains inappropriate language.';
            errorElement.style.display = 'block';
        }
        commentInput.classList.add('error');
        if (submitButton) submitButton.disabled = true;
        return false;
    }
    
    // Valid comment
    if (submitButton) submitButton.disabled = false;
    return true;
}

// Enhanced async comment form validation with server-side profanity check
async function validateCommentFormAsync() {
    // Run basic validation first
    if (!validateCommentForm()) {
        return false;
    }
    
    const commentInput = document.querySelector('.comment-input');
    if (!commentInput) return false;
    
    const comment = commentInput.value;
    const submitButton = document.querySelector('.submit-comment');
    const errorElement = document.querySelector('.error-message');
    
    // Show loading state
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Checking...';
    }
    
    try {
        // Check with server
        const contentCheck = await checkProfanityWithServer(comment);
        if (!contentCheck.clean) {
            if (errorElement) {
                errorElement.textContent = 'Comment contains inappropriate language.';
                errorElement.style.display = 'block';
            }
            commentInput.classList.add('error');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Submit';
            }
            return false;
        }
        
        // All checks passed
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit';
        }
        return true;
    } catch (error) {
        console.error('Error during comment validation:', error);
        // Reset button state
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit';
        }
        return validateCommentForm(); // Fall back to client-side validation
    }
}

// Chat message validation - enhanced
function validateChatMessage(message) {
    if (!message || message.trim() === '') {
        return false;
    }
    
    if (message.length > 500) {
        return false;
    }
    
    // Check for profanity
    if (containsProfanity(message)) {
        return false;
    }
    
    return true;
}

// Enhanced async chat message validation with server-side profanity check
async function validateChatMessageAsync(message) {
    // Basic validation first
    if (!validateChatMessage(message)) {
        return false;
    }
    
    try {
        // Check with server
        const contentCheck = await checkProfanityWithServer(message);
        return contentCheck.clean;
    } catch (error) {
        console.error('Error during chat message validation:', error);
        return validateChatMessage(message); // Fall back to client-side validation
    }
}

// Login form validation
function validateLoginForm() {
    const email = document.getElementById('email')?.value || '';
    const password = document.getElementById('password')?.value || '';
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const loginButton = document.getElementById('loginButton');
    
    // Reset errors
    if (emailError) {
        emailError.textContent = '';
        emailError.style.display = 'none';
    }
    if (passwordError) {
        passwordError.textContent = '';
        passwordError.style.display = 'none';
    }
    
    // Validate email
    let isValid = true;
    const emailValidation = validateRequired(email, 'Email') || 
                           validateEmail(email);
    if (emailValidation && emailError) {
        emailError.textContent = emailValidation;
        emailError.style.display = 'block';
        document.getElementById('email').classList.add('error');
        isValid = false;
    } else if (document.getElementById('email')) {
        document.getElementById('email').classList.remove('error');
    }
    
    // Validate password
    const passwordValidation = validateRequired(password, 'Password');
    if (passwordValidation && passwordError) {
        passwordError.textContent = passwordValidation;
        passwordError.style.display = 'block';
        document.getElementById('password').classList.add('error');
        isValid = false;
    } else if (document.getElementById('password')) {
        document.getElementById('password').classList.remove('error');
    }
    
    // Enable/disable login button
    if (loginButton) {
        loginButton.disabled = !isValid;
    }
    
    return isValid;
}

// Email validation helper
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(email)) {
        return 'Please enter a valid email address.';
    }
    return null;
}

// Utility function to escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Date formatting utility
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to check if user is admin
function isAdmin() {
    return document.body.dataset.role === 'admin';
}

// Function to check if user is visitor
function isVisitor() {
    return document.body.dataset.role === 'visitor';
}

// Main validation function for forms
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true; // If form not found, don't block submission
    
    let isValid = true;
    const errorMessages = [];
    
    // Get all required inputs in the form
    const requiredFields = form.querySelectorAll('[required]');
    
    // Check each required field
    requiredFields.forEach(field => {
        // Clear previous error styling
        field.classList.remove('is-invalid');
        
        // Check if empty
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            errorMessages.push(`${field.name || 'Field'} is required`);
            isValid = false;
        }
        
        // Special validation for post content - minimum length
        if (field.name === 'content' && field.value.trim().length < 10) {
            field.classList.add('is-invalid');
            errorMessages.push('Content must be at least 10 characters long');
            isValid = false;
        }
        
        // Special validation for title - minimum length
        if (field.name === 'title' && field.value.trim().length < 3) {
            field.classList.add('is-invalid');
            errorMessages.push('Title must be at least 3 characters long');
            isValid = false;
        }
    });
    
    // Display error messages if any
    const errorContainer = document.getElementById(`${formId}-errors`);
    if (errorContainer && errorMessages.length > 0) {
        errorContainer.innerHTML = errorMessages.map(msg => `<div class="alert alert-danger">${msg}</div>`).join('');
        errorContainer.style.display = 'block';
    } else if (errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }
    
    return isValid;
}

// Real-time validation for fields
function setupLiveValidation(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const fields = form.querySelectorAll('input, textarea, select');
    
    fields.forEach(field => {
        field.addEventListener('blur', function() {
            // Skip validation for non-required fields that are empty
            if (!this.hasAttribute('required') && !this.value.trim()) {
                this.classList.remove('is-invalid');
                return;
            }
            
            // Validate based on field type and name
            let isValid = true;
            
            // If field is required, check if it's empty
            if (this.hasAttribute('required') && !this.value.trim()) {
                isValid = false;
            }
            
            // Content length validation
            if (this.name === 'content' && this.value.trim().length < 10) {
                isValid = false;
            }
            
            // Title length validation
            if (this.name === 'title' && this.value.trim().length < 3) {
                isValid = false;
            }
            
            // Email format validation
            if (this.type === 'email' && this.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value.trim())) {
                    isValid = false;
                }
            }
            
            // Update field styling based on validation
            if (!isValid) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Set up form submission validation
    form.addEventListener('submit', function(event) {
        if (!validateForm(formId)) {
            event.preventDefault();
        }
    });
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Setup validation for post form if it exists
    const postForm = document.getElementById('postForm');
    if (postForm) {
        const titleInput = document.getElementById('postTitle');
        const contentInput = document.getElementById('postContent');
        
        if (titleInput) {
            titleInput.addEventListener('input', validatePostForm);
            titleInput.addEventListener('blur', validatePostForm);
        }
        
        if (contentInput) {
            contentInput.addEventListener('input', validatePostForm);
            contentInput.addEventListener('blur', validatePostForm);
        }
        
        postForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Always prevent default first
            
            const isValid = await validatePostFormAsync();
            if (isValid) {
                this.submit(); // Only submit if async validation passed
            }
        });
    }
    
    // Setup validation for comment form if it exists
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        const commentInput = document.querySelector('.comment-input');
        
        if (commentInput) {
            commentInput.addEventListener('input', validateCommentForm);
            commentInput.addEventListener('blur', validateCommentForm);
        }
        
        commentForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Always prevent default first
            
            const isValid = await validateCommentFormAsync();
            if (isValid) {
                this.submit(); // Only submit if async validation passed
            }
        });
    }
    
    // Setup validation for login form if it exists
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        if (emailInput) {
            emailInput.addEventListener('input', validateLoginForm);
            emailInput.addEventListener('blur', validateLoginForm);
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('input', validateLoginForm);
            passwordInput.addEventListener('blur', validateLoginForm);
        }
        
        loginForm.addEventListener('submit', function(event) {
            if (!validateLoginForm()) {
                event.preventDefault();
            }
        });
    }
    
    // Setup chat message validation if chat exists
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        const messageInput = document.getElementById('chatMessage');
        const sendButton = document.getElementById('sendMessage');
        
        chatForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            if (messageInput && sendButton) {
                const message = messageInput.value.trim();
                sendButton.disabled = true;
                
                const isValid = await validateChatMessageAsync(message);
                if (isValid) {
                    // Handle chat submission via AJAX
                    try {
                        const formData = new FormData();
                        formData.append('action', 'send_message');
                        formData.append('message', message);
                        
                        const response = await fetch('../controllers/chat_api.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Clear input and refresh messages
                            messageInput.value = '';
                            refreshChatMessages();
                        } else {
                            alert(result.message || 'Failed to send message');
                        }
                    } catch (error) {
                        console.error('Error sending chat message:', error);
                        alert('Error sending message. Please try again.');
                    }
                } else {
                    alert('Message contains inappropriate content or is invalid.');
                }
                
                sendButton.disabled = false;
            }
        });
    }
    
    // Set up validation for common forms
    setupLiveValidation('post-form');
    setupLiveValidation('comment-form');
    setupLiveValidation('login-form');
    
    // Generic handler for any form with data-validate attribute
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        setupLiveValidation(form.id);
    });
});

// Helper function to refresh chat messages
function refreshChatMessages() {
    const chatContainer = document.getElementById('chatMessages');
    if (!chatContainer) return;
    
    fetch('../controllers/chat_api.php?action=get_messages')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages) {
                // Clear current messages
                chatContainer.innerHTML = '';
                
                // Add each message
                data.messages.forEach(message => {
                    const messageEl = document.createElement('div');
                    messageEl.className = `chat-message ${message.user_id === getUserId() ? 'own-message' : ''}`;
                    
                    const header = document.createElement('div');
                    header.className = 'message-header';
                    header.innerHTML = `<span class="user-name">${escapeHtml(message.user_name)}</span> 
                                        <span class="message-time">${formatDate(message.created_at)}</span>`;
                    
                    const content = document.createElement('div');
                    content.className = 'message-content';
                    content.textContent = message.content;
                    
                    messageEl.appendChild(header);
                    messageEl.appendChild(content);
                    chatContainer.appendChild(messageEl);
                });
                
                // Scroll to bottom
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        })
        .catch(error => console.error('Error fetching chat messages:', error));
}

// Helper function to get current user ID
function getUserId() {
    return parseInt(document.body.dataset.userId || '0');
}