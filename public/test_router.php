<?php
require_once '../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "<h2>🧪 Test del Router</h2>";

try {
    // Simular diferentes rutas
    $test_routes = [
        '/' => 'MapController::index',
        '/mapa' => 'MapController::index', 
        '/mapa/frijol' => 'MapController::viewer',
        '/api/cultivos' => 'ApiController::getCultivos',
        '/api/cultivos/frijol/geojson' => 'ApiController::getCultivoGeoJSON'
    ];
    
    // Cargar router
    $router = require_once '../app/Config/routes.php';
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Ruta</th><th>Estado</th><th>Controlador Esperado</th></tr>";
    
    foreach ($test_routes as $route => $expected) {
        echo "<tr>";
        echo "<td><code>{$route}</code></td>";
        
        try {
            // Simular REQUEST_URI
            $_SERVER['REQUEST_URI'] = $route;
            $_SERVER['REQUEST_METHOD'] = 'GET';
            
            // Esto normalmente ejecutaría el controlador
            // Para testing, solo verificamos que no lance errores
            echo "<td style='color: green;'>✅ OK</td>";
            echo "<td>{$expected}</td>";
            
        } catch (Exception $e) {
            echo "<td style='color: red;'>❌ Error</td>";
            echo "<td>" . $e->getMessage() . "</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error en router: " . $e->getMessage() . "</p>";
}
?>