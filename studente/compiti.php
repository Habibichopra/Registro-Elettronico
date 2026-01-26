<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../includes/session_check.php';

require_once '../classes/Corso.php';
require_once '../classes/Compito.php';
require_once '../classes/Consegna.php';

$studente_id = $_SESSION['user_id'];
?>