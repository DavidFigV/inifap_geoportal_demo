# INIFAP Zacatecas - Geoportal Agrícola

Sistema web de geoportal para el análisis y visualización de potencial agrícola en el estado de Zacatecas, México. Desarrollado para el Instituto Nacional de Investigaciones Forestales, Agrícolas y Pecuarias (INIFAP).

## Descripción

El INIFAP Geoportal es una aplicación web que permite visualizar y analizar mapas de potencial agrícola para diferentes cultivos en Zacatecas. Utiliza datos geoespaciales procesados para mostrar zonas con diferentes niveles de aptitud para cultivos específicos, basándose en factores como temperatura y otras variables climáticas.

### Características Principales

- **Mapas Interactivos**: Visualización de polígonos con diferentes niveles de potencial agrícola
- **Análisis por Cultivos**: Información específica para frijol (con capacidad de expansión a maíz, tomate, ajo y aguacate)
- **Filtros Dinámicos**: Filtrado por nivel de potencial (Alto, Medio, Bajo, Sin Potencial)
- **Consultas por Punto**: Click en el mapa para obtener información específica de ubicación
- **Estadísticas en Tiempo Real**: Cálculo automático de áreas y distribución por potencial
- **API RESTful**: Endpoints para consulta de datos geoespaciales e integración con otros sistemas
- **Diseño Gubernamental**: Interfaz siguiendo las guías gráficas del Gobierno de México v3

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+** con arquitectura MVC personalizada
- **PostgreSQL 12+** con extensión **PostGIS 3.0+** para datos geoespaciales
- **Composer** para autoload PSR-4 y gestión de dependencias

### Frontend
- **Bootstrap 5.3.3** siguiendo estándares gubernamentales
- **Leaflet** para mapas interactivos
- **OpenStreetMap** como capa base
- **JavaScript ES6+** vanilla para interacciones

### Herramientas de Desarrollo
- **QGIS** para análisis y preparación de datos geoespaciales
- **Git** para control de versiones
- **pgAdmin 4** para administración de base de datos

### Datos Espaciales
- **Shapefiles** importados a PostGIS
- **Sistema de coordenadas**: UTM Zone 13N (SRID 32613) → WGS84 (SRID 4326) para web
- **Datos actuales**: 184 registros de potencial agrícola para frijol

## Estructura del Proyecto

```
inifap_geoportal/
├── app/
│   ├── Config/
│   │   ├── database.php          # Conexión singleton a PostgreSQL
│   │   └── routes.php            # Definición de rutas web y API
│   ├── Controllers/
│   │   ├── BaseController.php    # Controlador base con validaciones
│   │   ├── MapController.php     # Controlador de vistas de mapas
│   │   └── ApiController.php     # Controlador de API REST
│   ├── Models/
│   │   └── Cultivo.php           # Modelo para datos de cultivos
│   └── Views/
│       ├── layouts/              # Layouts reutilizables (header, navbar, footer)
│       └── mapa/                 # Vistas específicas de mapas
├── core/
│   ├── Router.php                # Sistema de routing personalizado
│   ├── Controller.php            # Clase base para controladores
│   └── Model.php                 # Clase base para modelos
├── public/
│   ├── assets/js/                # JavaScript de la aplicación
│   ├── .htaccess                 # Configuración Apache con CORS
│   └── index.php                 # Punto de entrada (front controller)
├── .env                          # Variables de entorno (no versionado)
├── .gitignore                    # Archivos ignorados por Git
├── composer.json                 # Dependencias PHP y autoload PSR-4
└── README.md                     # Este archivo
```

## Instalación

### Requisitos Previos

- **PHP 7.4 o superior** con extensiones:
  - PDO PostgreSQL
  - mbstring
  - curl
  - pcre
- **PostgreSQL 12+** con extensión **PostGIS 3.0+**
- **Apache/Nginx** con mod_rewrite habilitado
- **Composer** para gestión de dependencias
- **QGIS** (opcional, para manejo de shapefiles)

### Pasos de Instalación

#### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd inifap_geoportal
```

#### 2. Instalar dependencias
```bash
composer install
```

#### 3. Configurar variables de entorno
Crear archivo `.env` en la raíz del proyecto:
```env
DB_HOST=localhost
DB_NAME=inifap_geoportal
DB_USER=tu_usuario
DB_PASSWORD=tu_password
DB_PORT=5432

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### 4. Configurar base de datos

**Crear base de datos en PostgreSQL:**
```sql
-- Crear base de datos
CREATE DATABASE inifap_geoportal;

-- Conectar y habilitar PostGIS
\c inifap_geoportal;
CREATE EXTENSION IF NOT EXISTS postgis;

-- Verificar instalación
SELECT postgis_full_version();
```

#### 5. Importar datos geoespaciales

**Opción A: Usando shp2pgsql (recomendado):**
```bash
shp2pgsql -I -s 32613 -W UTF-8 ZacFrijolPVTemp_Nvo.shp cultivo_frijol | psql -U usuario -d inifap_geoportal
```

