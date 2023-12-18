<?php

class CrudDinamic
{

    private $servername = 'localhost';
    private $username = 'root';
    private $password = '';
    private $dbname = 'task_db';
    private $pdo;

    public function __construct()
    {
        $this->connectDatabase();
    }

    private function connectDatabase()
    {
        $dsn = 'mysql:host=' . $this->servername . ';dbname=' . $this->dbname;

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    private function validateColumns($table, $columns)
    {
        $stmt = $this->pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $tableColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        return empty(array_diff($columns, $tableColumns));
    }

    public function insertRecord($table, $data)
    {
        $validColumns = $this->validateColumns($table, array_keys($data));
        if (!$validColumns) {
            die("Invalid column(s) provided.");
        }

        $columns = implode(',', array_keys($data));
        $values = implode(',', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table($columns) VALUES($values)";

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt) {
            die("Error in prepared statement: " . print_r($this->pdo->errorInfo(), true));
        }

        $result = $stmt->execute(array_values($data));
        $stmt->closeCursor();

        return $result;
    }


    function updateRecord($table, $data, $id)
    {
        // Use prepared statements to prevent SQL injection
        $args = array();

        foreach ($data as $key => $value) {
            $args[] = "$key = ?";
        }

        $sql = "UPDATE $table SET " . implode(',', $args) . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt) {
            die("Error in prepared statement: " . print_r($this->pdo->errorInfo(), true));
        }

        // Bind parameters to the prepared statement
        // $types = str_repeat('s', count($data) + 1);
        // $params = array_values($data);
        // $params[] = $id;
        // mysqli_stmt_bind_param($stmt, $types, ...$params);

        // Execute the prepared statement
        $result = $stmt->execute(array_merge(array_values($data), [$id]));
        $stmt->closeCursor();

        // Close the statement
        // mysqli_stmt_close($stmt);

        return $result;
    }

    function deleteRecord($table, $id)
    {
        // Use prepared statements to prevent SQL injection
        $sql = "DELETE FROM $table WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt) {
            die("Error in prepared statement: " . print_r($this->pdo->errorInfo(), true));
        }

        // Bind parameter to the prepared statement
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        // Execute the prepared statement
        $result = $stmt->execute();

        // Close the statement
        $stmt->closeCursor();

        return $result;
    }


    function selectRecords($table, $columns = "*", $where = null)
    {
        // Use prepared statements to prevent SQL injection
        $sql = "SELECT $columns FROM $table";

        if ($where !== null) {
            $sql .= " WHERE $where";
        }

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt) {
            die("Error in prepared statement: " . print_r($this->pdo->errorInfo(), true));
        }

        $result = $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $data;

    }

}
$crud = new CrudDinamic();

// Insert example
$insertData = array('name' => 'Bilal', 'email' => 'jojjjn@example.com');
$crud->insertRecord('users', $insertData);

// Update example
$updateData = array('name' => 'Barcaa', 'email' => 'new_email@example.com');
$crud->updateRecord('users', $updateData, 15);


// Delete example
$crud->deleteRecord('users', 15);
// $selectResult = $crud->selectRecords('users', 'id', 'name');
