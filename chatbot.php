<?php
// AI Chatbot API Endpoint - SECURE VERSION

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// CORS - Restrict to allowed origins for security
$allowed_origins = [
    'http://localhost',
    'http://localhost/hungry-food',
    'http://localhost:8080',
    'http://localhost:8080/hungry-food'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header('Access-Control-Allow-Origin: http://localhost:8080');
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

// Validate message
if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Sanitize and limit message length
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
if (strlen($message) > 1000) {
    $message = substr($message, 0, 1000);
}

// Validate history
if (!is_array($history)) {
    $history = [];
}
// Limit history to last 10 messages to prevent abuse
$history = array_slice($history, -10);

$system_prompt = "You are Zara, a friendly and enthusiastic AI food assistant for Hungry Food restaurant. You help customers with:
- Menu questions (we serve burgers, pizzas, pasta, sandwiches, BBQ, desserts, drinks, and more)
- Pricing (prices are in USD)
- Placing orders (direct them to our menu page)
- Reservations (direct them to reservation page)
- Catering services (direct them to catering page)
- Delivery and timings
- Special offers and deals
- General food recommendations

Be warm, friendly, and conversational. Use food emojis occasionally. Keep responses concise (2-4 sentences max). If asked something unrelated to food or the restaurant, politely redirect the conversation back to how you can help them with food.

Restaurant details:
- Name: Hungry Food
- Specialties: Fast food, BBQ, Pakistani & Continental cuisine
- Services: Dine-in, Takeaway, Delivery, Catering, Reservations
- Contact: Available via WhatsApp
- Pages: menu.php, reservation.php, catering.php, contact.php, order.php";

// Build messages array with sanitized content
$messages = [];
foreach ($history as $h) {
    if (isset($h['role']) && isset($h['content'])) {
        $messages[] = [
            'role' => in_array($h['role'], ['user', 'assistant']) ? $h['role'] : 'user',
            'content' => htmlspecialchars(substr($h['content'], 0, 500), ENT_QUOTES, 'UTF-8')
        ];
    }
}
$messages[] = ['role' => 'user', 'content' => $message];

$data = [
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 300,
    'system' => $system_prompt,
    'messages' => $messages
];

// API key should be stored in environment variable or config file
$api_key = getenv('ANTHROPIC_API_KEY') ?: '';

if (empty($api_key)) {
    http_response_code(500);
    echo json_encode(['reply' => 'Chatbot is not configured yet. Please set up the API key.']);
    exit;
}

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $api_key,
    'anthropic-version: 2023-06-01'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log("Chatbot cURL error: " . $curl_error);
    http_response_code(500);
    echo json_encode(['reply' => 'Sorry, I am having trouble connecting right now. Please try again!']);
    exit;
}

$result = json_decode($response, true);

if ($http_code === 200 && isset($result['content'][0]['text'])) {
    echo json_encode(['reply' => $result['content'][0]['text']]);
} else {
    error_log("Chatbot API error: HTTP $http_code - " . $response);
    http_response_code($http_code === 429 ? 429 : 500);
    echo json_encode(['reply' => 'Sorry, I am having trouble responding right now. Please try again! 😊']);
}
?>
