<?php
class MysqlObjectDatabase
{
    private $conn;
    public function __construct($host, $port, $username, $password, $database)
    {
        $this->conn = new mysqli($host, $username, $password, $database, $port);
    }

    public function query($sql) {
        $result = $this->conn->query($sql);
        if ($result === false) {
            return false; 
        }
    
        if (preg_match('/^SELECT/i', $sql)) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        }
    
        if (preg_match('/^INSERT/i', $sql)) {
            return $this->conn->insert_id;
        }
    
        
        return true;
    }
    


    public function execute($sql){
        $this->conn->query($sql);
        return $this->conn->affected_rows;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
