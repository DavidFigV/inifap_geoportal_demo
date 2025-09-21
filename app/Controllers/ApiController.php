<?php
namespace App\Controllers;

use App\Models\Cultivo;

class ApiController extends BaseController {
    private $cultivoModel;
    
    public function __construct() {
        $this->cultivoModel = new Cultivo();
    }
    
    public function getCultivoGeoJSON($cultivo = 'frijol') {
        try {
            // Obtener parámetros de paginación y filtros
            $filtros = [
                'limit' => $_GET['limit'] ?? 10,  // Límite por defecto bajo
                'offset' => $_GET['offset'] ?? 0,
                'simplify' => $_GET['simplify'] ?? 0.0001, // Simplificación de geometrías
            ];
            
            // Filtros de potencial
            if (isset($_GET['potencial'])) {
                $filtros['potencial'] = is_array($_GET['potencial']) 
                    ? $_GET['potencial'] 
                    : explode(',', $_GET['potencial']);
            }
            
            $geojson = $this->cultivoModel->getGeoJSON($cultivo, $filtros);
            
            // Agregar metadatos útiles
            $geojson['metadata'] = array_merge($geojson['metadata'] ?? [], [
                'cultivo' => $cultivo,
                'timestamp' => date('c'),
                'filtros_aplicados' => $filtros,
                'total_returned' => count($geojson['features'] ?? [])
            ]);
            
            $this->json($geojson);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getEstadisticas($cultivo = 'frijol') {
        try {
            $stats = $this->cultivoModel->getEstadisticas($cultivo);
            $this->json([
                'cultivo' => $cultivo,
                'estadisticas' => $stats,
                'timestamp' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getCultivos() {
        try {
            $cultivos = $this->cultivoModel->getCultivosDisponibles();
            $this->json($cultivos);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    // Endpoint específico para obtener bounds
    public function getBounds($cultivo = 'frijol') {
        try {
            $bounds = $this->cultivoModel->getBounds($cultivo);
            $this->json([
                'cultivo' => $cultivo,
                'bounds' => $bounds,
                'timestamp' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    public function getPotencialByPoint() {
        try {
            $this->validateRequest(['lat', 'lon']);
            
            $lat = (float)$_GET['lat'];
            $lon = (float)$_GET['lon'];
            
            // Validar coordenadas aproximadas para México
            if ($lat < 14 || $lat > 33 || $lon < -118 || $lon > -86) {
                $this->json([
                    'error' => 'Coordenadas fuera del rango válido para México'
                ], 400);
            }
            
            $resultado = $this->cultivoModel->getPotencialByPoint($lat, $lon);
            
            if ($resultado) {
                $this->json([
                    'encontrado' => true,
                    'coordenadas' => ['lat' => $lat, 'lon' => $lon],
                    'potencial' => $resultado,
                    'timestamp' => date('c')
                ]);
            } else {
                $this->json([
                    'encontrado' => false,
                    'coordenadas' => ['lat' => $lat, 'lon' => $lon],
                    'mensaje' => 'No se encontró información para estas coordenadas',
                    'timestamp' => date('c')
                ]);
            }
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    public function verificarIndices() {
        try {
            $indices = $this->cultivoModel->verificarIndiceEspacial();
            
            $this->json([
                'indices_espaciales' => $indices,
                'recomendacion' => count($indices) > 0 ? 'Índices OK' : 'Se requiere crear índice GIST',
                'timestamp' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
}