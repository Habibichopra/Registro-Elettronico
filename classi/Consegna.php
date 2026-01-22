<?php 
require_once __DIR__ . '/Database.php';

class Consegna {
    private $conn;
    private $nome_tabella = "consegne";
    private $tabella_compiti = "compiti";
    private $tabella_users = "users";
    private $tabella_corsi = "corsi";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

}

?>