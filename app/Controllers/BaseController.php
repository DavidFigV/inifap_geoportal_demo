<?php
namespace App\Controllers;

use Core\Controller;

class BaseController extends Controller {
    protected function validateRequest($required = [], $data = null) {
        $data = $data ?? $_REQUEST;
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->json([
                'error' => 'Campos requeridos faltantes',
                'missing' => $missing
            ], 400);
        }
        
        return $data;
    }
    
    protected function handleException(\Exception $e) {
        if ($_ENV['APP_DEBUG'] === 'true') {
            $this->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        } else {
            $this->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}