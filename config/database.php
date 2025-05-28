<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'container_bar'; // Cambia por el nombre de tu base de datos
    private $username = 'root'; // Cambia por tu usuario
    private $password = ''; // Cambia por tu contraseña
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>