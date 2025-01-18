<?php
class Database {
    private $host = "localhost";
    private $db_name = "ceBUS";
    private $username = "root";
    private $password = "summitWATER2024!";
    public $conn;

    public function dbConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

$db = new Database();
$conn = $db->dbConnection();
?>
