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

    //metodo per aprire un file csv
    private function apriFileCSV($nomeFile) {
        $percorsoFile = $this->export_dir . $nomeFile;
        $file = fopen($percorsoFile, 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        return $file;
    }

    //Genera CSV con i voti di uno studente specifico
    public function exportVotiStudente($studente_id) {
        $query = "SELECT c.nome_corso, c.codice_corso, v.tipo_valutazione, v.voto, v.data_voto, v.note 
                  FROM voti v
                  JOIN corsi c ON v.corso_id = c.id
                  WHERE v.studente_id = ?
                  ORDER BY v.data_voto DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $studente_id);
        $stmt->execute();
        
        $nomeFile = "voti_studente_" . $studente_id . "_" . time() . ".csv";
        $file = $this->apriFileCSV($nomeFile);

        fputcsv($file, array('Corso', 'Codice', 'Tipo', 'Voto', 'Data', 'Note'));

        while ($riga = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($file, $riga);
        }

        fclose($file);
        return $nomeFile;
    }

    //Metodo per esportare le presenze di un corso specifico in un file CSV
    public function exportPresenze($corso_id) {
        $query = "SELECT u.matricola, u.cognome, u.nome, u.email, i.data_iscrizione 
                  FROM iscrizioni i
                  JOIN users u ON i.studente_id = u.id
                  WHERE i.corso_id = ? AND i.status = 'attivo'
                  ORDER BY u.cognome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $corso_id);
        $stmt->execute();

        $nomeFile = "elenco_iscritti_corso_" . $corso_id . "_" . time() . ".csv";
        $file = $this->apriFileCSV($nomeFile);

        fputcsv($file, array('Matricola', 'Cognome', 'Nome', 'Email', 'Data Iscrizione', 'Firma Lezione 1', 'Firma Lezione 2', 'Firma Lezione 3'));

        while ($riga = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $riga = array_values($riga); 
            $riga[] = "";
            $riga[] = ""; 
            $riga[] = ""; 
            fputcsv($file, $riga);
        }

        fclose($file);
        return $nomeFile;
    }

    //genera statistcihe corso con csv
    public function exportStatisticheCorso($corso_id) {
        $query = "SELECT 
                    AVG(voto) as media,
                    MAX(voto) as voto_massimo,
                    MIN(voto) as voto_minimo,
                    COUNT(*) as numero_voti
                  FROM voti WHERE corso_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $corso_id);
        $stmt->execute();
        $statistiche = $stmt->fetch(PDO::FETCH_ASSOC);

        $nomeFile = "statistiche_corso_" . $corso_id . "_" . time() . ".csv";
        $file = $this->apriFileCSV($nomeFile);

        fputcsv($file, array('Indicatore', 'Valore'));
        
        // Scriviamo i dati in verticale
        fputcsv($file, array('Media Voti', round($statistiche['media'], 2)));
        fputcsv($file, array('Voto Più Alto', $statistiche['voto_massimo']));
        fputcsv($file, array('Voto Più Basso', $statistiche['voto_minimo']));
        fputcsv($file, array('Totale Valutazioni', $statistiche['numero_voti']));

        fclose($file);
        return $nomeFile;
    }

    //importa studenti da un file csv
    public function importaStudentiDaCSV($file_input) {
        if (!is_uploaded_file($file_input['tmp_name'])) {
            return ["successo" => false, "messaggio" => "Nessun file caricato."];
        }

        $gestoreFile  = fopen($file_input['tmp_name'], "r");
        if ($gestoreFile  === FALSE) {
            return ["successo" => false, "messaggio" => "Impossibile leggere il file."];
        }

        $numeroImportati  = 0;
        $errori = [];
        $numeroRiga = 0;


        $sql = "INSERT INTO users (username, password_hash, email, nome, cognome, matricola, ruolo) 
                VALUES (:user, :pass, :email, :nome, :cognome, :matricola, 'studente')";
        $stmt = $this->conn->prepare($sql);

        while (($dati = fgetcsv($gestoreFile, 1000, ",")) !== FALSE) {
            $numeroRiga++;
            
            if ($numeroRiga == 1 && strtolower($dati[0]) == 'username') {
                continue; 
            }

            if (count($dati) < 6) {
                $errori[] = "Riga $numeroRiga: Colonne insufficienti.";
                continue;
            }

            $username = trim($dati[0]);
            $passwordBase = trim($dati[1]);
            $email = trim($dati[2]);
            $nome = trim($dati[3]);
            $cognome = trim($dati[4]);
            $matricola = trim($dati[5]);

            $password_hash = password_hash($passwordBase, PASSWORD_BCRYPT);

            try {
                $stmt->bindParam(':user', $username);
                $stmt->bindParam(':pass', $password_hash);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':cognome', $cognome);
                $stmt->bindParam(':matricola', $matricola);

                if ($stmt->execute()) {
                    $numeroImportati++;
                }
            } catch (PDOException $e) {
                $errori[] = "Riga $numeroRiga ($username): Errore inserimento (probabile duplicato).";
            }
        }

        fclose($gestoreFile);

        return [
            "successo" => true,
            "importato" => $numeroImportati,
            "errori" => $errori
        ];
    } 
}



?>