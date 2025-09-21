<?php
$extraCSS = '
<style>
    /* Estilos específicos para viewer */
    .breadcrumb {
        background-color: var(--gob-gray);
        border-radius: 8px;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--gob-wine);
    }
    
    .viewer-header {
        background: linear-gradient(135deg, var(--gob-wine), #8b2c56);
        color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .cultivo-badge {
        background-color: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        display: inline-block;
    }
</style>
';

$current_page = 'mapa';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/mapa">Mapa</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($cultivos[$cultivo_actual]['nombre']) ?></li>
        </ol>
    </nav>

    <!-- Header del Cultivo -->
    <div class="viewer-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2"><?= htmlspecialchars($cultivos[$cultivo_actual]['nombre']) ?></h1>
                <p class="mb-3"><?= htmlspecialchars($cultivos[$cultivo_actual]['descripcion']) ?></p>
                <span class="cultivo-badge">
                    <i class="bi bi-geo-alt me-1"></i>
                    Análisis para Zacatecas, México
                </span>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-grid gap-2 d-md-block">
                    <a href="/mapa" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Mapa General
                    </a>
                    <button class="btn btn-light" onclick="exportarDatos()">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Resumidas -->
    <div class="row mb-4">
        <?php foreach ($estadisticas as $stat): ?>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <?php 
                    $badgeClass = $stat['potencial_numero'] == 1 ? 'success' : 
                                 ($stat['potencial_numero'] == 2 ? 'warning' : 
                                 ($stat['potencial_numero'] == 3 ? 'danger' : 'secondary'));
                    ?>
                    <span class="badge bg-<?= $badgeClass ?> fs-6 mb-2"><?= htmlspecialchars($stat['nivel']) ?></span>
                    <h4 class="card-title text-gob-wine"><?= number_format($stat['area_total_ha'], 0) ?> ha</h4>
                    <p class="card-text text-muted"><?= $stat['cantidad_poligonos'] ?> zonas</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Mapa Específico -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gob-wine text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-map me-2"></i>
                        Mapa de Potencial - <?= htmlspecialchars($cultivos[$cultivo_actual]['nombre']) ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraJS = '
<script>
    // Configuración específica para el viewer
    const CULTIVO_ACTUAL = "' . $cultivo_actual . '";
    
    // Datos de estadísticas pasados desde el controlador
    const ESTADISTICAS = ' . json_encode($estadisticas) . ';
</script>
<script src="/assets/js/viewer.js"></script>
';

include __DIR__ . '/../layouts/footer.php';
?>