<?php
class DB {
    private $mysqli;

    public function __construct($host, $user, $password, $dbname, $charset = 'utf8mb4') {
        $this->mysqli = new mysqli($host, $user, $password, $dbname);
        if ($this->mysqli->connect_error) {
            die('Ошибка подключения: ' . $this->mysqli->connect_error);
        }
        $this->mysqli->set_charset($charset);
    }

    public function query($sql) {
        $result = $this->mysqli->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_map(function($value) {
            return "'" . $this->mysqli->real_escape_string($value) . "'";
        }, array_values($data)));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        $this->mysqli->query($sql);
        return $this->mysqli->insert_id;
    }

    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "`$column` = '" . $this->mysqli->real_escape_string($value) . "'";
        }
        $setStr = implode(", ", $set);
        $sql = "UPDATE `$table` SET $setStr WHERE $where";
        return $this->mysqli->query($sql);
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM `$table` WHERE $where";
        return $this->mysqli->query($sql);
    }

    public function escape($str) {
        return $this->mysqli->real_escape_string($str);
    }

    public function close() {
        $this->mysqli->close();
    }

    public function createTables() {
        $this->mysqli->query("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                article VARCHAR(100),
                price DECIMAL(10,2),
                url VARCHAR(500) UNIQUE
            )
        ");

        $this->mysqli->query("
            CREATE TABLE IF NOT EXISTS attribute_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL
            )
        ");

        $this->mysqli->query("
            CREATE TABLE IF NOT EXISTS attributes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                FOREIGN KEY (category_id) REFERENCES attribute_categories(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        $this->mysqli->query("
            CREATE TABLE IF NOT EXISTS product_attribute_values (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                attribute_id INT NOT NULL,
                value TEXT,
                FOREIGN KEY (product_id) REFERENCES products(id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (attribute_id) REFERENCES attributes(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");
    }
}
?>