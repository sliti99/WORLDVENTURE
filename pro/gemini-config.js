const { GoogleGenerativeAI } = require('@google/generative-ai');
require('dotenv').config();

// Check if API key exists
const apiKey = process.env.GEMINI_API_KEY;
if (!apiKey) {
  console.error('GEMINI_API_KEY not found in environment variables');
  process.exit(1);
}

// Initialize the Generative AI instance with the API key
const genAI = new GoogleGenerativeAI(apiKey);

// Define model names according to the latest Gemini API documentation
// Note: these model names may change, check documentation for updates
module.exports = {
  genAI,
  models: {
    // Use the correct model names without "models/" prefix
    pro: "gemini-pro",
    proVision: "gemini-pro-vision",
    flash: "gemini-flash"
  }
};
