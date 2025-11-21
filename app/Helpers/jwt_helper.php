<?php

if (!function_exists('generate_jwt')) {
    function generate_jwt($payload) {
        $secret = getenv('jwt.secret') ?: 'rahasia123_perpustakaan_2024';
        
        // Header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = base64_encode($header);
        
        // Payload
        $payload['iat'] = time();
        $payload['exp'] = time() + (7 * 24 * 60 * 60); // 7 days
        $payload = json_encode($payload);
        $payload = base64_encode($payload);
        
        // Signature
        $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }
}

if (!function_exists('validate_jwt')) {
    function validate_jwt($token) {
        $secret = getenv('jwt.secret') ?: 'rahasia123_perpustakaan_2024';
        
        // Split token
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verify signature
        $verified_signature = base64_encode(
            hash_hmac('sha256', "$header.$payload", $secret, true)
        );
        
        if ($signature !== $verified_signature) {
            return null;
        }
        
        // Decode payload
        $payload_data = json_decode(base64_decode($payload), true);
        
        // Check expiration
        if (isset($payload_data['exp']) && $payload_data['exp'] < time()) {
            return null;
        }
        
        return $payload_data;
    }
}