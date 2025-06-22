<?php
// Секретный токен для ядра (ENGINE_SECRET)
define('ENGINE_SECRET', 'waztExOIYRsLlkNhxxxn2Bc0K3cQlboe');

function process_core($messages, $session_id) {
    // Здесь будто бы идёт обработка в твоём ядре
    $data = [
        'model' => 'mistral-large-latest',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mistral.ai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . ENGINE_SECRET
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('cURL error: ' . $curl_error);
    }
    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_message = isset($error_data['message']) ? $error_data['message'] : 'HTTP ' . $http_code;
        throw new Exception('API error: ' . $error_message);
    }
    $response_data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from engine');
    }
    if (!isset($response_data['choices'][0]['message']['content'])) {
        throw new Exception('Invalid engine response format: ' . json_encode($response_data));
    }
    return $response_data['choices'][0]['message']['content'];
}
?>
