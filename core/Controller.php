<?php
namespace Core;

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require_once "../app/Views/{$view}.php";
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}