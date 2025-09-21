<?php
use App\Models\Cultivo;
require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    echo "<h2>🧪 Test con Estructura Real de cultivo_frijol</h2>";
    
    $pdo = new PDO(
        "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD']
    );
    
    // 1. Verificar estructura real
    echo "<h3>1. Estructura de la tabla:</h3>";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'cultivo_frijol' 
        ORDER BY ordinal_position;
    ");
    echo "<table border='1'><tr><th>Columna</th><th>Tipo</th><th>Nulo?</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td><td>{$row['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // 2. Verificar valores de potencial incluyendo NULL
    echo "<h3>2. Distribución de valores en 'potencial' (incluye NULL como 'Sin Potencial'):</h3>";
    $stmt = $pdo->query("
        SELECT 
            COALESCE(potencial, 'Sin Potencial') as potencial_categoria,
            potencial as potencial_original,
            COUNT(*) as cantidad
        FROM cultivo_frijol 
        GROUP BY potencial 
        ORDER BY 
            CASE 
                WHEN UPPER(potencial) = 'ALTO' THEN 1
                WHEN UPPER(potencial) = 'MEDIO' THEN 2 
                WHEN UPPER(potencial) = 'BAJO' THEN 3
                WHEN potencial IS NULL THEN 4
                ELSE 5
            END;
    ");
    echo "<table border='1'><tr><th>Categoría</th><th>Valor Original</th><th>Cantidad</th></tr>";
    while ($row = $stmt->fetch()) {
        $original = $row['potencial_original'] ?? 'NULL';
        echo "<tr><td><strong>{$row['potencial_categoria']}</strong></td><td>{$original}</td><td>{$row['cantidad']}</td></tr>";
    }
    echo "</table>";
    
    // 3. Verificar índice espacial
    echo "<h3>3. Verificar índice espacial GIST:</h3>";
    $stmt = $pdo->query("
        SELECT indexname, indexdef
        FROM pg_indexes 
        WHERE tablename = 'cultivo_frijol' 
        AND indexdef LIKE '%USING gist%';
    ");
    $indices = $stmt->fetchAll();
    if (count($indices) > 0) {
        echo "<p style='color: green;'>✅ Índice espacial encontrado:</p>";
        foreach ($indices as $index) {
            echo "<code>{$index['indexname']}</code><br>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontró índice espacial GIST</p>";
        echo "<p><strong>Recomendación:</strong> Crear índice con:</p>";
        echo "<code>CREATE INDEX cultivo_frijol_geom_idx ON cultivo_frijol USING GIST (geom);</code>";
    }
    
    // 4. Test del modelo actualizado
    echo "<h3>4. Test del modelo con estructura real:</h3>";
    
    $cultivo = new Cultivo();
    
    // Test estadísticas
    $stats = $cultivo->getEstadisticas('frijol');
    echo "<h4>Estadísticas:</h4>";
    echo "<pre>" . json_encode($stats, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test GeoJSON con límite pequeño
    echo "<h4>Test GeoJSON (5 registros):</h4>";
    $geojson = $cultivo->getGeoJSON('frijol', ['limit' => 5]);
    echo "Features obtenidos: " . count($geojson['features']) . "<br>";
    echo "Metadatos: <pre>" . json_encode($geojson['metadata'] ?? [], JSON_PRETTY_PRINT) . "</pre>";
    
    // Test bounds
    echo "<h4>Límites geográficos:</h4>";
    $bounds = $cultivo->getBounds('frijol');
    echo "<pre>" . json_encode($bounds, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<br><strong style='color: green;'>🎉 Todos los tests con estructura real completados!</strong>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<pre style='color: red;'>" . $e->getMessage() . "</pre>";
}
?>