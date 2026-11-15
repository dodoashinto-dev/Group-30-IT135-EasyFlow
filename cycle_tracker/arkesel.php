<?php

function sendArkeselSms($phoneNumber, $message)
{
    $apiKey = getenv('ARKESSEL_API_KEY');
    $senderId = getenv('ARKESSEL_SENDER_ID');

    if (!$apiKey || !$senderId) {
        throw new RuntimeException('ARKESSEL_API_KEY and ARKESSEL_SENDER_ID must be configured.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required to send SMS messages.');
    }

    $payload = json_encode([
        'sender' => $senderId,
        'message' => $message,
        'recipients' => [ltrim($phoneNumber, '+')],
    ]);

    if ($payload === false) {
        throw new RuntimeException('Could not encode the Arkesel SMS request.');
    }

    $curl = curl_init('https://sms.arkesel.com/api/v2/sms/send');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Arkesel request failed: ' . $curlError);
    }

    if ($statusCode < 200 || $statusCode >= 300) {
        throw new RuntimeException('Arkesel returned HTTP status ' . $statusCode . '.');
    }

    $result = json_decode($response, true);
    if (!is_array($result) || !isset($result['status']) || $result['status'] !== 'success') {
        throw new RuntimeException('Arkesel did not confirm that the SMS request succeeded.');
    }
}
