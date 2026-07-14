<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'Partie.php';

// Fichier de sauvegarde partagé entre les deux PCs
define('PARTIE_FILE', __DIR__ . '/partie.dat');

function chargerPartie(): Partie {
    if (file_exists(PARTIE_FILE)) {
        $data = file_get_contents(PARTIE_FILE);
        $obj = @unserialize($data);
        if ($obj instanceof Partie) {
            return $obj;
        }
    }
    // nouvelle partie par défaut
    return new Partie(1);
}

function sauvegarderPartie(Partie $partie): void {
    file_put_contents(PARTIE_FILE, serialize($partie));
}

$joueurId   = isset($_POST['joueur_id']) ? (int)$_POST['joueur_id'] : 1;
$typeAction = $_POST['type_action'] ?? 'get_status';

$partie = chargerPartie();

// si initialiser => on repart de zéro
if ($typeAction === 'initialiser') {
    $partie = new Partie(1);
}

$result = $partie->executerAction($_POST, $joueurId);
sauvegarderPartie($partie);

echo json_encode([
    'action_result' => $result,
    'etat_jeu'      => $partie->getEtatPourClient()
]);
exit;
?>