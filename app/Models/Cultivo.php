<?php
namespace App\Models;

use Core\Model;

class Cultivo extends Model {
    protected $table = 'cultivo_frijol';
    
    public function getGeoJSON($cultivo = 'frijol', $filtros = []) {
        // LÍMITE DE SEGURIDAD: Máximo 500 polígonos por request
        $limit = isset($filtros['limit']) ? min((int)$filtros['limit'], 2000) : 500;
        $offset = isset($filtros['offset']) ? (int)$filtros['offset'] : 0;
        
        // Condición base - IMPORTANTE: NULL significa "Sin Potencial" según reglas de negocio
        $where = "WHERE 1=1";
        $params = [];
        
        // Aplicar filtros de potencial - Incluye NULL como "Sin Potencial"
        if (!empty($filtros['potencial'])) {
            $conditions = [];
            $filterParams = [];
            
            foreach ($filtros['potencial'] as $filtro) {
                switch (strtoupper($filtro)) {
                    case '1':
                    case 'ALTO':
                        $conditions[] = "UPPER(potencial) = 'ALTO'";
                        break;
                    case '2': 
                    case 'MEDIO':
                        $conditions[] = "UPPER(potencial) = 'MEDIO'";
                        break;
                    case '3':
                    case 'BAJO':
                        $conditions[] = "UPPER(potencial) = 'BAJO'";
                        break;
                    case '0':
                    case 'SIN_POTENCIAL':
                    case 'NULL':
                        $conditions[] = "potencial IS NULL";
                        break;
                }
            }
            
            if (!empty($conditions)) {
                $where .= " AND (" . implode(' OR ', $conditions) . ")";
            }
        }
        
        // Nivel de simplificación de geometrías (en grados para WGS84)
        $tolerance = isset($filtros['simplify']) ? (float)$filtros['simplify'] : 0.0001; // ~11m
        
        $sql = "
            SELECT jsonb_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(jsonb_agg(feature), '[]'::jsonb),
                'metadata', jsonb_build_object(
                    'total_features', COUNT(*) OVER(),
                    'limit', {$limit},
                    'offset', {$offset},
                    'srid_original', 32613,
                    'srid_output', 4326
                )
            ) as geojson
            FROM (
                SELECT jsonb_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(
                        ST_SimplifyPreserveTopology(
                            ST_Transform(geom, 4326), 
                            {$tolerance}
                        )
                    )::jsonb,
                    'properties', jsonb_build_object(
                        'gid', gid,
                        'id', id,
                        'gridcode', gridcode,
                        'potencial_texto', COALESCE(potencial, 'Sin Potencial'),
                        'potencial_numero', 
                        CASE 
                            WHEN UPPER(potencial) = 'ALTO' THEN 1
                            WHEN UPPER(potencial) = 'MEDIO' THEN 2 
                            WHEN UPPER(potencial) = 'BAJO' THEN 3
                            WHEN potencial IS NULL THEN 0
                            ELSE NULL
                        END,
                        'cultivo', '{$cultivo}',
                        'area_ha', ROUND((ST_Area(geom) / 10000)::numeric, 2),
                        'area_m2', ROUND(ST_Area(geom)::numeric, 2)
                    )
                ) as feature
                FROM {$this->table}
                {$where}
                ORDER BY gid  -- Usar gid como llave primaria
                LIMIT {$limit} OFFSET {$offset}
            ) features";
        
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            
            return json_decode($result['geojson'], true);
            
        } catch (\Exception $e) {
            error_log("Error en getGeoJSON: " . $e->getMessage());
            throw new \Exception("Error al obtener datos geoespaciales: " . $e->getMessage());
        }
    }
    
    public function getEstadisticas($cultivo = 'frijol') {
        $sql = "
            SELECT 
                COALESCE(potencial, 'Sin Potencial') as potencial_texto,
                CASE 
                    WHEN UPPER(potencial) = 'ALTO' THEN 1
                    WHEN UPPER(potencial) = 'MEDIO' THEN 2 
                    WHEN UPPER(potencial) = 'BAJO' THEN 3
                    WHEN potencial IS NULL THEN 0
                    ELSE NULL
                END as potencial_numero,
                COALESCE(potencial, 'Sin Potencial') as nivel,
                COUNT(*) as cantidad_poligonos,
                ROUND(SUM(ST_Area(geom))::numeric, 2) as area_total_m2,
                ROUND((SUM(ST_Area(geom)) / 10000)::numeric, 2) as area_total_ha,
                ROUND(AVG(ST_Area(geom))::numeric, 2) as area_promedio_m2
            FROM {$this->table}
            GROUP BY potencial
            ORDER BY 
                CASE 
                    WHEN UPPER(potencial) = 'ALTO' THEN 1
                    WHEN UPPER(potencial) = 'MEDIO' THEN 2 
                    WHEN UPPER(potencial) = 'BAJO' THEN 3
                    WHEN potencial IS NULL THEN 4
                    ELSE 5
                END";
        
        try {
            $stmt = $this->query($sql);
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            throw new \Exception("Error al obtener estadísticas: " . $e->getMessage());
        }
    }
    
    public function getBounds($cultivo = 'frijol') {
        $sql = "
            SELECT ST_AsGeoJSON(
                ST_Transform(
                    ST_SetSRID(ST_Extent(geom), 32613), 
                    4326
                )
            ) as bounds
            FROM {$this->table}
            WHERE geom IS NOT NULL";
            
        try {
            $stmt = $this->query($sql);
            $result = $stmt->fetch();
            
            if ($result && $result['bounds']) {
                return json_decode($result['bounds'], true);
            }
            
            throw new \Exception("No se encontraron datos geográficos válidos");
            
        } catch (\Exception $e) {
            error_log("Error en getBounds: " . $e->getMessage());
            throw new \Exception("Error al obtener límites geográficos: " . $e->getMessage());
        }
    }
    
    public function getTotalCount($filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['potencial'])) {
            $conditions = [];
            
            foreach ($filtros['potencial'] as $filtro) {
                switch (strtoupper($filtro)) {
                    case '1':
                    case 'ALTO':
                        $conditions[] = "UPPER(potencial) = 'ALTO'";
                        break;
                    case '2':
                    case 'MEDIO': 
                        $conditions[] = "UPPER(potencial) = 'MEDIO'";
                        break;
                    case '3':
                    case 'BAJO':
                        $conditions[] = "UPPER(potencial) = 'BAJO'";
                        break;
                    case '0':
                    case 'SIN_POTENCIAL':
                    case 'NULL':
                        $conditions[] = "potencial IS NULL";
                        break;
                }
            }
            
            if (!empty($conditions)) {
                $where .= " AND (" . implode(' OR ', $conditions) . ")";
            }
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            return (int)$result['total'];
            
        } catch (\Exception $e) {
            error_log("Error en getTotalCount: " . $e->getMessage());
            throw new \Exception("Error al contar registros: " . $e->getMessage());
        }
    }
    
    // Método para consulta por punto (implementa la sugerencia de la documentación)
    public function getPotencialByPoint($lat, $lon) {
        $sql = "
            SELECT 
                gid,
                id,
                gridcode,
                COALESCE(potencial, 'Sin Potencial') as potencial_texto,
                CASE 
                    WHEN UPPER(potencial) = 'ALTO' THEN 1
                    WHEN UPPER(potencial) = 'MEDIO' THEN 2 
                    WHEN UPPER(potencial) = 'BAJO' THEN 3
                    WHEN potencial IS NULL THEN 0
                    ELSE NULL
                END as potencial_numero,
                ROUND((ST_Area(geom) / 10000)::numeric, 2) as area_ha
            FROM {$this->table}
            WHERE ST_Intersects(
                geom, 
                ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326), 32613)
            )
            LIMIT 1";
            
        try {
            $stmt = $this->query($sql, [$lon, $lat]);
            return $stmt->fetch();
            
        } catch (\Exception $e) {
            error_log("Error en getPotencialByPoint: " . $e->getMessage());
            throw new \Exception("Error al obtener potencial por punto: " . $e->getMessage());
        }
    }
    
    public function getCultivosDisponibles() {
        return [
            'frijol' => [
                'nombre' => 'Frijol',
                'tabla' => 'cultivo_frijol',
                'activo' => true,
                'descripcion' => 'Potencial de cultivo de frijol basado en temperatura'
            ],
            'maiz' => [
                'nombre' => 'Maíz',
                'tabla' => 'cultivo_maiz',
                'activo' => false,
                'descripcion' => 'Próximamente'
            ],
            'tomate' => [
                'nombre' => 'Tomate',
                'tabla' => 'cultivo_tomate',
                'activo' => false,
                'descripcion' => 'Próximamente'
            ]
        ];
    }
    
    // Método para verificar índice espacial
    public function verificarIndiceEspacial() {
        $sql = "
            SELECT 
                indexname,
                indexdef
            FROM pg_indexes 
            WHERE tablename = ? 
            AND indexdef LIKE '%USING gist%'";
            
        try {
            $stmt = $this->query($sql, [$this->table]);
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            error_log("Error verificando índice espacial: " . $e->getMessage());
            return [];
        }
    }
}