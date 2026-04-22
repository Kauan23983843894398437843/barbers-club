<?php
/**
 * Classe para gerenciar conexões com o banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class Database {
    private $conn;
    private static $instance;

    /**
     * Construtor privado para implementar o padrão Singleton
     */
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Obtém uma instância da classe Database (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtém a conexão com o banco de dados
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Executa uma consulta SQL com parâmetros
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erro na consulta: " . $e->getMessage());
        }
    }

    /**
     * Obtém um único registro do banco de dados
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return array|false
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Obtém todos os registros do banco de dados
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Insere um registro no banco de dados
     * 
     * @param string $table Nome da tabela
     * @param array $data Dados a serem inseridos
     * @return int ID do registro inserido
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return $this->conn->lastInsertId();
    }

    /**
     * Atualiza um registro no banco de dados
     * 
     * @param string $table Nome da tabela
     * @param array $data Dados a serem atualizados
     * @param string $where Condição WHERE
     * @param array $whereParams Parâmetros para a condição WHERE
     * @return int Número de registros afetados
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * Exclui um registro do banco de dados
     * 
     * @param string $table Nome da tabela
     * @param string $where Condição WHERE
     * @param array $params Parâmetros para a condição WHERE
     * @return int Número de registros afetados
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
?>