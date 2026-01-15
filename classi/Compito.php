<?php 
require_once __DIR__ . '/Database.php';

class Compito {
    private $conn;
    private $nome_tabella = "compiti";
    private $tabella_corsi = "corsi";
    private $tabella_iscrizioni = "iscrizioni";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    //creazione di un nuovo compito
    public function creaCompito($corso_id, $titolo, $descrizione, $scadenza, $punti_max, $allegato = null) {
        $query = "INSERT INTO " . $this->nome_tabella . " 
                  (corso_id, titolo, descrizione, data_scadenza, punti_max, allegato) 
                  VALUES (:corso_id, :titolo, :descrizione, :scadenza, :punti_max, :allegato)";
        
        $stmt = $this->conn->prepare($query);

        $titolo = htmlspecialchars(strip_tags($titolo));
        $descrizione = htmlspecialchars(strip_tags($descrizione));
        $scadenza = htmlspecialchars(strip_tags($scadenza));
        $punti_max = htmlspecialchars(strip_tags($punti_max));

        $stmt->bindParam(":corso_id", $corso_id);
        $stmt->bindParam(":titolo", $titolo);
        $stmt->bindParam(":descrizione", $descrizione);
        $stmt->bindParam(":scadenza", $scadenza);
        $stmt->bindParam(":punti_max", $punti_max);
        $stmt->bindParam(":allegato", $allegato);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //aggorna compito
    public function aggiornaCompito($id, $dati) {
        $query = "UPDATE " . $this->nome_tabella . " SET 
                    titolo = :titolo, 
                    descrizione = :descrizione, 
                    data_scadenza = :scadenza, 
                    punti_max = :punti_max";
        
        if (!empty($dati['allegato'])) {
            $query .= ", allegato = :allegato";
        }

        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $titolo = htmlspecialchars(strip_tags($dati['titolo']));
        $descrizione = htmlspecialchars(strip_tags($dati['descrizione']));
        
        $stmt->bindParam(":titolo", $titolo);
        $stmt->bindParam(":descrizione", $descrizione);
        $stmt->bindParam(":scadenza", $dati['data_scadenza']);
        $stmt->bindParam(":punti_max", $dati['punti_max']);
        $stmt->bindParam(":id", $id);

        if (!empty($dati['allegato'])) {
            $stmt->bindParam(":allegato", $dati['allegato']);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //elimina un compito
    public function eliminaCompito($id) {
        $query = "DELETE FROM " . $this->nome_tabella . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //compito in base al id
    public function getCompitoById($id) {
        $query = "SELECT t.*, c.nome_corso 
                  FROM " . $this->nome_tabella . " t
                  JOIN " . $this->tabella_corsi . " c ON t.corso_id = c.id
                  WHERE t.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }

    //compiti in base al corso
    public function getCompitiByCorso($corso_id) {
        $query = "SELECT * FROM " . $this->nome_tabella . " 
                  WHERE corso_id = ? 
                  ORDER BY data_scadenza ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $corso_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //trovare compiti scaduti
    public function getCompitiScaduti($corso_id = null) {
        $query = "SELECT * FROM " . $this->nome_tabella . " WHERE data_scadenza < NOW()";
        
        if ($corso_id) {
            $query .= " AND corso_id = :cid";
        }
        
        $query .= " ORDER BY data_scadenza DESC";
        $stmt = $this->conn->prepare($query);
        if ($corso_id) {
            $stmt->bindParam(":cid", $corso_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //compiti in scadenza nei prossimi giorni
    public function getCompitiProssimi($giorni = 7, $studente_id = null) {
        if ($studente_id) {
            //seleziono compiti dove lo studente è iscritto e la data è futura ma entro X giorni
            $query = "SELECT t.*, c.nome_corso, c.codice_corso 
                      FROM " . $this->nome_tabella . " t
                      JOIN " . $this->tabella_corsi . " c ON t.corso_id = c.id
                      JOIN " . $this->tabella_iscrizioni . " i ON c.id = i.corso_id
                      WHERE i.studente_id = :sid 
                      AND t.data_scadenza BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :giorni DAY)
                      ORDER BY t.data_scadenza ASC";
        } else {
            $query = "SELECT t.*, c.nome_corso 
                      FROM " . $this->nome_tabella . " t
                      JOIN " . $this->tabella_corsi . " c ON t.corso_id = c.id
                      WHERE t.data_scadenza BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :giorni DAY)
                      ORDER BY t.data_scadenza ASC";
        }

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":giorni", $giorni);
        if ($studente_id) {
            $stmt->bindParam(":sid", $studente_id);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>