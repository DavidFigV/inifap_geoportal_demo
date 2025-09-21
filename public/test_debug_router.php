<?php
use App\Controllers\ApiController;
require_once '../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "<h2>🔍 Debug del Router - INIFAP</h2>";

try {
    // Test 1: Verificar información del servidor
    echo "<h3>1. Información del Servidor:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Variable</th><th>Valor</th></tr>";
    echo "<tr><td>REQUEST_URI</td><td>" . ($_SERVER['REQUEST_URI'] ?? 'NO DEFINIDO') . "</td></tr>";
    echo "<tr><td>REQUEST_METHOD</td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'NO DEFINIDO') . "</td></tr>";
    echo "<tr><td>SCRIPT_NAME</td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'NO DEFINIDO') . "</td></tr>";
    echo "<tr><td>PATH_INFO</td><td>" . ($_SERVER['PATH_INFO'] ?? 'NO DEFINIDO') . "</td></tr>";
    echo "<tr><td>QUERY_STRING</td><td>" . ($_SERVER['QUERY_STRING'] ?? 'NO DEFINIDO') . "</td></tr>";
    echo "</table>";
    
    // Test 2: Verificar que las clases existan
    echo "<h3>2. Verificar Clases:</h3>";
    $clases = [
        'Core\Router' => class_exists('Core\Router'),
        'App\Controllers\ApiController' => class_exists('App\Controllers\ApiController'),
        'App\Controllers\MapController' => class_exists('App\Controllers\MapController'),
        'App\Models\Cultivo' => class_exists('App\Models\Cultivo')
    ];
    
    foreach ($clases as $clase => $existe) {
        $estado = $existe ? '✅' : '❌';
        echo "{$estado} {$clase}<br>";
    }
    
    // Test 3: Cargar router manualmente
    echo "<h3>3. Test Manual del Router:</h3>";
    
    $router = require_once '../app/Config/routes.php';
    echo "✅ Router cargado correctamente<br>";
    
    // Test 4: Simular rutas específicas
    echo "<h3>4. Test de Rutas Específicas:</h3>";
    
    $rutas_test = [
        '/api/cultivos/frijol/geojson',
        '/api/cultivos/frijol/estadisticas', 
        '/api/cultivos',
        '/api/potencial',
        '/',
        '/mapa'
    ];
    
    echo "<table border='1'>";
    echo "<tr><th>Ruta</th><th>Resultado</th><th>Detalles</th></tr>";
    
    foreach ($rutas_test as $ruta) {
        echo "<tr><td><code>{$ruta}</code></td>";
        
        try {
            // Simular variables de servidor
            $_SERVER['REQUEST_URI'] = $ruta;
            $_SERVER['REQUEST_METHOD'] = 'GET';
            
            // Intentar resolver (sin ejecutar)
            $uri = parse_url($ruta, PHP_URL_PATH);
            echo "<td style='color: green;'>✅ Parseada OK</td>";
            echo "<td>URI procesada: {$uri}</td>";
            
        } catch (Exception $e) {
            echo "<td style='color: red;'>❌ Error</td>";
            echo "<td>{$e->getMessage()}</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 5: Test directo del controlador
    echo "<h3>5. Test Directo del ApiController:</h3>";
    
    try {
        $apiController = new ApiController();
        echo "✅ ApiController instanciado correctamente<br>";
        
        // Verificar métodos
        $metodos = ['getCultivos', 'getCultivoGeoJSON', 'getEstadisticas', 'verificarIndices'];
        foreach ($metodos as $metodo) {
            if (method_exists($apiController, $metodo)) {
                echo "✅ Método {$metodo} existe<br>";
            } else {
                echo "❌ Método {$metodo} NO existe<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error instanciando ApiController: " . $e->getMessage() . "<br>";
    }
    
    // Test 6: URLs de acceso directo
    echo "<h3>6. URLs de Prueba Directas:</h3>";
    $base_url = "http://" . $_SERVER['HTTP_HOST'];
    
    echo "<ul>";
    echo "<li><a href='{$base_url}/test_direct_api.php' target='_blank'>Test API Directo</a></li>";
    echo "<li><a href='{$base_url}/test_estructura_real.php' target='_blank'>Test Estructura Real</a></li>";
    echo "<li><a href='{$base_url}/' target='_blank'>Página Principal</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error General:</h3>";
    echo "<pre style='color: red;'>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>