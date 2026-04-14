<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OAT;

#[OAT\Info(
    version: '1.0.0',
    title: 'Admin Panel API Documentation'
)]
#[OAT\PathItem(path: '/api/admin')]
#[OAT\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Enter your Bearer token to access admin endpoints',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OAT\Server(url: 'http://localhost:8080', description: 'Local Server')]
abstract class Controller {}
