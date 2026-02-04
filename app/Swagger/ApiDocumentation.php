<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Glamrush admin service API',
    version: '1.0.0',
    description: 'API documentation for glamrush admin service.',
    contact: new OA\Contact(
        email: 'demioyewusi@gmail.com'
    )
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000/api/v1/',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'token'
)]

class ApiDocumentation {}
