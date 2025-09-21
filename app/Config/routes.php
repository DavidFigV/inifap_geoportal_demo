<?php

use Core\Router;
use App\Controllers\MapController;
use App\Controllers\ApiController;

$router = new Router();

// Rutas web
$router->get('/', [MapController::class, 'index']);
$router->get('/mapa', [MapController::class, 'index']);
$router->get('/mapa/([a-zA-Z]+)', [MapController::class, 'viewer']);

// API Routes
$router->get('/api/cultivos', [ApiController::class, 'getCultivos']);
$router->get('/api/cultivos/([a-zA-Z]+)/geojson', [ApiController::class, 'getCultivoGeoJSON']);
$router->get('/api/cultivos/([a-zA-Z]+)/estadisticas', [ApiController::class, 'getEstadisticas']);
// API Routes adicionales basadas en la documentación
$router->get('/api/cultivos/([a-zA-Z]+)/bounds', [ApiController::class, 'getBounds']);
$router->get('/api/potencial', [ApiController::class, 'getPotencialByPoint']);
$router->get('/api/areas', [ApiController::class, 'getCultivoGeoJSON']); // Alias más descriptivo
$router->get('/api/system/indices', [ApiController::class, 'verificarIndices']);

return $router;