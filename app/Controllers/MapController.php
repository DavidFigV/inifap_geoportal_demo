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
            'cultivos' => $cultivos
        ]);
    }
    
    public function viewer($cultivo = 'frijol') {
        $cultivos = $this->cultivoModel->getCultivosDisponibles();
        
        if (!isset($cultivos[$cultivo])) {
            $this->redirect('/mapa');
        }
        
        $estadisticas = $this->cultivoModel->getEstadisticas($cultivo);
        
        $this->view('mapa/viewer', [
            'title' => "Mapa de {$cultivos[$cultivo]['nombre']}",
            'cultivo_actual' => $cultivo,
            'cultivos' => $cultivos,
            'estadisticas' => $estadisticas
        ]);
    }
}