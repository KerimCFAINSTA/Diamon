<?php
require_once __DIR__ . '/../includes/db.php';

class Grade {
    
    /**
     * Récupère tous les grades
     */
    public static function getAll() {
        global $pdo;
        
        $stmt = $pdo->query("SELECT * FROM grade ORDER BY code DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un grade par son code
     */
    public static function getByCode($code) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM grade WHERE code = :code");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch();
    }
}