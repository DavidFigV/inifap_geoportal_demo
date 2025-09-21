// public/assets/js/mapa.js - Versión con debug completo

// Variables globales
let map;
let currentLayer = null;
let cultivoActual = 'frijol';
let isLoading = false;

// Configuración del mapa
const MAP_CONFIG = {
    center: [22.7709, -102.5832], // Zacatecas centro
    zoom: 10,
    maxZoom: 18,
    minZoom: 8
};

// Colores por potencial
const COLORES_POTENCIAL = {
    1: '#2E7D32', // Alto - Verde
    2: '#FFA726', // Medio - Naranja  
    3: '#EF5350', // Bajo - Rojo
    0: '#9E9E9E'  // Sin potencial - Gris
};

// Debug: Log inicial
console.log('🚀 Iniciando aplicación INIFAP Geoportal');

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM cargado, iniciando componentes...');
    
    // Verificar que Leaflet esté disponible
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet no está cargado');
        mostrarError('Error: Leaflet no está disponible');
        return;
    }
    
    inicializarMapa();
    cargarCultivos();
    configurarEventListeners();
});

function inicializarMapa() {
    console.log('🗺️ Inicializando mapa...');
    
    try {
        // Crear mapa
        map = L.map('map').setView(MAP_CONFIG.center, MAP_CONFIG.zoom);
        console.log('✅ Mapa base creado');
        
        // Capa base
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: MAP_CONFIG.maxZoom
        }).addTo(map);
        console.log('✅ Capa base agregada');

        // Configurar eventos del mapa
        map.on('click', function(e) {
            console.log(`🖱️ Click en mapa: ${e.latlng.lat}, ${e.latlng.lng}`);
            consultarPunto(e.latlng.lat, e.latlng.lng);
        });

        // Remover indicador de carga inicial
        setTimeout(() => {
            const loadingElement = document.querySelector('#map .loading');
            if (loadingElement) {
                loadingElement.style.display = 'none';
                console.log('✅ Indicador de carga removido');
            }
        }, 1000);
        
        console.log('✅ Mapa inicializado correctamente');
        
    } catch (error) {
        console.error('❌ Error inicializando mapa:', error);
        mostrarError('Error al inicializar el mapa');
    }
}

async function cargarCultivos() {
    console.log('🌱 Cargando cultivos disponibles...');
    
    try {
        const response = await fetch('/api/cultivos');
        console.log('📡 Respuesta de API cultivos:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const cultivos = await response.json();
        console.log('📊 Cultivos recibidos:', cultivos);
        
        const selector = document.getElementById('cultivoSelector');
        if (!selector) {
            throw new Error('Selector de cultivos no encontrado en el DOM');
        }
        
        selector.innerHTML = '';
        
        Object.entries(cultivos).forEach(([key, cultivo]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = cultivo.nombre + (cultivo.activo ? '' : ' (Próximamente)');
            option.disabled = !cultivo.activo;
            option.selected = key === 'frijol';
            selector.appendChild(option);
        });
        
        console.log('✅ Selector de cultivos poblado');

        // Cargar datos iniciales
        await cargarDatos();
        await cargarEstadisticas();
        
    } catch (error) {
        console.error('❌ Error cargando cultivos:', error);
        mostrarError('Error al cargar cultivos disponibles: ' + error.message);
    }
}

async function cargarDatos() {
    if (isLoading) {
        console.log('⏳ Ya hay una carga en progreso, saltando...');
        return;
    }
    
    console.log('📦 Iniciando carga de datos del mapa...');
    isLoading = true;
    mostrarCargando(true);
    
    try {
        const cultivo = document.getElementById('cultivoSelector')?.value || 'frijol';
        const limite = document.getElementById('limitePol')?.value || 100;
        const simplify = document.getElementById('simplificacion')?.value || 0.0001;
        const filtros = obtenerFiltrosPotencial();
        
        let url = `/api/cultivos/${cultivo}/geojson?limit=${limite}&simplify=${simplify}`;
        if (filtros.length > 0) {
            url += `&potencial=${filtros.join(',')}`;
        }
        
        console.log('🔗 URL de solicitud:', url);
        console.log('🎯 Filtros aplicados:', filtros);

        const response = await fetch(url);
        console.log('📡 Respuesta de GeoJSON:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const geojson = await response.json();
        console.log('📊 GeoJSON recibido:', {
            type: geojson.type,
            features: geojson.features?.length || 0,
            metadata: geojson.metadata
        });
        
        if (!geojson.features || geojson.features.length === 0) {
            console.warn('⚠️ No hay features en el GeoJSON');
            mostrarToast('No se encontraron datos para los filtros seleccionados', 'warning');
            return;
        }
        
        // Limpiar capa anterior
        if (currentLayer) {
            console.log('🧹 Removiendo capa anterior');
            map.removeLayer(currentLayer);
        }
        
        // Agregar nueva capa
        console.log('➕ Agregando nueva capa con', geojson.features.length, 'features');
        currentLayer = L.geoJSON(geojson, {
            style: stylePolygon,
            onEachFeature: onEachFeature
        }).addTo(map);
        
        console.log('✅ Capa agregada al mapa');

        // Ajustar vista si hay datos
        if (geojson.features && geojson.features.length > 0) {
            const bounds = currentLayer.getBounds();
            console.log('🎯 Ajustando vista a bounds:', bounds);
            map.fitBounds(bounds, { padding: [20, 20] });
        }

        // Actualizar información
        actualizarInfoMapa(geojson);
        
        mostrarToast(`Cargados ${geojson.features?.length || 0} polígonos`, 'success');
        console.log('✅ Datos cargados exitosamente');
        
    } catch (error) {
        console.error('❌ Error cargando datos:', error);
        mostrarError('Error al cargar datos del mapa: ' + error.message);
    } finally {
        isLoading = false;
        mostrarCargando(false);
    }
}

async function cargarEstadisticas() {
    console.log('📈 Cargando estadísticas...');
    
    try {
        const cultivo = document.getElementById('cultivoSelector')?.value || 'frijol';
        const response = await fetch(`/api/cultivos/${cultivo}/estadisticas`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📊 Estadísticas recibidas:', data);
        
        mostrarEstadisticas(data.estadisticas);
        
    } catch (error) {
        console.error('❌ Error cargando estadísticas:', error);
    }
}

function stylePolygon(feature) {
    const potencial = feature.properties.potencial_numero;
    const color = COLORES_POTENCIAL[potencial] || '#666666';
    
    console.log(`🎨 Estilizando polígono - Potencial: ${potencial}, Color: ${color}`);
    
    return {
        fillColor: color,
        weight: 1,
        opacity: 1,
        color: 'white',
        fillOpacity: 0.7,
        className: 'clickable-polygon'
    };
}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: function(e) {
            console.log('🖱️ Mouseover en polígono:', feature.properties.gid);
            e.target.setStyle({
                weight: 3,
                color: '#666',
                fillOpacity: 0.9
            });
            e.target.bringToFront();
        },
        mouseout: function(e) {
            currentLayer.resetStyle(e.target);
        },
        click: function(e) {
            console.log('🖱️ Click en polígono:', feature.properties);
            mostrarInfoPoligono(feature.properties);
        }
    });

    // Tooltip
    const props = feature.properties;
    const tooltip = `
        <strong>${props.potencial_texto}</strong><br>
        Grid: ${props.gridcode}<br>
        Área: ${props.area_ha} ha
    `;
    layer.bindTooltip(tooltip);
}

