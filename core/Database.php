<?php
// core/Database.php - PHP 7.4 compativel

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erro de conexao com banco de dados: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function query($sql, $params = array()) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne($sql, $params = array()) {
        $result = $this->query($sql, $params)->fetch();
        return $result ? $result : null;
    }

    public function fetchAll($sql, $params = array()) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $cols = implode(',', array_map(function($k) { return "`$k`"; }, $keys));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO `$table` ($cols) VALUES ($placeholders)", array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = array()) {
        $keys = array_keys($data);
        $set  = implode(',', array_map(function($k) { return "`$k`=?"; }, $keys));
        $stmt = $this->query(
            "UPDATE `$table` SET $set WHERE $where",
            array_merge(array_values($data), $whereParams)
        );
        return $stmt->rowCount();
    }

    public function lastId() {
        return (int)$this->pdo->lastInsertId();
    }
}
