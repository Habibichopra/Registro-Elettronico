<?php

require_once __DIR__ . '/Database.php';

class Materiale {
    private $conn;
    private $nome_tabella = "materiali";


    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    //caricamento nuovo materiale
    public function caricaMateriale($corso_id, $titolo, $descrizione, $tipo, $file) {
        $cartellaDestinazione = __DIR__ . "/../importazioni/materiali/";
        
        if (!file_exists($cartellaDestinazione)) {
            mkdir($cartellaDestinazione, 0755, true);
        }

        $estensioneFile = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $titoloPulito = preg_replace('/[^A-Za-z0-9\-]/', '_', $titolo);
        $nomeNuovoFile = $corso_id . "_" . $titoloPulito . "_" . time() . "." . $estensioneFile;
        
        $percorsoFileServer = $cartellaDestinazione . $nomeNuovoFile;
        $percorsoFileDatabase = "importazioni/materiali/" . $nomeNuovoFile;

        if (move_uploaded_file($file["tmp_name"], $percorsoFileServer)) {
            
            $query = "INSERT INTO " . $this->nome_tabella . " 
                      (corso_id, titolo, descrizione, tipo, file_path) 
                      VALUES (:cid, :titolo, :desc, :tipo, :path)";
            
            $stmt = $this->conn->prepare($query);

        
            $titolo = htmlspecialchars(strip_tags($titolo));
            $descrizione = htmlspecialchars(strip_tags($descrizione));
            $tipo = htmlspecialchars(strip_tags($tipo));

            $stmt->bindParam(":cid", $corso_id);
            $stmt->bindParam(":titolo", $titolo);
            $stmt->bindParam(":desc", $descrizione);
            $stmt->bindParam(":tipo", $tipo);
            $stmt->bindParam(":path", $percorsoFileDatabase);

            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }

    //eliminazione materiale
    public function eliminaMateriale($id) {

    }

    //ottenere tutti i materiali di un corso
    public function getMaterialiByCorso($corso_id) {

    }

    //download materiale
    public function downloadMateriale($id) {

    }
}

?>