**Opción B: Usando pgAdmin 4:**
1. Abrir pgAdmin 4
2. Tools → PostGIS Shapefile Import/Export Manager
3. Configurar:
   - Connection: tu servidor PostgreSQL
   - Database: `inifap_geoportal`
   - Schema: `public`
   - Table: `cultivo_frijol`
   - SRID: `32613` (UTM Zone 13N)
   - Seleccionar archivo `ZacFrijolPVTemp_Nvo.shp`

#### 6. Verificar importación de datos
```sql
-- Verificar registros importados
SELECT COUNT(*) as total_registros FROM cultivo_frijol;

-- Verificar distribución de valores de potencial
SELECT 
    COALESCE(potencial, 'Sin Potencial') as categoria,
    COUNT(*) as cantidad
FROM cultivo_frijol 
GROUP BY potencial;

-- Verificar índice espacial
SELECT indexname FROM pg_indexes 
WHERE tablename = 'cultivo_frijol' 
AND indexdef LIKE '%USING gist%';
```

#### 7. Configurar servidor web

**Apache Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName inifap.local
    DocumentRoot /ruta/al/proyecto/public
    
    <Directory /ruta/al/proyecto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**O usar servidor PHP integrado para desarrollo:**
```bash
cd public/
php -S localhost:8000
```

## Configuración

### Estructura de Base de Datos

La aplicación utiliza PostgreSQL con PostGIS. El shapefile importado debe tener:

**Tabla: `cultivo_frijol`**
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `gid` | `serial` | Llave primaria autoincremental |
| `id` | `float8` | Identificador original del shapefile |
| `gridcode` | `float8` | Código de grid para simbología |
| `potencial` | `varchar(50)` | Nivel de aptitud (ALTO/MEDIO/BAJO/NULL) |
| `geom` | `geometry(MULTIPOLYGON, 32613)` | Geometría en UTM Zone 13N |

**Regla de Negocio Crítica**: El valor `NULL` en `potencial` representa "Sin Potencial" para el cultivo, no datos faltantes.

### Límites de Rendimiento

Para evitar problemas de memoria y rendimiento:

- **Máximo 2000 polígonos** por request (recomendado: 100-500)
- **Simplificación automática** de geometrías según zoom
- **Límite de memoria PHP**: 256MB recomendado
- **Timeout**: 60 segundos máximo por consulta
- **Índices espaciales GIST** obligatorios para rendimiento óptimo

## Uso

### Interfaz Web

#### URLs Principales
- **Mapa Principal**: `http://localhost:8000/`
- **Viewer de Frijol**: `http://localhost:8000/mapa/frijol`

#### Funcionalidades de la Interfaz

**Panel de Control:**
- Selector de cultivo (actualmente solo frijol activo)
- Filtros por nivel de potencial con checkboxes
- Configuración de límites (50-500 polígonos)
- Configuración de simplificación (alta/media/baja)

**Mapa Interactivo:**
- Click en polígono: Muestra información detallada
- Click en mapa: Consulta potencial por coordenadas
- Tooltips automáticos al pasar el mouse
- Zoom y centrado automático en datos

**Estadísticas Dinámicas:**
- Área total por nivel de potencial
- Cantidad de polígonos por categoría
- Cálculos en hectáreas y metros cuadrados

### API REST

#### Endpoints Principales

**Obtener cultivos disponibles:**
```http
GET /api/cultivos
```

**Obtener datos GeoJSON:**
```http
GET /api/cultivos/{cultivo}/geojson?limit=100&simplify=0.0001&potencial=1,2,3
```

**Parámetros disponibles:**
- `limit`: Número máximo de polígonos (1-2000, recomendado: 100)
- `offset`: Para paginación
- `simplify`: Nivel de simplificación (0.00001-0.01)
- `potencial`: Filtros por nivel (1=Alto, 2=Medio, 3=Bajo, 0=Sin Potencial)

**Obtener estadísticas:**
```http
GET /api/cultivos/{cultivo}/estadisticas
```

**Consultar por coordenadas:**
```http
GET /api/potencial?lat=22.7709&lon=-102.5832
```

**Obtener límites geográficos:**
```http
GET /api/cultivos/{cultivo}/bounds
```

**Verificar índices del sistema:**
```http
GET /api/system/indices
```

#### Ejemplos de Respuesta

