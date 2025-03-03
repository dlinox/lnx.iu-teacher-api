<?php
return [
    'paths' => ['api/*'], // Aplica CORS solo a rutas API
    'allowed_methods' => ['*'], // Permitir todos los métodos
    'allowed_origins' => [
        //todo los orígenes permitidos de cualquier puerto 34.55.61.47:
        '/^http:\/\/34\.55\.61\.47(:\d+)?$/', // Permitir cualquier puerto en esta IP
        '/^http:\/\/localhost(:\d+)?$/' // Permitir localhost en cualquier puerto
    ], // Solo estos orígenes pueden acceder a la API
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Permitir todos los encabezados
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Solo si necesitas cookies o autenticación
];
