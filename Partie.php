<?php
require_once 'Joueur.php';
require_once 'Drone.php';

class Partie {
    public int $id;
    public Joueur $joueur1;
    public Joueur $joueur2;
    public int $joueurActifId = 1;
    public int $tourCourant = 1;
    public string $statut = 'en_cours'; 
    
    private function calculerDistance(array $posA, array $posB): int {
        return abs($posA['x'] - $posB['x']) + abs($posA['y'] - $posB['y']);
    }

    public function __construct(int $id) {
        $this->id = $id;
        $vaisseau1 = new Vaisseau("Ajax Star", 100, 100, 20, ['x' => 1,  'y' => 1]);
        $vaisseau2 = new Vaisseau("PHP Fighter", 100, 100, 20, ['x' => 10, 'y' => 10]);
        $this->joueur1 = new Joueur(1, $vaisseau1);
        $this->joueur2 = new Joueur(2, $vaisseau2);
    }
    
    private function nettoyerDrones(Joueur $joueur) {
        $joueur->dronesIndependants = array_filter($joueur->dronesIndependants, fn($d) => $d->getEnergie() > 0);
    }
    
    private function tickDrones(Joueur $joueur): void {
        foreach ($joueur->dronesIndependants as $drone) {
            $drone->consommer(10);
            if ($drone->getType() === 'attaque') {
                $adversaire = $this->getAdversaire();
                // Vérifie si l'adversaire est passé à côté du drone (qui est maintenant fixe)
                $dist = $this->calculerDistance($drone->getPosition(), $adversaire->vaisseau->getPosition());
                if ($dist <= 3) $adversaire->vaisseau->subirDegats(10);
            }
        }
    }

    public function getJoueurActif(): Joueur { return $this->joueurActifId === 1 ? $this->joueur1 : $this->joueur2; }
    public function getAdversaire(): Joueur { return $this->joueurActifId === 1 ? $this->joueur2 : $this->joueur1; }

    public function terminerTour(): void {
        $joueurCourant = $this->getJoueurActif();
        if ($joueurCourant->malusTirTours > 0) $joueurCourant->malusTirTours--;

        $this->joueurActifId = $this->joueurActifId === 1 ? 2 : 1;
        $this->tourCourant++;

        $this->tickDrones($this->joueur1);
        $this->tickDrones($this->joueur2);
        $this->nettoyerDrones($this->joueur1);
        $this->nettoyerDrones($this->joueur2);
        
        $this->getJoueurActif()->vaisseau->regenererEnergie(10);

        if ($this->tourCourant > 1 && $this->tourCourant % 5 === 1) {
            $this->joueur1->nbDrones++;
            $this->joueur2->nbDrones++;
        }
    }

    public function verifierFinDePartie(): void {
        if ($this->joueur1->vaisseau->estDetruit() || $this->joueur2->vaisseau->estDetruit()) {
            $this->statut = 'finie';
        }
    }

