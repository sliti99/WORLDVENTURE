<?php
/**
 * FilterService - Content Moderation Service
 * Provides text filtering capabilities using advanced detection methods
 * Integrated with Gemini 2.5 Flash API for content moderation
 */
class FilterService {
    // Load API key from .env file
    private static function getApiKey() {
        $envFile = __DIR__ . '/../../.env';
        $apiKey = null;
        
        if (file_exists($envFile)) {
            $envVars = parse_ini_file($envFile);
            $apiKey = $envVars['GEMINI_API_KEY'] ?? null;
        }
        
        return $apiKey;
    }
    
    /**
     * Filter content for inappropriate language or content
     * @param string $text Content to filter
     * @return array Result with 'clean' status and filtered text if applicable
     */
    public static function filterContent($text) {
        if (empty($text)) {
            return ['clean' => true, 'filteredText' => $text];
        }

        // Basic filtering for immediate protection (runs always)
        $basicBannedWords = ['fuck', 'shit', 'ass', 'bitch', 'dick', 'pussy', 'cunt', 'whore', 'bastard'];
        $foundBasic = false;
        
        foreach ($basicBannedWords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $foundBasic = true;
                break;
            }
        }
        
        if ($foundBasic) {
            return ['clean' => false, 'filteredText' => null];
        }
        
        // Additional filtering for edge cases
        // Simple pattern detection (common obfuscation techniques)
        $patterns = [
            '/f+\s*u+\s*c+\s*k+/i', 
            '/s+\s*h+\s*i+\s*t+/i',
            '/b+\s*i+\s*t+\s*c+\s*h+/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return ['clean' => false, 'filteredText' => null];
            }
        }
        
        // Try using Gemini if API key is available
        $apiKey = self::getApiKey();
        if ($apiKey) {
            try {
                $geminiResult = self::checkWithGemini($text, $apiKey);
                if (is_array($geminiResult)) {
                    return $geminiResult;
                }
            } catch (Exception $e) {
                error_log("Gemini API error: " . $e->getMessage());
                // Continue with basic filtering if Gemini fails
            }
        }
        
        // If all checks pass or Gemini wasn't available, content is considered clean
        return ['clean' => true, 'filteredText' => $text];
    }
    
    /**
     * Check content moderation using Gemini API
     * @param string $text Content to check
     * @param string $apiKey Gemini API key
     * @return array|bool Filtered result or false on failure
     */
    private static function checkWithGemini($text, $apiKey) {
        // Gemini API endpoint for content safety check
        $endpoint = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent";
        $url = $endpoint . "?key=" . urlencode($apiKey);
        
        // Prepare the prompt for content safety
        $prompt = "Check if the following content is appropriate and doesn't contain profanity, hate speech, or other harmful content. Respond with only 'SAFE' or 'UNSAFE'. Here's the content: " . $text;
        
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ]);
        
        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        
        // Execute request and handle response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || $error) {
            error_log("Gemini API error (HTTP $httpCode): $error");
            return false;
        }
        
        // Process the response
        $result = json_decode($response, true);
        
        // Check if there's a valid response with content
        if (isset($result['candidates']) && 
            isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            
            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Log the AI response for debugging
            error_log("Gemini safety response for content: $aiResponse");
            
            // Check if the AI determined the content was unsafe
            if (stripos($aiResponse, 'UNSAFE') !== false) {
                return ['clean' => false, 'filteredText' => null];
            } else {
                return ['clean' => true, 'filteredText' => $text];
            }
        }
        
        // If something went wrong with parsing the response
        return false;
    }
}
?>