<?php
// student_ms/database/database.php

class Database {
    private static $instance = null;
    private $conn;
    private $host = 'localhost';
    private $user = 'root'; // !! IMPORTANT: Change to a less privileged user in production
    private $pass = '';     // !! IMPORTANT: Change to a strong password in production
    private $name = 'student_ms';

    /**
     * Private constructor to prevent direct instantiation.
     * Establishes a PDO connection to the database.
     */
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4",
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Default fetch mode to associative array
                    PDO::ATTR_EMULATE_PREPARES => false                   // Disable emulation for better security and performance
                ]
            );
        } catch (PDOException $e) {
            // Log the detailed error message for debugging purposes
            error_log("Database connection failed: " . $e->getMessage());
            // Throw a generic exception to the calling code to prevent sensitive info leakage
            throw new Exception("Database connection error. Please try again later.");
        }
    }

    /**
     * Get the single instance of the Database class (Singleton pattern).
     *
     * @return Database The single instance of the Database class.
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object.
     *
     * @return PDO The PDO connection object.
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * Prepares a SQL statement for execution.
     * This method is exposed to allow other classes to use prepared statements directly from the Database instance.
     *
     * @param string $sql The SQL query string.
     * @return PDOStatement The prepared statement object.
     */
    public function prepare(string $sql): PDOStatement {
        return $this->conn->prepare($sql);
    }

    /**
     * Begins a transaction.
     *
     * @return bool True on success or false on failure.
     */
    public function beginTransaction(): bool {
        return $this->conn->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool True on success or false on failure.
     */
    public function commit(): bool {
        return $this->conn->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool True on success or false on failure.
     */
    public function rollBack(): bool {
        return $this->conn->rollBack();
    }
}
?>
