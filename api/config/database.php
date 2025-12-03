<?php
require_once __DIR__ . '/../../db_connect.php';

class Database {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
