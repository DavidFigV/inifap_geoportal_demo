<?php
namespace Core;

use App\Config\Database;

class Model {
    protected $pdo;
    protected $table;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    protected function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function all() {
        $stmt = $this->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function find($id) {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}