<?php
require_once '../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    echo "<h2>🧪 Test de Conexión INIFAP</h2>";
    
    // Test 1: Variables de entorno
    echo "<h3>1. Variables de entorno</h3>";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? '❌ No definido') . "<br>";
    echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? '❌ No definido') . "<br>";
    echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? '❌ No definido') . "<br>";
    
    // Test 2: Conexión a base de datos
    echo "<h3>2. Conexión a PostgreSQL</h3>";
    
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];
    $port = $_ENV['DB_PORT'] ?? 5432;
    
    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname}",
        $username,
        $password
    );
    
    echo "✅ Conexión exitosa a PostgreSQL<br>";
    
    // Test 3: Verificar PostGIS
    echo "<h3>3. Verificar PostGIS</h3>";
    $stmt = $pdo->query("SELECT postgis_version();");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ PostGIS versión: " . $version['postgis_version'] . "<br>";
    
    // Test 4: Verificar tabla cultivo_frijol
    echo "<h3>4. Verificar datos del shapefile</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cultivo_frijol;");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Registros en cultivo_frijol: " . $count['total'] . "<br>";
    
    // Test 5: Verificar columnas
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'cultivo_frijol';");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Columnas disponibles: " . implode(', ', $columns) . "<br>";
    
    // Test 6: Datos de muestra
    echo "<h3>5. Muestra de datos</h3>";
    $stmt = $pdo->query("SELECT id, gridcode, potencial FROM cultivo_frijol LIMIT 3;");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>GridCode</th><th>Potencial</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['gridcode']}</td><td>{$row['potencial']}</td></tr>";
    }
    echo "</table>";
    
    echo "<br><strong>🎉 Todos los tests básicos pasaron correctamente!</strong>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<pre style='color: red;'>" . $e->getMessage() . "</pre>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar que PostgreSQL esté corriendo</li>";
    echo "<li>Verificar credenciales en .env</li>";
    echo "<li>Verificar que la base de datos 'inifap_geoportal' exista</li>";
    echo "<li>Verificar que el usuario tenga permisos</li>";
    echo "</ul>";
}
?>