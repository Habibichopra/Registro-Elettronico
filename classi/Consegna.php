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

    //cosnega del compito da parte del stusente
    public function consegnaCompito($compito_id, $studente_id, $file, $note) {

    }

    //valutazione del compito da parte del professore
    public function valutaConsegna($consegna_id, $voto, $feedback) {

    }

    //get consegna in base al id
    public function getConsegnaById($id) {

    }

    //lista delle consegne per un compito specifico
    public function getConsegneByCompito($compito_id) {

    }

    //lista delle consegne per uno studente specifico
    public function getConsegneByStudente($studente_id) {

    } 

    //controllo se la consegna è in ritardo
    public function checkRitardo($consegna_id) {

    }

    //download del file della consegna
    public function downloadConsegna($id) {

    }
}

?>