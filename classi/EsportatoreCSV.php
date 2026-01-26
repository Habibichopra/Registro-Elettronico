<?php
require_once __DIR__ . '/Database.php';

class EsportatoreCSV  {
    private $conn;
    private $export_dir;

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();

         $this->export_dir = __DIR__ . "/../esportazioni/";
        
        if (!file_exists($this->export_dir)) {
            mkdir($this->export_dir, 0755, true);
        }
    }


}

?>