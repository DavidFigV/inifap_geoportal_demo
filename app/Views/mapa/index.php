<?php
// Configurar variables para el layout
$extraCSS = '
<style>
    #map {
        height: 70vh;
        min-height: 500px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: 2px solid #e9ecef;
    }

    .control-panel {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        max-height: 70vh;
        overflow-y: auto;
    }

    .legend {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: 15px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--gob-wine);
    }

    .badge-potencial {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .badge-alto { background-color: var(--gob-green); }
    .badge-medio { background-color: var(--gob-gold); color: #000; }
    .badge-bajo { background-color: #dc3545; }
    .badge-sin { background-color: #6c757d; }

    .loading {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
</style>
';

$current_page = 'mapa';

// Incluir header con navbar
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>

<!-- Toast Container -->
<div class="toast-container"></div>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Panel de Control -->
        <div class="col-lg-3 col-md-4">
            <div class="control-panel p-3">
                <h5 class="text-gob-wine mb-3 d-flex align-items-center">
                    <i class="bi bi-sliders me-2"></i>Panel de Control
                </h5>
                
                <!-- Selector de Cultivos -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Cultivo:</label>
                    <select class="form-select" id="cultivoSelector">
                        <option value="">Cargando...</option>
                    </select>
                </div>

                <!-- Filtros de Potencial -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Filtrar por Potencial:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="filtroAlto" checked>
                        <label class="form-check-label" for="filtroAlto">
                            <span class="badge badge-potencial badge-alto">Alto</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="2" id="filtroMedio" checked>
                        <label class="form-check-label" for="filtroMedio">
                            <span class="badge badge-potencial badge-medio">Medio</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="3" id="filtroBajo" checked>
                        <label class="form-check-label" for="filtroBajo">
                            <span class="badge badge-potencial badge-bajo">Bajo</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="0" id="filtroSin" checked>
                        <label class="form-check-label" for="filtroSin">
                            <span class="badge badge-potencial badge-sin">Sin Potencial</span>
                        </label>
                    </div>
                </div>

                <!-- Configuración del Mapa -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Configuración:</label>
                    <div class="mb-2">
                        <label for="limitePol" class="form-label small">Polígonos a mostrar:</label>
                        <select class="form-select form-select-sm" id="limitePol">
                            <option value="50">50 (Rápido)</option>
                            <option value="100" selected>100 (Recomendado)</option>
                            <option value="200">200 (Detallado)</option>
                            <option value="500">500 (Completo)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="simplificacion" class="form-label small">Simplificación:</label>
                        <select class="form-select form-select-sm" id="simplificacion">
                            <option value="0.001">Alta (Rápido)</option>
                            <option value="0.0001" selected>Media (Balance)</option>
                            <option value="0.00001">Baja (Detalle)</option>
                        </select>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-2">
                    <button class="btn btn-gob-primary btn-sm" id="btnActualizar" onclick="cargarDatos()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar Mapa
                    </button>
                    <button class="btn btn-gob-secondary btn-sm" onclick="centrarMapa()">
                        <i class="bi bi-geo-alt me-1"></i>Centrar en Zacatecas
                    </button>
                </div>
            </div>

            <!-- Estadísticas -->
            <div id="estadisticasPanel" class="mt-3">
                <!-- Se carga dinámicamente -->
            </div>

            <!-- Leyenda -->
            <div class="legend">
                <h6 class="fw-bold mb-3">Leyenda - Potencial Agrícola</h6>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #2E7D32;"></div>
                    <span><strong>Alto:</strong> Condiciones óptimas</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FFA726;"></div>
                    <span><strong>Medio:</strong> Condiciones moderadas</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #EF5350;"></div>
                    <span><strong>Bajo:</strong> Condiciones limitadas</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #9E9E9E;"></div>
                    <span><strong>Sin Potencial:</strong> No recomendado</span>
                </div>
            </div>
        </div>

        <!-- Mapa Principal -->
        <div class="col-lg-9 col-md-8">
            <div class="position-relative">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="text-gob-wine mb-1">Mapa de Potencial Agrícola</h3>
                        <p class="text-muted mb-0" id="infoMapa">Zacatecas, México</p>
                    </div>
                    <div id="loadingIndicator" class="d-none">
                        <div class="spinner-border spinner-border-sm me-2" role="status" style="border-color: var(--gob-wine); border-right-color: transparent;"></div>
                        <span class="text-muted">Cargando...</span>
                    </div>
                </div>
                
                <div id="map">
                    <div class="loading">
                        <div class="spinner-border mb-3" role="status" style="border-color: var(--gob-wine); border-right-color: transparent;">
                            <span class="visually-hidden">Cargando mapa...</span>
                        </div>
                        <p>Inicializando mapa...</p>
                    </div>
                </div>
            </div>

            <!-- Panel de Información -->
            <div id="infoPanel" class="card mt-3 d-none">
                <div class="card-header bg-gob-wine text-white">
                    <h6 class="mb-0 d-flex justify-content-between align-items-center">
                        <span>Información de la Zona Seleccionada</span>
                        <button type="button" class="btn-close btn-close-white" onclick="cerrarInfoPanel()"></button>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="infoContent">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraJS = '
<script src="/assets/js/mapa.js"></script>
';

include __DIR__ . '/../layouts/footer.php';
?>