function mostrarInfoPoligono(props) {
    console.log('ℹ️ Mostrando info de polígono:', props.gid);
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID:</strong> ${props.gid}</p>
                <p><strong>Grid Code:</strong> ${props.gridcode}</p>
                <p><strong>Cultivo:</strong> ${props.cultivo}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Potencial:</strong> <span class="badge badge-potencial badge-${props.potencial_numero === 1 ? 'alto' : props.potencial_numero === 2 ? 'medio' : props.potencial_numero === 3 ? 'bajo' : 'sin'}">${props.potencial_texto}</span></p>
                <p><strong>Área:</strong> ${props.area_ha} hectáreas</p>
                <p><strong>Área:</strong> ${Number(props.area_m2).toLocaleString()} m²</p>
            </div>
        </div>
    `;
    
    document.getElementById('infoContent').innerHTML = content;
    document.getElementById('infoPanel').classList.remove('d-none');
}

function mostrarEstadisticas(stats) {
    console.log('📊 Mostrando estadísticas:', stats);
    
    const panel = document.getElementById('estadisticasPanel');
    if (!panel) {
        console.warn('⚠️ Panel de estadísticas no encontrado');
        return;
    }
    
    let html = '<h6 class="text-gob-wine fw-bold mb-3">Estadísticas por Potencial</h6>';
    
    stats.forEach(stat => {
        const badgeClass = stat.potencial_numero === 1 ? 'badge-alto' : 
                         stat.potencial_numero === 2 ? 'badge-medio' : 
                         stat.potencial_numero === 3 ? 'badge-bajo' : 'badge-sin';
        
        html += `
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge badge-potencial ${badgeClass}">${stat.nivel}</span>
                    <strong>${stat.cantidad_poligonos} zonas</strong>
                </div>
                <div class="small text-muted">
                    <div>Área total: ${Number(stat.area_total_ha).toLocaleString()} ha</div>
                    <div>Área promedio: ${Number(stat.area_promedio_m2 / 10000).toFixed(2)} ha</div>
                </div>
            </div>
        `;
    });
    
    panel.innerHTML = html;
}

async function consultarPunto(lat, lon) {
    console.log(`📍 Consultando punto: ${lat}, ${lon}`);
    
    try {
        const response = await fetch(`/api/potencial?lat=${lat}&lon=${lon}`);
        const data = await response.json();
        
        console.log('📡 Respuesta consulta punto:', data);
        
        if (data.encontrado) {
            const info = data.potencial;
            mostrarToast(`Punto consultado: ${info.potencial_texto} (${info.area_ha} ha)`, 'info');
        } else {
            mostrarToast('No se encontró información para esta ubicación', 'warning');
        }
    } catch (error) {
        console.error('❌ Error consultando punto:', error);
    }
}

function obtenerFiltrosPotencial() {
    const filtros = [];
    ['filtroAlto', 'filtroMedio', 'filtroBajo', 'filtroSin'].forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && checkbox.checked) {
            filtros.push(checkbox.value);
        }
    });
    
    console.log('🔍 Filtros obtenidos:', filtros);
    return filtros;
}

function configurarEventListeners() {
    console.log('⚙️ Configurando event listeners...');
    
    // Cambio de cultivo
    const cultivoSelector = document.getElementById('cultivoSelector');
    if (cultivoSelector) {
        cultivoSelector.addEventListener('change', function() {
            console.log('🌱 Cambio de cultivo a:', this.value);
            cultivoActual = this.value;
            cargarDatos();
            cargarEstadisticas();
        });
    }

    // Cambio de filtros
    ['filtroAlto', 'filtroMedio', 'filtroBajo', 'filtroSin'].forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                console.log(`🔍 Cambio de filtro ${id}:`, this.checked);
                cargarDatos();
            });
        }
    });
    
    console.log('✅ Event listeners configurados');
}

function centrarMapa() {
    console.log('🎯 Centrando mapa en Zacatecas');
    if (map) {
        map.setView(MAP_CONFIG.center, MAP_CONFIG.zoom);
    }
}

function toggleFullscreen() {
    const mapElement = document.getElementById('map');
    if (mapElement.style.height === '100vh') {
        mapElement.style.height = '70vh';
        document.querySelector('.col-lg-3').style.display = 'block';
    } else {
        mapElement.style.height = '100vh';
        document.querySelector('.col-lg-3').style.display = 'none';
    }
    setTimeout(() => map.invalidateSize(), 100);
}

function cerrarInfoPanel() {
    document.getElementById('infoPanel').classList.add('d-none');
}

function mostrarCargando(mostrar) {
    const indicator = document.getElementById('loadingIndicator');
    const btn = document.getElementById('btnActualizar');
    
    if (mostrar) {
        if (indicator) indicator.classList.remove('d-none');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando...';
        }
    } else {
        if (indicator) indicator.classList.add('d-none');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Actualizar Mapa';
        }
    }
}

function actualizarInfoMapa(geojson) {
    const metadata = geojson.metadata || {};
    const infoText = `${metadata.total_returned || 0} polígonos mostrados de ${metadata.total_features || 0} total`;
    const infoElement = document.getElementById('infoMapa');
    if (infoElement) {
        infoElement.textContent = infoText;
    }
}

function mostrarToast(mensaje, tipo = 'info') {
    console.log(`📢 Toast ${tipo}: ${mensaje}`);
    
    const container = document.querySelector('.toast-container');
    if (!container) {
        console.warn('⚠️ Contenedor de toasts no encontrado');
        // Fallback: usar alert
        alert(mensaje);
        return;
    }
    
    const toastId = 'toast-' + Date.now();
    
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header">
                <div class="rounded me-2 bg-${tipo}" style="width: 20px; height: 20px;"></div>
                <strong class="me-auto">INIFAP Geoportal</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${mensaje}</div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHTML);
    
    // Verificar si Bootstrap está disponible
    if (typeof bootstrap !== 'undefined') {
        const toastElement = new bootstrap.Toast(document.getElementById(toastId));
        toastElement.show();
    } else {
        console.warn('⚠️ Bootstrap no disponible, usando fallback');
        alert(mensaje);
    }
}

function mostrarError(mensaje) {
    console.error('🚨 Error:', mensaje);
    mostrarToast(mensaje, 'danger');
}

function mostrarAyuda() {
    const mensaje = `
        <strong>Uso del Geoportal:</strong><br>
        • Selecciona un cultivo del menú desplegable<br>
        • Usa los filtros para mostrar diferentes niveles de potencial<br>
        • Haz clic en cualquier polígono para ver detalles<br>
        • Haz clic en el mapa para consultar información de un punto específico<br>
        • Ajusta la configuración para optimizar la visualización
    `;
    mostrarToast(mensaje, 'info');
}

function exportarDatos() {
    mostrarToast('Funcionalidad de exportación en desarrollo', 'warning');
}

function imprimirMapa() {
    window.print();
}

// Redimensionar mapa cuando cambia el tamaño de ventana
window.addEventListener('resize', () => {
    if (map) {
        console.log('🖥️ Redimensionando mapa');
        map.invalidateSize();
    }
});

// Log final
console.log('📜 Script mapa.js cargado completamente');