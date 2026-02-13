<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Task Tracker API')]
#[OA\SecurityScheme(securityScheme: 'sanctum', type: 'http', bearerFormat: 'Token', scheme: 'bearer')]
abstract readonly class Controller
{
}
