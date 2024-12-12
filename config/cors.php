<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Rutas que permitirán solicitudes CORS

    'allowed_methods' => ['*'], // Métodos HTTP permitidos (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => [
        '*'
        
    ], // Aquí debes colocar la URL de tu frontend

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Permite todos los encabezados

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Esto permite el uso de cookies y autenticación con CORS

];
