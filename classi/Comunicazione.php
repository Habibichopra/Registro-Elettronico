<?php 
require_once __DIR__ . '/Database.php';

class Comunicazione {
    private $conn;
    private $nome_tabella = "comunicazioni";
    private $tabella_users = "users";
    private $tabella_corsi = "corsi";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }


    public function inviaComunicazione($mittente_id, $destinatario_id, $corso_id, $oggetto, $messaggio) {
    
    }

    //recuparare tutte le comunicazioni per un utente specifico
    public function getComunicazioniByUser($user_id) {

    }

    //sergnare una comunicazione come letta
    public function segnaComeLetto($comunicazione_id) {

    }

    //eliminare una comunicazione
    public function eliminaComunicazione($id) {
    
    }
}

?>