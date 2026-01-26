<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classsi/Corso.php';
require_once '../classsi/Compito.php';
require_once '../classsi/Consegna.php';

$studente_id = $_SESSION['user_id'];

$corsoObj = new Corso();
$compitoObj = new Compito();
$consegnaObj = new Consegna();

$corsi = $corsoObj->getCorsiByStudente($studente_id);
$tutte_consegne = $consegnaObj->getConsegneByStudente($studente_id);

$mappa_consegne = [];
foreach ($tutte_consegne as $c) {
    $mappa_consegne[$c['compito_id']] = $c;
}

$lista_da_fare = [];
$lista_storico = [];

foreach ($corsi as $corso) {
    $compiti_corso = $compitoObj->getCompitiByCorso($corso['id']);

    foreach ($compiti_corso as $compito) {

        $compito['nome_corso'] = $corso['nome_corso'];
        $compito['codice_corso'] = $corso['codice_corso'];

        $consegna_effettuata = isset($mappa_consegne[$compito['id']]) ? $mappa_consegne[$compito['id']] : null;
        
        $data_scadenza = new DateTime($compito['data_scadenza']);
        $oggi = new DateTime();

        if ($consegna_effettuata) {
            $compito['stato_utente'] = $consegna_effettuata['stato'];
            $compito['dati_consegna'] = $consegna_effettuata;
            $lista_storico[] = $compito;
        } elseif ($oggi > $data_scadenza) {
            $compito['stato_utente'] = 'mancante';
            $lista_storico[] = $compito;
        } else {
            $compito['stato_utente'] = 'da_fare';
            $lista_da_fare[] = $compito;
        }
    }
}

usort($lista_da_fare, function($a, $b) {
    return strtotime($a['data_scadenza']) - strtotime($b['data_scadenza']);
});

usort($lista_storico, function($a, $b) {
    return strtotime($b['data_scadenza']) - strtotime($a['data_scadenza']);
});

define('PAGE_TITLE', 'I Miei Compiti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>


<div class="container layout-contenuto">
    <header class="header-pagina">
        <h1><i class="fas fa-tasks"></i> Gestione Compiti</h1>
        <p>Visualizza le scadenze e invia i tuoi elaborati.</p>
    </header>

    <section class="mb-5">
        <h2><i class="fas fa-hourglass-half"></i> In Scadenza</h2>
        
        <?php if (count($lista_da_fare) > 0): ?>
            <div class="griglia-task">
                <?php foreach ($lista_da_fare as $task): ?>
                    <?php 
                        $scadenza = new DateTime($task['data_scadenza']);
                        $oggi = new DateTime();
                        $giorni_rimasti = $oggi->diff($scadenza)->days;
                        $is_urgente = ($giorni_rimasti < 3);
                    ?>
                    <div class="scheda-task <?php echo $is_urgente ? 'contorno-urgente' : ''; ?>">
                        <div class="task-header">
                            <span class="avviso-corso"><?php echo htmlspecialchars($task['codice_corso']); ?></span>
                            <?php if($is_urgente): ?>
                                <span class="avviso-urgente">Scade tra <?php echo $giorni_rimasti; ?> gg</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="task-body">
                            <h3><?php echo htmlspecialchars($task['titolo']); ?></h3>
                            <p class="testo-disattivato"><?php echo htmlspecialchars($task['nome_corso']); ?></p>
                            <p class="task-desc">
                                <?php echo substr(htmlspecialchars($task['descrizione']), 0, 120) . '...'; ?>
                            </p>
                            
                            <div class="task-meta">
                                <span><i class="far fa-calendar"></i> Scadenza: <strong><?php echo $scadenza->format('d/m/Y H:i'); ?></strong></span>
                                <span><i class="fas fa-star"></i> Max Punti: <?php echo $task['punti_max']; ?></span>
                            </div>
                        </div>

                        <div class="task-footer">
                            <a href="consegna.php?id=<?php echo $task['id']; ?>" class="btn btn-primario btn-blocco">
                                <i class="fas fa-upload"></i> Effettua Consegna
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-successo">
                <i class="fas fa-check-circle"></i> Ottimo lavoro! Non hai compiti in sospeso al momento.
            </div>
        <?php endif; ?>
    </section>

</div>

<?php 
include '../inclusi/footer.php'; 
?>