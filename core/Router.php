<?php
namespace Core;

class Router {
    private $routes = [];
    
    public function get($pattern, $callback) {
        $this->routes['GET'][$pattern] = $callback;
    }
    
    public function post($pattern, $callback) {
        $this->routes['POST'][$pattern] = $callback;
    }
    
    public function resolve($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes[$method] ?? [] as $pattern => $callback) {
            if (preg_match("#^{$pattern}$#", $uri, $matches)) {
                array_shift($matches);
                
                if (is_array($callback)) {
                    [$controller, $action] = $callback;
                    $controller = new $controller();
                    return call_user_func_array([$controller, $action], $matches);
                }
                
                return call_user_func_array($callback, $matches);
            }
        }
        
        throw new \Exception('Ruta no encontrada', 404);
    }
}