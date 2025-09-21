<?php
namespace App\Controllers;

use App\Models\Cultivo;

class MapController extends BaseController {
    private $cultivoModel;
    
    public function __construct() {
        $this->cultivoModel = new Cultivo();
    }
    
    public function index() {
        $cultivos = $this->cultivoModel->getCultivosDisponibles();
        
        $this->view('mapa/index', [
            'title' => 'Geoportal Agrícola - INIFAP Zacatecas',
            'cultivos' => $cultivos,
            'current_page' => 'mapa'
        ]);
    }
    
    public function viewer($cultivo = 'frijol') {
        $cultivos = $this->cultivoModel->getCultivosDisponibles();
        
        if (!isset($cultivos[$cultivo]) || !$cultivos[$cultivo]['activo']) {
            $this->redirect('/mapa');
        }
        
        $estadisticas = $this->cultivoModel->getEstadisticas($cultivo);
        
        $this->view('mapa/viewer', [
            'title' => "Mapa de {$cultivos[$cultivo]['nombre']} - INIFAP Zacatecas",
            'cultivo_actual' => $cultivo,
            'cultivos' => $cultivos,
            'estadisticas' => $estadisticas,
            'current_page' => 'mapa'
        ]);
    }
}