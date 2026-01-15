<?php 

require_once __DIR__ . '/Database.php';
class Voto{
    private $conn;
    private $nome_tabella = "voti";
    private $tabella_corsi = "corsi";
    private $tabella_users = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    //inserimento voto
    public function addVoto($studente_id, $corso_id, $tipo, $voto, $note) {

    }

    //aggiorna voto
    public function aggiornaVoto($id, $voto, $note) {

    }

    //eliminazione del voto
    public function eliminaVoto($id) {
    
    }

    //get voti di un studente
    public function getVotiByStudente($studente_id, $corso_id = null) {

    }

    //calcola media voti studente
    public function calcolaMedia($studente_id, $corso_id = null) {
    
    }

    //statistiche del corso
    public function getStatisticheCorso($corso_id) {

    }

    //generazione pagella in formato CSV
    public function generatePagella($studente_id) {
    
    }
}

?>