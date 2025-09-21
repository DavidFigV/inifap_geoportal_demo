<?php

require_once '../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Manejar errores
if ($_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

try {
    // Cargar router
    $router = require_once '../app/Config/routes.php';
    
    // Resolver ruta
    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    $router->resolve($uri, $method);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        echo "<h1>Error {$e->getCode()}</h1>";
        echo "<p>{$e->getMessage()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    } else {
        echo "<h1>Error</h1><p>Ha ocurrido un error.</p>";
    }
}