    public function executerAction(array $actionData, int $joueurId): array {
        $joueurActif = $this->getJoueurActif();
        $adversaire  = $this->getAdversaire();
        $message = "Action effectuée.";
        $succes  = true;

        if ($joueurId !== $this->joueurActifId && !in_array($actionData['type_action'] ?? '', ['initialiser', 'get_status'])) {
            return ['succes' => false, 'message' => "Ce n'est pas votre tour !"];
        }

        $type = $actionData['type_action'] ?? 'get_status';

        if (strpos($type, 'lancer_drone_') === 0) {
            if ($joueurActif->nbDrones <= 0) {
                return ['succes' => false, 'message' => "Stock vide ! (Ravitaillement tous les 5 tours)"];
            }
        }

        switch ($type) {
            case 'initialiser': $message = "Partie initialisée."; break;
            case 'get_status':  $message = "Sync."; break;

            case 'deplacer':
                $x = (int)($actionData['x'] ?? 0); $y = (int)($actionData['y'] ?? 0);
                if (!$joueurActif->vaisseau->deplacer($x, $y)) {
                    $succes = false; $message = "Déplacement impossible (Énergie/Limites).";
                } else {
                    $message = "Déplacement en ($x, $y).";
                }
                break;

            case 'tirer':
                $dist = $this->calculerDistance($joueurActif->vaisseau->getPosition(), $adversaire->vaisseau->getPosition());
                if ($dist > 4) { $succes = false; $message = "Hors de portée."; break; }
                
                $res = $this->resoudreTir($joueurActif, $adversaire);
                if ($res === false) { $succes = false; $message = "Pas assez d'énergie."; }
                else { $message = $res; $this->verifierFinDePartie(); }
                break;

            // --- DRONE D'ATTAQUE (Désormais fixe à la position ciblée) ---
            case 'lancer_drone_attaque':
                // 1. Récupération des coordonnées ciblées (X,Y)
                $targetX = (int)($actionData['x'] ?? 0);
                $targetY = (int)($actionData['y'] ?? 0);
                $targetPos = ['x' => $targetX, 'y' => $targetY];

                // 2. Vérification Distance de déploiement (Vaisseau -> Case cible)
                if ($this->calculerDistance($joueurActif->vaisseau->getPosition(), $targetPos) > 4) {
                     $succes=false; $message="Cible trop loin pour déployer le drone (Max 4 cases)."; break; 
                }

                // 3. Consommation énergie
                if (!$joueurActif->vaisseau->lancerDrone()) { $succes=false; $message="Manque d'énergie."; break; }

                // 4. Création du drone à la position CIBLÉE (il ne bougera pas avec le vaisseau)
                $drone = new Drone('attaque', 30, 5, $targetPos);
                $drone->setToursRestants(3);
                $joueurActif->dronesIndependants[] = $drone;
                $joueurActif->nbDrones--; 

                // Note : Pas de dégâts immédiats, le drone agira à la fin du tour via tickDrones()
                // si l'ennemi est à proximité de sa position.
                $message = "Drone Attaque déployé en ($targetX, $targetY). Stock: {$joueurActif->nbDrones}";
                break;

            case 'lancer_drone_bouclier':
                if (!$joueurActif->vaisseau->lancerDrone()) { $succes=false; $message="Manque d'énergie."; break; }
                $drone = new Drone('bouclier', 30, 0, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $joueurActif->bouclierPoints += 30;
                $joueurActif->nbDrones--; 
                $message = "Drone Bouclier lancé.";
                break;

            case 'lancer_drone_sabotage':
                if ($this->calculerDistance($joueurActif->vaisseau->getPosition(), $adversaire->vaisseau->getPosition()) > 3) {
                     $succes=false; $message="Trop loin pour saboter."; break; 
                }
                if (!$joueurActif->vaisseau->lancerDrone()) { $succes=false; $message="Manque d'énergie."; break; }
                $drone = new Drone('sabotage', 30, 5, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $adversaire->malusTirTours += 2;
                $joueurActif->nbDrones--; 
                $message = "Drone Sabotage lancé.";
                break;

            case 'lancer_drone_reparation':
                if (!$joueurActif->vaisseau->lancerDrone()) { $succes=false; $message="Manque d'énergie."; break; }
                $drone = new Drone('reparation', 30, 0, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $joueurActif->vaisseau->soigner(20);
                $joueurActif->nbDrones--; 
                $message = "Drone Réparation lancé.";
                break;

            case 'lancer_drone_kamikaze':
                if ($this->calculerDistance($joueurActif->vaisseau->getPosition(), $adversaire->vaisseau->getPosition()) !== 1) {
                     $succes=false; $message="Doit être adjacent pour Kamikaze."; break; 
                }
                if (!$joueurActif->vaisseau->lancerDrone()) { $succes=false; $message="Manque d'énergie."; break; }
                $degats = 30;
                $absorbe = min($degats, $adversaire->bouclierPoints);
                $adversaire->bouclierPoints -= $absorbe;
                $degats -= $absorbe;
                $adversaire->vaisseau->subirDegats($degats);
                $joueurActif->nbDrones--; 
                $message = "Drone Kamikaze !";
                $this->verifierFinDePartie();
                break;

            default: $succes = false; $message = "Action inconnue.";
        }

        if ($succes && $this->statut === 'en_cours' && !in_array($type, ['initialiser', 'get_status'])) {
            $this->terminerTour();
        }
        
        $this->nettoyerDrones($joueurActif);
        $this->nettoyerDrones($adversaire);

        return ['succes' => $succes, 'message' => $message];
    }

    private function resoudreTir(Joueur $att, Joueur $def) {
        if ($att->vaisseau->estDetruit()) return "Vaisseau détruit.";
        
        $dmg = $att->vaisseau->tirer($def->vaisseau);
        if ($dmg === -1) return false;

        $reel = $dmg;
        if ($att->malusTirTours > 0) {
            $prev = $reel;
            $reel = max(5, intdiv($reel, 2));
            $def->vaisseau->soigner($prev - $reel);
        }
        if ($def->bouclierPoints > 0) {
            $abs = min($reel, $def->bouclierPoints);
            $def->bouclierPoints -= $abs;
            $def->vaisseau->soigner($abs);
            $reel -= $abs;
        }
        return "Tir: $reel dégâts.";
    }

    public function getEtatPourClient(): array {
        return [
            'tour' => $this->tourCourant,
            'statut' => $this->statut,
            'joueur_actif_id' => $this->joueurActifId,
            'joueur1' => [
                'vaisseau' => [
                    'nom' => $this->joueur1->vaisseau->getNom(),
                    'vie' => $this->joueur1->vaisseau->getVie(),
                    'energie' => $this->joueur1->vaisseau->getEnergie(),
                    'position' => $this->joueur1->vaisseau->getPosition()
                ],
                'nbDrones' => $this->joueur1->nbDrones,
                'malus' => $this->joueur1->malusTirTours,
                'drones' => $this->joueur1->getDronesData()
            ],
            'joueur2' => [
                'vaisseau' => [
                    'nom' => $this->joueur2->vaisseau->getNom(),
                    'vie' => $this->joueur2->vaisseau->getVie(),
                    'energie' => $this->joueur2->vaisseau->getEnergie(),
                    'position' => $this->joueur2->vaisseau->getPosition()
                ],
                'nbDrones' => $this->joueur2->nbDrones,
                'malus' => $this->joueur2->malusTirTours,
                'drones' => $this->joueur2->getDronesData()
            ]
        ];
    }
}
?>