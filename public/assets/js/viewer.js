// Script específico para el viewer
document.addEventListener('DOMContentLoaded', function() {
    inicializarMapaViewer();
    cargarDatosCultivoEspecifico();
});

function inicializarMapaViewer() {
    map = L.map('map').setView([22.7709, -102.5832], 9);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

async function cargarDatosCultivoEspecifico() {
    try {
        const response = await fetch(`/api/cultivos/${CULTIVO_ACTUAL}/geojson?limit=200`);
        const geojson = await response.json();
        
        const layer = L.geoJSON(geojson, {
            style: function(feature) {
                const potencial = feature.properties.potencial_numero;
                const colores = {1: '#2E7D32', 2: '#FFA726', 3: '#EF5350', 0: '#9E9E9E'};
                
                return {
                    fillColor: colores[potencial] || '#666666',
                    weight: 1,
                    opacity: 1,
                    color: 'white',
                    fillOpacity: 0.7
                };
            },
            onEachFeature: function(feature, layer) {
                const props = feature.properties;
                const popup = `
                    <strong>${props.potencial_texto}</strong><br>
                    ID: ${props.gid}<br>
                    Grid: ${props.gridcode}<br>
                    Área: ${props.area_ha} ha
                `;
                layer.bindPopup(popup);
            }
        }).addTo(map);
        
        map.fitBounds(layer.getBounds());
        
    } catch (error) {
        console.error('Error cargando datos específicos:', error);
    }
}