**GeoJSON Response:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": { /* Geometría simplificada */ },
      "properties": {
        "gid": 1,
        "gridcode": 5241,
        "potencial_texto": "MEDIO",
        "potencial_numero": 2,
        "cultivo": "frijol",
        "area_ha": 12.34,
        "area_m2": 123400
      }
    }
  ],
  "metadata": {
    "total_features": 184,
    "limit": 100,
    "offset": 0,
    "srid_original": 32613,
    "srid_output": 4326
  }
}
```

## Desarrollo

### Agregar Nuevo Cultivo

1. **Importar shapefile** con estructura idéntica a `cultivo_frijol`
2. **Actualizar modelo** en `app/Models/Cultivo.php`:
   ```php
   public function getCultivosDisponibles() {
       return [
           'frijol' => [
               'nombre' => 'Frijol',
               'tabla' => 'cultivo_frijol',
               'activo' => true,
               'descripcion' => 'Potencial basado en temperatura'
           ],
           'maiz' => [
               'nombre' => 'Maíz',
               'tabla' => 'cultivo_maiz',
               'activo' => true, // Cambiar a true cuando esté listo
               'descripcion' => 'Potencial basado en precipitación'
           ]
       ];
   }
   ```

### Arquitectura MVC

**Modelos** (`app/Models/`):
- Contienen lógica de negocio y consultas PostGIS
- Manejan transformaciones de SRID y simplificación
- Implementan cache y optimizaciones

**Vistas** (`app/Views/`):
- Layouts reutilizables con estilos gubernamentales
- JavaScript modular para cada vista
- Integración con APIs mediante fetch()

**Controladores** (`app/Controllers/`):
- Validación de parámetros de entrada
- Coordinación entre modelos y vistas
- Manejo de errores y respuestas JSON

### Debugging y Testing

**Verificar conexión y datos:**
```bash
# Verificar que todo funciona
curl http://localhost:8000/api/cultivos

# Test con límite pequeño
curl "http://localhost:8000/api/cultivos/frijol/geojson?limit=5"

# Verificar estadísticas
curl http://localhost:8000/api/cultivos/frijol/estadisticas
```

**Console JavaScript para debugging:**
```javascript
// En navegador (F12 → Console)
// Verificar estado del mapa
console.log('Mapa:', map);
console.log('Capa actual:', currentLayer);

// Test de carga de datos
cargarDatos();

// Ver filtros activos
console.log('Filtros:', obtenerFiltrosPotencial());
```

## Resolución de Problemas

### Error: "Memory exhausted"
**Causa**: Demasiados polígonos cargados simultáneamente

**Soluciones:**
```bash
# Aumentar memoria en .htaccess
echo "php_value memory_limit 512M" >> public/.htaccess

# O reducir límites en interfaz
# limit=50&simplify=0.001
```

### Error: "SRID unknown (0)"
**Causa**: Geometrías sin sistema de coordenadas asignado

**Solución:**
```sql
UPDATE cultivo_frijol 
SET geom = ST_SetSRID(geom, 32613) 
WHERE ST_SRID(geom) = 0;
```

### Polígonos no se muestran en el mapa
**Verificaciones:**
1. Consultar bounds: `curl http://localhost:8000/api/cultivos/frijol/bounds`
2. Verificar SRID: `SELECT DISTINCT ST_SRID(geom) FROM cultivo_frijol;`
3. Check JavaScript console para errores

### API retorna 404
**Verificaciones:**
1. Archivo `.htaccess` existe en `public/`
2. mod_rewrite habilitado en Apache
3. Rutas definidas correctamente en `routes.php`

### Rendimiento lento
**Optimizaciones:**
```sql
-- Crear índice espacial si no existe
CREATE INDEX IF NOT EXISTS cultivo_frijol_geom_idx 
ON cultivo_frijol USING GIST (geom);

-- Actualizar estadísticas
ANALYZE cultivo_frijol;
```

## Contribución

### Estándares de Código
- **PSR-4** para autoloading de clases
- **Comentarios en español** para funciones principales
- **Variables descriptivas** en español
- **Logging detallado** para debugging en desarrollo

### Workflow de Desarrollo
1. Crear rama feature: `git checkout -b feature/nueva-funcionalidad`
2. Implementar cambios siguiendo estándares MVC
3. Probar con datos reales de Zacatecas
4. Actualizar documentación si es necesario
5. Crear pull request con descripción detallada

### Mejoras Futuras Propuestas
- **Cache de consultas** con Redis
- **Tiles de mapas** para datasets grandes
- **Panel administrativo** para gestión de cultivos
- **Exportación a PDF/Excel** de reportes
- **Integración con datos meteorológicos** en tiempo real

## Licencia y Créditos

**Desarrollado para**: INIFAP Centro Zacatecas  
**Empresa Desarrolladora**: Fundación DEDICA  
**Framework de Diseño**: [Guías Gráficas del Gobierno de México v3](https://www.gob.mx/guias/grafica/v3/)

Proyecto gubernamental siguiendo las directrices de desarrollo de software del Gobierno de México para servicios digitales públicos.

## Contacto y Soporte

Para soporte técnico:
1. Revisar este README para problemas comunes
2. Consultar documentación de PostGIS y Leaflet
3. Verificar logs del servidor web y PHP
4. Contactar al equipo de desarrollo para casos específicos

**Nota**: Este sistema maneja datos geoespaciales críticos para la agricultura. Cualquier modificación debe ser probada exhaustivamente antes de implementarse en producción.